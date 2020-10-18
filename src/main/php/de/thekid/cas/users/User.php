<?php namespace de\thekid\cas\users;

record User(string $username, array<string> $tokens) {

  /** Returns whether any TOTP tokens exist */
  public fn mfa() => !empty($this->tokens);
}