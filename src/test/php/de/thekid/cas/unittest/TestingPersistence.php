<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\Persistence;
use de\thekid\cas\tickets\Tickets;
use de\thekid\cas\users\Users;

class TestingPersistence implements Persistence {

  public function __construct(
    private Users $users= new TestingUsers(),
    private Tickets $tickets= new TestingTickets(),
  ) { }

  /* Users accessor */
  public fn users(): Users => $this->users;

  /* Tickets accessor */
  public fn tickets(): Tickets => $this->tickets;
}