<?php namespace de\thekid\cas\users;

class PasswordMismatch implements Authentication {

  public function __construct(private string $user) { }

  public fn authenticated() => null;
}