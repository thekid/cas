<?php namespace de\thekid\cas\users;

use text\hash\{Hashing, HashCode};
use util\Secret;

abstract class Users {
  private static $hash= Hashing::sha256();

  /** Returns all users */
  public abstract function all(?string $filter= null): iterable;

  /** Returns a user by a given username */
  public abstract function named(string $username): ?User;

  /** Creates a new user with a given username and password. */
  public abstract function create(string $username, string|Secret $password): User;

  /** Removes an existing user */
  public abstract function remove(string|User $user): void;

  /** Changes a user's password. */
  public abstract function password(string|User $user, string|Secret $password): void;

  /** Creates a hash for a given secret */
  public function hash(string|Secret $secret): string {
    return self::$hash->digest($secret instanceof Secret ? $secret->reveal() : $secret)->hex();
  }

  /** Authenticates a user, returning success or failure in a result object */
  public function authenticate(string $username, Secret $password): Authentication {
    $user= $this->named($username);
    if (null === $user) {
      return new NoSuchUser($username);
    }

    $computed= self::$hash->digest($password->reveal());
    if (!$computed->equals(HashCode::fromHex($user->hash()))) {
      return new PasswordMismatch($username);
    }

    return new Authenticated($user);
  }
}