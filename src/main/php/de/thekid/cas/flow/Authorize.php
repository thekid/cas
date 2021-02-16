<?php namespace de\thekid\cas\flow;

use de\thekid\cas\services\Services;

class Authorize implements Step {

  public function __construct() { 

    // FIXME: Implementation!
    $this->clients= new class() {
      public function lookup($id) {
        return [
          'id'     => $id,
          'name'   => 'Auth @ localhost',
          'uris'   => ['http://oauth.example.com/'],
          'secret' => 'e7911968cbb49487ec3a249c6aee3fbaa0c2fe90'
        ];
      }
    };
  }

  public function setup($req, $res, $session) {
    if ('code' !== $req->param('response_type')) {
      return new View('forbidden');
    }

    if (null === ($client= $this->clients->lookup($req->param('client_id')))) {
      return new View('forbidden');
    }

    // Default to "user" scope
    $scopes= [];
    foreach (explode(' ', $req->param('scope') ?? 'user') as $scope) {
      $scopes[$scope]= true;
    }
    $session->register('oauth', [
      'client' => $client,
      'state'  => $req->param('state'),
      'scopes' => $scopes
    ]);
    $session->register('service', $req->param('redirect_uri') ?? $client['uris'][0]);
    return null;
  }

  public function complete($req, $res, $session) {
    // NOOP
  }
}