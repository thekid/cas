<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\users\{User, Users};
use lang\MethodNotImplementedException;
use util\Secret;

class TestingUsers extends Users {
  private $backing= [];

  public function __construct(array $users= []) {
    foreach ($users as $username => $password) {
      $this->backing[$username]= $this->hash($password);
    }
  }

  /** Returns all users */
  public function all(?string $filter= null): iterable {
    throw new MethodNotImplementedException('Not used in tests', __FUNCTION__);
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {
    $hash= $this->backing[$username] ?? null;
    return $hash ? new User($username, $hash, []) : null;
  }

  /** Creates a new user with a given username and password. */
  public function create(string $username, string|Secret $password): User {
    $this->backing[$username]= $this->hash($password);
    return new User($username, $this->backing[$username], []);
  }

  /** Removes an existing user */
  public function remove(string|User $user): void {
    $username= $user instanceof User ? $user->username() : $user;
    unset($this->backing[$username]);
  }

  /** Changes a user's password. */
  public function password(string|User $user, string|Secret $password): void {
    $username= $user instanceof User ? $user->username() : $user;
    $this->backing[$username]= $this->hash($password);
  }
}