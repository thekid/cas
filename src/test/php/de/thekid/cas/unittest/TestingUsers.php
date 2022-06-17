<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\users\{User, Users};
use lang\MethodNotImplementedException;
use util\Secret;

class TestingUsers extends Users {
  private $backing= [];

  public function __construct(array $users= []) {
    foreach ($users as $username => $details) {
      $this->backing[$username]= ['hash' => $this->hash($details['password']), 'tokens' => $details['tokens'] ?? []];
    }
  }

  /** Returns all users */
  public function all(?string $filter= null): iterable {
    throw new MethodNotImplementedException('Not used in tests', __FUNCTION__);
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {
    $user= $this->backing[$username] ?? null;
    return $user ? new User($username, $user['hash'], $user['tokens']) : null;
  }

  /** Creates a new user with a given username and password. */
  public function create(string $username, string|Secret $password): User {
    $this->backing[$username]= ['hash' => $this->hash($password), 'tokens' => []];
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
    $this->backing[$username]['hash']= $this->hash($password);
  }

  public function newToken(string|User $user, string $name, string|Secret $secret) {
    $username= $user instanceof User ? $user->username() : $user;
    $this->backing[$username]['tokens'][$name]= $secret instanceof Secret ? $secret->reveal() : $secret;
  }

  public function removeToken(string|User $user, string $name) {
    $username= $user instanceof User ? $user->username() : $user;
    unset($this->backing[$username]['tokens'][$name]);
  }
}