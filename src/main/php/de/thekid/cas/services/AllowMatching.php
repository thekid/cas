<?php namespace de\thekid\cas\services;

class AllowMatching implements Services {
  private $pattern;

  /**
   * Creates a new service validator based on a regex
   *
   * @param  string $pattern PRCE, not including delimiters
   */
  public function __construct($pattern) {
    $this->pattern= '#^('.str_replace('#', '\#', $pattern).')#';
  }

  /**
   * Validates the given URL
   *
   * @param  string $url
   * @return bool
   */
  public function validate($url) {
    return (bool)preg_match($this->pattern, $url);
  }
}