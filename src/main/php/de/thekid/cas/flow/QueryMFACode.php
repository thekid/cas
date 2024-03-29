<?php namespace de\thekid\cas\flow;

use com\google\authenticator\{TimeBased, SecretBytes, Tolerance};
use de\thekid\cas\{Encryption, Persistence};
use util\Secret;

/**
 * Multi-factor authentication using time-based tokens (TOTP).
 *
 * If user has already authenticated with MFA; or if the user does not
 * have MFA activated, skip to next state.
 */
class QueryMFACode implements Step {

  public function __construct(private Persistence $persistence, private Encryption $encryption) { }

  public function setup($req, $res, $session) {
    if ($session->value('mfa') || !$session->value('user')['mfa']) return null;
    return new View('mfa', ['service' => $session->value('service')]);
  }

  public function complete($req, $res, $session) {
    $time= time();
    $code= $req->param('code');

    // Check all of the user's token whether one of them matches.
    if ($user= $this->persistence->users()->named($session->value('user')['username'])) {
      foreach ($user->tokens() as $name => $token) {
        $timebased= new TimeBased(new SecretBytes($this->encryption->decrypt($token)));
        if ($timebased->verify($code, $time, Tolerance::$PREVIOUS_AND_NEXT)) {
          $session->register('mfa', $name);
          return;
        }
      }
    }

    $session->remove('mfa');
    return ['failed' => 'invalid-code'];
  }
}