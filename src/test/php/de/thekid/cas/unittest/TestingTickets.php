<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\tickets\Tickets;
use unittest\Assert;

class TestingTickets implements Tickets {
  private $backing= [];

  public fn prefix() => 'ST-';

  public function create($value) {
    $this->backing[]= $value;
    return sizeof($this->backing) - 1;
  }

  public function validate($id) {
    return $this->backing[$id] ?? null;
  }
}