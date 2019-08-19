<?php namespace de\thekid\cas\flow;

use util\URI;

class Redirect {

  public function __construct(private URI|string $location) { }
  
  public function transmit($res, $templates, $context) {
    $res->answer(302);
    $res->header('Location', $this->location);
  }
}