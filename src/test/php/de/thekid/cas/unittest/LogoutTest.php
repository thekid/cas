<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\{Logout, Templating};
use unittest\TestCase;
use web\io\{TestInput, TestOutput, Buffered};
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

  private function handle($method= 'GET', $headers= [], $body= '') {
    $req= new Request(new TestInput($method, '/logout', $headers, $body));
    $res= new Response(new TestOutput()->using(Buffered::class));
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
  public function logout_performed_when_tokens_match() {
    $token= uniqid();
    $session= $this->sessions->create();
    $session->register('token', $token);

    $cookie= $this->sessions->name().'='.$session->id();
    $body= 'token='.rawurlencode($token);
    $this->handle(
      'POST',
      ['Cookie' => $cookie, 'Content-Type' => 'application/x-www-form-urlencoded', 'Content-Length' => strlen($body)],
      $body
    );

    $this->assertEquals(['logout' => []], $this->templates->rendered);
    $this->assertFalse($session->valid());
  }
}