<?php namespace de\thekid\cas\flow;

use de\thekid\cas\{Signed, Persistence};
use util\URI;

class RedirectToService implements Step {

  public function __construct(private Persistence $persistence, private Signed $signed) { }
 
  public function setup($req, $res, $session) {
    if ($service= $session->value('service')) {
      $id= $this->persistence->tickets()->create(['user' => $session->value('user'), 'service' => $service]);
      return new Redirect(new URI($service)
        ->using()
        ->param('ticket', $this->signed->id($id, $this->persistence->tickets()->prefix()))
        ->create()
      );
    }
    return null;
  }

  public function complete($req, $res, $session) {
    // Never called, this is the last step
  }
}