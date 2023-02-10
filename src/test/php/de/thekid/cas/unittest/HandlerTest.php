<?php namespace de\thekid\cas\unittest;

use test\{Assert, Before};
use web\io\{TestInput, TestOutput};
use web\session\ForTesting;
use web\{Request, Response};

abstract class HandlerTest {
  protected $sessions;

  #[Before]
  public function sessions() {
    $this->sessions= new ForTesting();
  }

  /**
   * Returns handler to be tested
   *
   * @return web.Handler
   */
  protected abstract function handler();

  /**
   * Creates a session and optionally register initial values
   *
   * @param  [:var] $values
   * @return web.session.ISession
   */
  protected function session($values= []) {
    $session= $this->sessions->create();
    foreach ($values as $name => $value) {
      $session->register($name, $value);
    }
    return $session;
  }

  /**
   * Invokes handle()
   *
   * @param  web.session.ISession $session
   * @param  string $method
   * @param  string $uri
   * @param  [:string] $payload
   * @return web.Response
   */
  protected function handle($session, $method= 'GET', $uri= '/', $payload= '') {
    $headers= $session ? ['Cookie' => $this->sessions->name().'='.$session->id()] : [];

    $req= new Request(new TestInput($method, $uri, $headers, $payload));
    $res= new Response(new TestOutput());

    $this->handler()->handle($req, $res);
    return $res;
  }
}