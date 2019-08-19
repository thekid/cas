<?php namespace de\thekid\cas\flow;

class DisplaySuccess implements Step {

  public function setup($req, $res, $session) {
    return new View('success', ['user' => $session->value('user')]);
  }

  public function complete($req, $res, $session) {
    // Never called, this is the last step
  }
}