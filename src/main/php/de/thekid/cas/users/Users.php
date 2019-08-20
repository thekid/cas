<?php namespace de\thekid\cas\users;

use util\Secret;

interface Users {

  /** Authenticates a user, returning success or failure in a result object */
  public function authenticate(string $username, Secret $password): Authentication;

  /** Returns a user by a given username */
  public function named(string $username): ?User;

  /** Creates a new user with a given username and password. */
  public function create(string $username, string|Secret $password): User;

  /** Removes an existing user */
  public function remove(string|User $user): void;

  /** Changes a user's password. */
  public function password(string|User $user, string|Secret $password): void;

}