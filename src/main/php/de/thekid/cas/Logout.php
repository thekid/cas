<?php namespace de\thekid\cas;

use web\Handler;
use web\session\Sessions;

class Logout implements Handler {

  /** Creates a new logout handler */
  public function __construct(private TemplateEngine $templates, private Sessions $sessions) { }

  /** @return var */
  public function handle($req, $res) {
    if ($session= $this->sessions->locate($req)) {
      $token= $session->value('token');

      // Verify CSRF token; if not present, let user confirm
      if ($token !== $req->param('token')) {
        $this->templates->render($res, 'confirm', ['token' => $token]);
        return;
      }

      $session->destroy();
      $session->transmit($res);
    }

    // Render success page, even if no session is given.
    $this->templates->render($res, 'logout');
  }
}