<?php namespace de\thekid\cas\users;

record User(string $username, string $hash, array<string, string> $tokens) {

  /** Returns whether any TOTP tokens exist */
  public fn mfa() => !empty($this->tokens);

  /** Custom string representation */
  public fn toString() => sprintf(
    '%s(username: %s, tokens: [%s])',
    nameof($this),
    $this->username,
    implode(', ', array_keys($this->tokens))
  );
}