<?php namespace de\thekid\cas;

use de\thekid\cas\flow\Flow;
use util\Random;
use web\session\Sessions;
use web\{Handler, Error};

class Login implements Handler {
  private const RAND = 8;

  /** Creates a new login page flow */
  public function __construct(
    private TemplateEngine $templates,
    private Flow $flow,
    private Sessions $sessions,
    private Random $random,
    private Signed $signed,
  ) { }

  /** @return var */
  public function handle($req, $res) {

    // Ensure a session exists
    if (null === ($session= $this->sessions->locate($req))) {
      $session= $this->sessions->create();

      $token= bin2hex($this->random->bytes(self::RAND));
      $session->register('token', $token);
    } else {
      $token= $session->value('token');
    }

    // Complete selected flow
    if (null === ($step= $this->signed->verify($req->param('flow')))) {
      $state= $this->flow->start();
    } else {
      if ($token !== $req->param('token')) {
        throw new Error(400, 'Missing CSRF token');
      }

      $state= $this->flow->resume($step);
      if ($error= $state->complete($req, $res, $session)) {
        $result= $state->setup($req, $res, $session);
        $session->transmit($res);
        $result->transmit($res, $this->templates, [
          'request' => $req,
          'flow'    => $this->signed->id($state->step()),
          'token'   => $token,
          'error'   => $error,
        ]);
        return;
      }

      $state->next();
    }

    // Skip to next flow state until a setup method returns a result
    do {
      $result= $state->setup($req, $res, $session);
    } while (null === $result ? $state->next() : false);

    $session->transmit($res);
    $result->transmit($res, $this->templates, [
      'request' => $req,
      'flow'    => $this->signed->id($state->step()),
      'token'   => $token
    ]);
  }
}