<?php namespace de\thekid\cas\users;

use rdbms\DBConnection;
use text\hash\{Hashing, HashCode};
use util\Secret;

/**
 * Minimalistic user database implementation
 *
 * user - Credentials table
 * ========================
 * user_id   int PK
 * username  varchar
 * hash      varchar - stored using SHA256
 *
 * token - TOTP token table
 * ========================
 * token_id  int PK
 * user_id   int
 * name      varchar
 * secret    varchar
 */
class UserDatabase implements Users {
  private $hash;

  /** Creates a new database-driven datasource */
  public function __construct(private DBConnection $conn) {
    $this->hash= Hashing::sha256();
  }

  /** Fetches tokens */
  private function tokens($id): array<string, string> {
    $tokens= [];
    foreach ($this->conn->query('select * from token where user_id = %d', $id) as $token) {
      $tokens[$token['name']]= $token['secret'];
    }
    return $tokens;    
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {
    $user= $this->conn->query('select * from user where username = %s', $username)->next();
    if (null === $user) return null;

    return new User($user['username'], $this->tokens($user['user_id']));
  }

  /** Authenticates a user, returning success or failure in a result object */
  public function authenticate(string $username, Secret $password): Authentication {
    $user= $this->conn->query('select * from user where username = %s', $username)->next();
    if (null === $user) {
      return new NoSuchUser($username);
    }

    $computed= $this->hash->digest($password->reveal());
    if (!$computed->equals(HashCode::fromHex($user['hash']))) {
      return new PasswordMismatch($username);
    }

    return new Authenticated(new User($user['username'], $this->tokens($user['user_id'])));
  }

  public function create(string $user, string|Secret $password) {
    $this->conn->insert(
      'into user (username, hash) values (%s, %s)',
      $user,
      $this->hash->digest($password instanceof Secret ? $password->reveal() : $password),
    );
  }

  public function remove(string|User $user) {
    $this->conn->delete(
      'from user where username = %s',
      $user instanceof User ? $user->username() : $user,
    );
  }

  public function newToken(string|User $user, string $name, string|Secret $secret) {
    $q= $this->conn->query(
      'select user_id from user where username = %s',
      $user instanceof User ? $user->username() : $user,
    );
    $this->conn->insert(
      'into token (user_id, name, secret) values (%d, %s, %s)',
      $q->next('user_id'),
      $name,
      $secret instanceof Secret ? $secret->reveal() : $secret,
    );
  }

  public function removeToken(string|User $user, string $name) {
    $q= $this->conn->query(
      'select user_id from user where username = %s',
      $user instanceof User ? $user->username() : $user,
    );
    $this->conn->delete('from token where user_id = %d and name = %s', $q->next('user_id'), $name);
  }
}