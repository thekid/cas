<?php namespace de\thekid\cas\impl;

use de\thekid\cas\Tokens;
use web\{Handler, Error};

/** Identity endpoint to return user by a given access token */
class Identity implements Handler {

  public function __construct(private Tokens $tokens) {  }

  public function handle($req, $res) {
    sscanf($req->header('Authorization'), "Bearer %[^\r]", $token);
    if (null === $token) {
      throw new Error(401, 'Missing access token');
    }

    if (null === ($access= $this->tokens->resolve($token))) {
      throw new Error(403, 'Invalid access token '.$token);
    }

    if (!isset($access['granted']['user'])) {
      throw new Error(403, 'Cannot access user scope with token '.$token);
    }

    $identity= [
      'username' => $access['user']['username'],
      'access'   => array_keys(array_filter($access['granted'])),
      ...(array)$access['user']['attributes']
    ];
    $res->answer(200);
    $res->send(json_encode($identity), 'application/json');
  }
}