<?php namespace de\thekid\cas\impl;

use de\thekid\cas\tickets\Tickets;
use de\thekid\cas\{Signed, Tokens};
use web\{Handler, Error};

class AccessToken implements Handler {

  public function __construct(
    private Signed $signed,
    private Tickets $tickets,
    private Tokens $tokens,
  ) {  }

  public function handle($req, $res) {
    $id= $this->signed->verify($req->param('code'), $this->tickets->prefix());
    if (null === $id || null === ($ticket= $this->tickets->validate($id))) {
      throw new Error(401, 'Invalid code '.$req->param('code'));
    }

    if ($ticket['state'] !== $req->param('state')) {
      throw new Error(401, 'Invalid state '.$req->param('state'));
    }

    $client= $ticket['client'];
    if ($client['id'] !== $req->param('client_id') || $client['secret'] !== $req->param('client_secret')) {
      throw new Error(401, 'Invalid client '.$req->param('client_id'));
    }

    // Issue and send token
    $token= $this->tokens->issue($ticket);
    $res->answer(200);
    $res->send('access_token='.urlencode($token).'&token_type=Bearer', 'application/x-www-form-urlencoded');
  }
}