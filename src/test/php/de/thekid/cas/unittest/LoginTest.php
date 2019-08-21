<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\flow\{Flow, UseService, EnterCredentials, DisplaySuccess};
use de\thekid\cas\services\Services;
use de\thekid\cas\users\{NoSuchUser, PasswordMismatch};
use de\thekid\cas\{Login, Signed};
use util\Random;

class LoginTest extends HandlerTest {
  public const SERVICE = 'https://example.org/';

  private $templates, $signed, $flow;

  /** @return void */
  public function setUp() {
    parent::setUp();
    $this->templates= new TestingTemplates();
    $this->signed= new Signed('secret');
    $this->flow= new Flow([
      new UseService(new class() implements Services {
        public fn validate($url) => LoginTest::SERVICE === $url;
      }),
      new EnterCredentials(new TestingUsers(['root' => 'secret'])),
      new DisplaySuccess(),
    ]);
  }


  /** @return web.Handler */
  protected fn handler() => new Login($this->templates, $this->flow, $this->sessions, new Random(), $this->signed);

  <<test>>
  public function stores_given_service() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login?service='.self::SERVICE);
    $this->assertEquals(self::SERVICE, $session->value('service'));
  }

  <<test>>
  public function overwrites_existing_service() {
    $session= $this->session(['token' => $token= uniqid(), 'service' => '<previous-value>']);

    $this->handle($session, 'GET', '/login?service='.self::SERVICE);
    $this->assertEquals(self::SERVICE, $session->value('service'));
  }

  <<test>>
  public function shows_forbidden_page_and_does_not_store_invalid_service() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login?service=invalid' );
    $this->assertNull($session->value('service'));
    $this->assertEquals(
      [
        'forbidden' => [
          'service' => 'invalid',
          'token'   => $token,
          'flow'    => $this->signed->id(0),
        ]
      ],
      $this->templates->rendered()
    );
  }

  <<test>>
  public function cannot_authenticate_unknown_user() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', sprintf(
      'flow=%s&token=%s&username=unknown&password=...',
      $this->templates->rendered()['login']['flow'],
      $token,
    ));

    $this->assertEquals(
      [
        'login' => [
          'service' => null,
          'token'   => $token,
          'flow'    => $this->signed->id(1),
          'error'   => ['failed' => new NoSuchUser('unknown')],
        ]
      ],
      $this->templates->rendered()
    );
  }

  <<test>>
  public function cannot_authenticate_user_with_incorrect_password() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', sprintf(
      'flow=%s&token=%s&username=root&password=incorrect',
      $this->templates->rendered()['login']['flow'],
      $token,
    ));

    $this->assertEquals(
      [
        'login' => [
          'service' => null,
          'token'   => $token,
          'flow'    => $this->signed->id(1),
          'error'   => ['failed' => new PasswordMismatch('root')],
        ]
      ],
      $this->templates->rendered()
    );
  }

  <<test>>
  public function authenticate_registers_user() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', sprintf(
      'flow=%s&token=%s&username=root&password=secret',
      $this->templates->rendered()['login']['flow'],
      $token,
    ));

    $this->assertEquals(
      [
        'username'   => 'root',
        'tokens'     => [],
        'mfa'        => false,
        'attributes' => null,
      ],
      $session->value('user')
    );
  }
}