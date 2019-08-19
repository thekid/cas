<?php namespace de\thekid\cas\flow;

use de\thekid\cas\Signed;
use de\thekid\cas\tickets\Tickets;
use util\URI;

class RedirectToService implements Step {

  public function __construct(private Tickets $tickets, private Signed $signed) { }
 
  public function setup($req, $res, $session) {
    if ($service= $session->value('service')) {
      $id= $this->tickets->create(['user' => $session->value('user'), 'service' => $service]);
      return new Redirect(new URI($service)
        ->using()
        ->param('ticket', $this->signed->id($id, $this->tickets->prefix()))
        ->create()
      );
    }
    return null;
  }

  public function complete($req, $res, $session) {
    // Never called, this is the last step
  }
}