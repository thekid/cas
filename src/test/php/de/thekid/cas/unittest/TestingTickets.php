<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\tickets\Tickets;
use test\Assert;

class TestingTickets extends Tickets {
  private $backing= [];

  public function create($value) {
    $this->backing[]= $value;
    return sizeof($this->backing) - 1;
  }

  public function validate($id) {
    return $this->backing[$id] ?? null;
  }
}