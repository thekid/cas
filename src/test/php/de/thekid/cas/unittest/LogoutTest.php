<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\impl\Logout;
use test\{Assert, Test};

class LogoutTest extends HandlerTest {
  private $templates;

  /** @return web.Handler */
  protected fn handler() => new Logout($this->templates, $this->sessions);

  #[Test]
  public function displays_logout_screen_when_accessed_without_session() {
    $this->templates= new TestingTemplates();
    $this->handle(null, 'GET', '/logout');
    Assert::equals(['logout' => []], $this->templates->rendered());
  }

  #[Test]
  public function displays_confirmation_screen_when_accessed_without_token_parameter() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);
    $this->handle($session, 'GET', '/logout');

    Assert::equals(['confirm' => ['token' => $token]], $this->templates->rendered());
    Assert::true($session->valid());
  }

  #[Test]
  public function displays_confirmation_screen_when_accessed_with_mismatched_token_parameter() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);
    $this->handle($session, 'GET', '/logout', ['token' => 'incorrect']);

    Assert::equals(['confirm' => ['token' => $token]], $this->templates->rendered());
    Assert::true($session->valid());
  }

  #[Test]
  public function logout_performed_when_tokens_match() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);
    $this->handle($session, 'POST', '/logout', ['token' => $token]);

    Assert::equals(['logout' => []], $this->templates->rendered());
    Assert::false($session->valid());
  }
}