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

  /** Creates a new database-driven datasource */
  public function __construct(private DBConnection $conn) { }

  /** Returns all users */
  public function all(?string $filter= null): iterable {
    if (null === $filter) {
      $q= $this->conn->open('select * from user left join token on user.user_id = token.user_id');
    } else {
      $q= $this->conn->open(
        'select * from user left join token on user.user_id = token.user_id where username like %s',
        strtr($filter, '*', '%')
      );
    }

    // Separate cross productmath into user and tokens
    $user= null;
    while ($record= $q->next()) {
      if ($record['username'] !== $user['username']) {
        $user && yield new User($user['username'], $user['hash'], $user['tokens']);
        $user= $record + ['tokens' => []];
      }
      $record['token_id'] && $user['tokens'][$record['name']]= $record['secret'];
    }
    $user && yield new User($user['username'], $user['hash'], $user['tokens']);
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {
    $results= $this->conn->select(
      '* from user left join token on user.user_id = token.user_id where username = %s',
      $username
    );
    if (empty($results)) return null;

    // Map outer-join list of tokens
    $tokens= [];
    foreach ($results as $record) {
      $record['token_id'] && $tokens[$record['name']]= $record['secret'];
    }
    return new User($results[0]['username'], $results[0]['hash'], $tokens);
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