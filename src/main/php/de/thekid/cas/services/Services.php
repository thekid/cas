<?php namespace de\thekid\cas\services;

interface Services {

  /**
   * Validates the given URL
   *
   * @param  string $url
   * @return bool
   */
  public function validate($url);
}