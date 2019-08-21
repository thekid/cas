<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\flow\{Flow, UseService, EnterCredentials, RedirectToService, DisplaySuccess};
use de\thekid\cas\services\Services;
use de\thekid\cas\tickets\Tickets;
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
    $this->tickets= new TestingTickets();
    $this->signed= new Signed('secret');
    $this->flow= new Flow([
      new UseService(new class() implements Services {
        public fn validate($url) => LoginTest::SERVICE === $url;
      }),
      new EnterCredentials(new TestingUsers(['root' => 'secret'])),
      new RedirectToService($this->tickets, $this->signed),
      new DisplaySuccess(),
    ]);
  }


  /** @return web.Handler */
  protected fn handler() => new Login($this->templates, $this->flow, $this->sessions, $this->signed);

  <<test>>
  public function creates_session_if_ncessary() {
    $this->handle(null, 'GET', '/login');
    $this->assertNotEquals(null, current($this->sessions->all())->value('token'));
  }

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
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'unknown',
      'password' => '...',
    ]);

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
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'incorrect',
    ]);

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
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'secret',
    ]);

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

  <<test>>
  public function displays_success() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'secret',
    ]);

    $this->assertEquals(
      [
        'token'   => $token,
        'flow'    => $this->signed->id(3),
        'user'    => [
          'username'   => 'root',
          'tokens'     => [],
          'mfa'        => false,
          'attributes' => null,
        ],
      ],
      $this->templates->rendered()['success']
    );
  }

  <<test>>
  public function issues_ticket_and_redirect_to_service() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login?service='.self::SERVICE);
    $res= $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'secret',
    ]);

    $this->assertEquals(
      [
        'service' => self::SERVICE,
        'user'    => [
          'username'   => 'root',
          'tokens'     => [],
          'mfa'        => false,
          'attributes' => null,
        ],
      ],
      $this->tickets->validate(0),
    );
    $this->assertEquals(
      self::SERVICE.'?ticket='.$this->signed->id(0, $this->tickets->prefix()),
      $res->headers()['Location']
    );
  }
}