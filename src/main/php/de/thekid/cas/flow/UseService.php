<?php namespace de\thekid\cas\flow;

use de\thekid\cas\services\Services;

class UseService implements Step {

  public function __construct(private Services $services) { }

  public function setup($req, $res, $session) {
    if ($service= $req->param('service')) {
      if (!$this->services->validate($service)) {
        return new View('forbidden', ['service' => $service]);
      }

      $session->register('service', $service);
    }
    return null;
  }

  public function complete($req, $res, $session) {
    // NOOP
  }
}