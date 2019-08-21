<?php namespace de\thekid\cas\unittest;

use unittest\TestCase;
use web\io\{TestInput, TestOutput};
use web\session\ForTesting;
use web\{Request, Response};

abstract class HandlerTest extends TestCase {
  protected $sessions;

  /** @return void */
  public function setUp() {
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
   * @param  string $body
   * @return void
   */
  protected function handle($session, $method= 'GET', $uri= '/', $body= '') {
    $headers= $session ? ['Cookie' => $this->sessions->name().'='.$session->id()] : [];
    $body && $headers+= ['Content-Type' => 'application/x-www-form-urlencoded', 'Content-Length' => strlen($body)];

    $req= new Request(new TestInput($method, $uri, $headers, $body));
    $res= new Response(new TestOutput());

    $this->handler()->handle($req, $res);
  }
}