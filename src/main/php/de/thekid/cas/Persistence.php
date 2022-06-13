<?php namespace de\thekid\cas;

use de\thekid\cas\tickets\Tickets;
use de\thekid\cas\users\Users;

interface Persistence {

  /** Returns users persistence */
  public function users(): Users;

  /** Returns tickets persistence */
  public function tickets(): Tickets;

}