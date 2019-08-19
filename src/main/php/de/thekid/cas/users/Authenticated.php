<?php namespace de\thekid\cas\users;

class Authenticated implements Authentication {

  public function __construct(private User $user) { }

  public fn authenticated() => $this->user;
}