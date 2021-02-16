<?php namespace de\thekid\cas\flow;

use de\thekid\cas\Signed;
use de\thekid\cas\tickets\Tickets;
use lang\IllegalStateException;
use util\URI;

class ContinueAuthorization implements Step {

  public function __construct(private Tickets $tickets, private Signed $signed) { }
 
  public function setup($req, $res, $session) {
    $oauth= $session->value('oauth') ?? throw new IllegalStateException('Missing authorization');
    $id= $this->tickets->create($oauth + ['granted' => $session->value('scopes'), 'user' => $session->value('user')]);

    return new Redirect(new URI($session->value('service'))
      ->using()
      ->param('state', $oauth['state'])
      ->param('code', $this->signed->id($id, $this->tickets->prefix()))
      ->create()
    );
  }

  public function complete($req, $res, $session) {
    // Never called, unconditionally redirects
  }
}