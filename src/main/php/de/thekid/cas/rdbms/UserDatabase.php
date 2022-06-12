<?php namespace de\thekid\cas\rdbms;

use de\thekid\cas\users\{User, Users};
use rdbms\{DBConnection, DriverManager};
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
class UserDatabase extends Users {
  private $conn;

  /** Creates a new database-driven datasource */
  public function __construct(string|DBConnection $conn) {
    $this->conn= $conn instanceof DBConnection ? $conn : DriverManager::getConnection($conn);
  }

  /** Fetches tokens */
  private function tokens($id): array<string, string> {
    $tokens= [];
    foreach ($this->conn->query('select * from token where user_id = %d', $id) as $token) {
      $tokens[$token['name']]= $token['secret'];
    }
    return $tokens;    
  }

  /** Returns all users */
  public function all(?string $filter= null): iterable {
    if (null === $filter) {
      $it= $this->conn->open('select * from user left join token on user.user_id = token.user_id');
    } else {
      $it= $this->conn->open(
        'select * from user left join token on user.user_id = token.user_id where username like %s',
        strtr($filter, '*', '%')
      );
    }

    // Separate cross productmath into user and tokens
    $user= null;
    foreach ($it as $record) {
      if ($record['username'] !== $user['username']) {
        $user && yield new User($user['username'], $user['tokens']);
        $user= $record + ['tokens' => []];
      }
      $record['token_id'] && $user['tokens'][$record['name']]= $record['secret'];
    }
    $user && yield new User($user['username'], $user['hash'], $user['tokens']);
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {
    $user= $this->conn->query('select * from user where username = %s', $username)->next();
    if (null === $user) return null;

    return new User($user['username'], $user['hash'], $this->tokens($user['user_id']));
  }

  /** Creates a new user with a given username and password. */
  public function create(string $username, string|Secret $password): User {
    $hash= $this->hash($password);
    $this->conn->insert('into user (username, hash) values (%s, %s)', $username, $hash);

    return new User($username, $hash, []);
  }

  /** Removes an existing user */
  public function remove(string|User $user): void {
    $this->conn->delete(
      'from user where username = %s',
      $user instanceof User ? $user->username() : $user,
    );
  }

  /** Changes a user's password. */
  public function password(string|User $user, string|Secret $password): void {
    $this->conn->update(
      'user set hash = %s where username = %s',
      $this->hash($password),
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