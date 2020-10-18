<?php namespace de\thekid\cas\flow;

use de\thekid\cas\users\Users;
use lang\Throwable;
use util\Secret;

class EnterCredentials implements Step {

  public function __construct(private Users $users) { }

  public function setup($req, $res, $session) {

    // If user is already authenticated, skip to next state
    $authenticated= $session->value('user');
    if ($authenticated && !$req->param('renew')) {

      // Verify user still exists in database. TODO: If tokens or password
      // have changed, also force reauthentication!
      if ($this->users->named($authenticated['username'])) return;
    }

    // Show login view
    return new View('login', ['service' => $session->value('service')]);
  }

  public function complete($req, $res, $session) {
    try {
      $result= $this->users->authenticate($req->param('username'), new Secret($req->param('password')));
      if ($user= $result->authenticated()) {
        $session->register('user', [
          'username'   => $user->username(),
          'tokens'     => $user->tokens(),
          'mfa'        => $user->mfa(),
          'attributes' => null, // TODO, see https://github.com/thekid/cas/issues/3
        ]);
        return;
      }

      $error= ['failed' => $result];
    } catch (Throwable $t) {
      $error= ['exception' => $t];
    }

    $session->remove('user');
    return $error;
  }
}