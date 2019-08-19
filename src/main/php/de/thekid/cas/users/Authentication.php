<?php namespace de\thekid\cas\users;

interface Authentication {

  /** Returns authenticated user if authentication was successful */
  public function authenticated();
}