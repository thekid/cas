<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\Logout;

class LogoutTest extends HandlerTest {
  private $templates;

  /** @return void */
  public function setUp() {
    parent::setUp();
    $this->templates= new TestingTemplates();
  }

  /** @return web.Handler */
  protected fn handler() => new Logout($this->templates, $this->sessions);

  #[Test]
  public function displays_logout_screen_when_accessed_without_session() {
    $this->handle(null, 'GET', '/logout');
    $this->assertEquals(['logout' => []], $this->templates->rendered());
  }

  #[Test]
  public function displays_confirmation_screen_when_accessed_without_token_parameter() {
    $session= $this->session(['token' => $token= uniqid()]);
    $this->handle($session, 'GET', '/logout');

    $this->assertEquals(['confirm' => ['token' => $token]], $this->templates->rendered());
    $this->assertTrue($session->valid());
  }

  #[Test]
  public function displays_confirmation_screen_when_accessed_with_mismatched_token_parameter() {
    $session= $this->session(['token' => $token= uniqid()]);
    $this->handle($session, 'GET', '/logout', ['token' => 'incorrect']);

    $this->assertEquals(['confirm' => ['token' => $token]], $this->templates->rendered());
    $this->assertTrue($session->valid());
  }

  #[Test]
  public function logout_performed_when_tokens_match() {
    $session= $this->session(['token' => $token= uniqid()]);
    $this->handle($session, 'POST', '/logout', ['token' => $token]);

    $this->assertEquals(['logout' => []], $this->templates->rendered());
    $this->assertFalse($session->valid());
  }
}