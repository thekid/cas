<?php namespace de\thekid\cas;

use web\session\Sessions;

class Tokens {

  public function __construct(private Sessions $sessions) { }

  /**
   * Issue a token for a given value
   *
   * @param  var $value
   * @return string
   */
  public function issue($value) {
    $session= $this->sessions->create();

    try {
      $session->register('token', ['value' => $value]);
      return $session->id();
    } finally {
      $session->close();
    }
  }

  /**
   * Resolve a token and return the value (or NULL if the token does not exist)
   *
   * @param  string $token
   * @return var
   */
  public function resolve($token) {
    if (null === ($session= $this->sessions->open($token))) return null;

    try {
      return $session->value('token')['value'] ?? null;
    } finally {
      $session->close();
    }
  }
}