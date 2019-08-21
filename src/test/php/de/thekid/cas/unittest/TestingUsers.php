<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\users\{Authentication, Users, User, NoSuchUser, Authenticated};
use util\Secret;

class TestingUsers implements Users {

  public function __construct(private array $users= []) { }

  /** Authenticates a user, returning success or failure in a result object */
  public function authenticate(string $username, Secret $password): Authentication {
    if (!isset($this->users[$username])) {
      return new NoSuchUser($username);
    }
    return new Authenticated(new User($username, []));
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {

  }

  /** Creates a new user with a given username and password. */
  public function create(string $username, string|Secret $password): User {

  }

  /** Removes an existing user */
  public function remove(string|User $user): void {

  }

  /** Changes a user's password. */
  public function password(string|User $user, string|Secret $password): void {

  }
}