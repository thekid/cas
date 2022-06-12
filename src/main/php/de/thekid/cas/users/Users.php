<?php namespace de\thekid\cas\users;

use util\Secret;

abstract class Users {

  /** Authenticates a user, returning success or failure in a result object */
  public abstract function authenticate(string $username, Secret $password): Authentication;

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

}