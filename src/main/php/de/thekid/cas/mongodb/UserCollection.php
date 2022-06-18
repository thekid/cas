<?php namespace de\thekid\cas\mongodb;

use com\mongodb\{Collection, Document};
use de\thekid\cas\users\{User, Users};
use util\Secret;

/**
 * Users implementation storing users in a MongoDB database
 */
class UserCollection extends Users {

  /** Creates a new MongoDB-based users datasource */
  public function __construct(private Collection $collection) { }

  /** Transforms token list into map */
  private function tokens(array<mixed> $input): array<string, string> {
    $r= [];
    foreach ($input as $token) {
      $r[$token['name']]= $token['secret'];
    }
    return $r;
  }

  /** Returns all users */
  public function all(?string $filter= null): iterable {
    if (null === $filter) {
      $it= $this->collection->find();
    } else {
      $it= $this->collection->find(['username' => ['$regex' => strtr($filter, '*', '.*')]]);
    }

    foreach ($it as $user) {
      yield new User($user['username'], $user['hash'], $this->tokens($user['tokens']));
    }
  }

  /** Returns a user by a given username */
  public function named(string $username): ?User {
    $user= $this->collection->find(['username' => $username])->first();
    if (null === $user) return null;

    return new User($user['username'], $user['hash'], $this->tokens($user['tokens']));
  }

  /** Creates a new user with a given username and password. */
  public function create(string $username, string|Secret $password): User {
    $hash= $this->hash($password);
    $this->collection->insert(new Document([
      'username' => $username,
      'hash'     => $hash,
      'tokens'   => [],
    ]));
    return new User($username, $hash, []);
  }

  /** Removes an existing user */
  public function remove(string|User $user): void {
    $this->collection->delete(['username' => $user instanceof User ? $user->username() : $user]);
  }

  /** Changes a user's password. */
  public function password(string|User $user, string|Secret $password): void {
    $this->collection->update(
      ['username' => $user instanceof User ? $user->username() : $user],
      [['$set' => ['hash' => $this->hash($password)]]]
    );
  }

  public function newToken(string|User $user, string $name, string|Secret $secret) {
    $this->collection->update(
      ['username' => $user instanceof User ? $user->username() : $user],
      ['$push' => ['tokens' => [
        'name'   => $name,
        'secret' => $secret instanceof Secret ? $secret->reveal() : $secret
      ]]]
    );
  }

  public function removeToken(string|User $user, string $name) {
    $this->collection->update(
      ['username' => $user instanceof User ? $user->username() : $user],
      ['$pull' => ['tokens' => ['name' => $name]]]
    );
  }
}