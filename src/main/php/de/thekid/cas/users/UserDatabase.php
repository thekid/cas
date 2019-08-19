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
 * secret    varchar
 */
class UserDatabase implements Users {
  private $hash;

  /** Creates a new database-driven datasource */
  public function __construct(private DBConnection $conn) {
    $this->hash= Hashing::sha256();
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

    $tokens= [];
    foreach ($this->conn->query('select * from token where user_id = %d', $user['user_id']) as $token) {
      $tokens[]= $token['secret'];
    }
    return new Authenticated(new User($user['username'], $tokens));
  }
}