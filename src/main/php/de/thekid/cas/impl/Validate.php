<?php namespace de\thekid\cas\impl;

use de\thekid\cas\Signed;
use de\thekid\cas\tickets\Tickets;
use web\Handler;

/**
 * The /serviceValidate endpoint
 *
 * @test  xp://de.thekid.cas.unittest.ValidateTest
 * @see   https://apereo.github.io/cas/6.0.x/protocol/CAS-Protocol-Specification.html
 */
class Validate implements Handler {

  public function __construct(private Tickets $tickets, private Signed $signed) { }

  /**
   * Handle request and response
   *
   * @param  web.Request $req
   * @param  web.Response $res
   * @return var
   */
  public function handle($req, $res) {
    $ticket= $req->param('ticket');
    $service= $req->param('service');
    $response= ServiceResponse::forFormat($req->param('format'));

    if (null === $ticket || null === $service) {
      return $response->transmit($res, $response->failure(
        'INVALID_REQUEST',
        'Parameters ticket and service are required, have [%s]',
        implode(', ', array_keys($req->params())),
      ));
    }

    if (null === ($id= $this->signed->verify($ticket, $this->tickets->prefix()))) {
      return $response->transmit($res, $response->failure(
        'INVALID_TICKET_SPEC',
        'Ticket %s',
        $req->param('ticket'),
      ));
    }

    if (null === ($issued= $this->tickets->validate($id))) {
      return $response->transmit($res, $response->failure(
        'INVALID_TICKET',
        'Ticket %s not recognized',
        $req->param('ticket'),
      ));
    }

    if ($issued['service'] !== $service) {
      return $response->transmit($res, $response->failure(
        'INVALID_SERVICE',
        'Expected %s, have %s',
        $issued['service'],
        $service,
      ));
    }

    $response->transmit($res, $response->success($issued['user']));
  }
}