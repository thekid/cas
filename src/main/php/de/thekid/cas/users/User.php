<?php namespace de\thekid\cas\users;

class User {

  /** Creates a new user */
  public function __construct(private string $username, private array<string> $tokens) { }

  /** Returns username */
  public fn username() => $this->username;

  /** Returns TOTP token secrets */
  public fn tokens() => $this->tokens;

  /** Returns whether any TOTP tokens exist */
  public fn mfa() => !empty($this->tokens);
}