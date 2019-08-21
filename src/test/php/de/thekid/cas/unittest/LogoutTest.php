<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\{Logout, Templating};
use unittest\TestCase;
use web\io\{TestInput, TestOutput};
use web\session\ForTesting;
use web\{Request, Response};

class LogoutTest extends TestCase {

  /** @return void */
  public function setUp() {
    $this->templates= new class() implements Templating {
      public $rendered= [];

      public function render($response, $name, $context= []) {
        $this->rendered[$name]= $context;
      }
    };
    $this->sessions= new ForTesting();
  }

  /**
   * Invokes handle()
   *
   * @param  string $method
   * @param  [:string] $headers
   * @param  string $body
   * @return void
   */
  private function handle($method= 'GET', $headers= [], $body= '') {
    $body && $headers+= ['Content-Type' => 'application/x-www-form-urlencoded', 'Content-Length' => strlen($body)];

    $req= new Request(new TestInput($method, '/logout', $headers, $body));
    $res= new Response(new TestOutput());

    new Logout($this->templates, $this->sessions)->handle($req, $res);
  }

  <<test>>
  public function displays_logout_screen_when_accessed_without_session() {
    $this->handle('GET');
    $this->assertEquals(['logout' => []], $this->templates->rendered);
  }

  <<test>>
  public function displays_confirmation_screen_when_accessed_without_token_parameter() {
    $token= uniqid();
    $session= $this->sessions->create();
    $session->register('token', $token);

    $this->handle('GET', ['Cookie' => $this->sessions->name().'='.$session->id()]);
    $this->assertEquals(['confirm' => ['token' => $token]], $this->templates->rendered);
    $this->assertTrue($session->valid());
  }

  <<test>>
  public function displays_confirmation_screen_when_accessed_with_mismatched_token_parameter() {
    $token= uniqid();
    $session= $this->sessions->create();
    $session->register('token', $token);

    $this->handle('GET', ['Cookie' => $this->sessions->name().'='.$session->id()], 'token=incorrect');
    $this->assertEquals(['confirm' => ['token' => $token]], $this->templates->rendered);
    $this->assertTrue($session->valid());
  }

  <<test>>
  public function logout_performed_when_tokens_match() {
    $token= uniqid();
    $session= $this->sessions->create();
    $session->register('token', $token);

    $this->handle('POST', ['Cookie' => $this->sessions->name().'='.$session->id()], 'token='.rawurlencode($token));
    $this->assertEquals(['logout' => []], $this->templates->rendered);
    $this->assertFalse($session->valid());
  }
}