<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\users\{Authenticated, Authentication, NoSuchUser, PasswordMismatch, User, Users};
use lang\MethodNotImplementedException;
use unittest\Assert;
use util\Secret;

class TestingUsers implements Users {
  private $backing= [];

  public function __construct(array $users= []) {
    foreach ($users as $username => $password) {
      $this->backing[$username]= $password instanceof Secret ? $password : new Secret($password);
    }
  }

  /** Authenticates a user, returning success or failure in a result object */
  public function authenticate(string $username, Secret $password): Authentication {
    if (!isset($this->backing[$username])) {
      return new NoSuchUser($username);
    }

    if (!$password->equals($this->backing[$username])) {
      return new PasswordMismatch($username);
    }

    return new Authenticated(new User($username, []));
  }

  /** Returns all users */
  public function all(?string $filter= null): iterable {
    throw new MethodNotImplementedException('Not used in tests', __FUNCTION__);
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {
    return isset($this->backing[$username]) ? new User($username, []) : null;
  }

  /** Creates a new user with a given username and password. */
  public function create(string $username, string|Secret $password): User {
    $this->backing[$username]= $password instanceof Secret ? $password : new Secret($password);
    return new User($username, []);
  }

  /** Removes an existing user */
  public function remove(string|User $user): void {
    $username= $user instanceof User ? $user->username() : $user;
    unset($this->backing[$username]);
  }

  /** Changes a user's password. */
  public function password(string|User $user, string|Secret $password): void {
    $username= $user instanceof User ? $user->username() : $user;
    $this->backing[$username]= $password instanceof Secret ? $password : new Secret($password);
  }
}