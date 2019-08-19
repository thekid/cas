<?php namespace de\thekid\cas\users;

use util\Secret;

interface Users {

  /** Authenticates a user, returning success or failure in a result object */
  public function authenticate(string $username, Secret $password): Authentication;

}