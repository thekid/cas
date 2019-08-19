<?php namespace de\thekid\cas\users;

class NoSuchUser implements Authentication {

  public function __construct(private string $username) { }

  public fn authenticated() => null;
}