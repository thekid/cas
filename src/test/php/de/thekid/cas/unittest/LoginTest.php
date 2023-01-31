<?php namespace de\thekid\cas\unittest;

use com\google\authenticator\{SecretBytes, TimeBased};
use de\thekid\cas\flow\{DisplaySuccess, EnterCredentials, Flow, QueryMFACode, RedirectToService, UseService};
use de\thekid\cas\impl\Login;
use de\thekid\cas\services\Services;
use de\thekid\cas\tickets\Tickets;
use de\thekid\cas\users\{NoSuchUser, PasswordMismatch};
use de\thekid\cas\{Encryption, Signed};
use test\{Assert, Before, Test, Values};
use util\Random;

class LoginTest extends HandlerTest {
  const SERVICE     = 'https://example.org/';
  const TOTP_SECRET = 'U7YOJLCYMSOQDGI6';

  private $encryption, $persistence, $templates, $signed, $flow;

  #[Before]
  public function initialize() {
    $this->encryption= new Encryption(random_bytes(32));
    $this->persistence= new TestingPersistence(users: new TestingUsers(['root' => ['password' => 'secret']]));
    $this->signed= new Signed('secret');
    $this->flow= new Flow([
      new UseService(new class() implements Services {
        public fn validate($url) => LoginTest::SERVICE === $url;
      }),
      new EnterCredentials($this->persistence),
      new QueryMFACode($this->persistence, $this->encryption),
      new RedirectToService($this->persistence, $this->signed),
      new DisplaySuccess(),
    ]);
  }


  /** @return web.Handler */
  protected fn handler() => new Login($this->templates, $this->flow, $this->sessions, $this->signed);

  #[Test]
  public function creates_session_if_necessary() {
    $this->templates= new TestingTemplates();
    $this->handle(null, 'GET', '/login');
    Assert::notEquals(null, current($this->sessions->all())->value('token'));
  }

  #[Test]
  public function stores_given_service() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login?service='.self::SERVICE);
    Assert::equals(self::SERVICE, $session->value('service'));
  }

  #[Test]
  public function overwrites_existing_service() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid(), 'service' => '<previous-value>']);

    $this->handle($session, 'GET', '/login?service='.self::SERVICE);
    Assert::equals(self::SERVICE, $session->value('service'));
  }

  #[Test]
  public function shows_forbidden_page_and_does_not_store_invalid_service() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login?service=invalid' );
    Assert::null($session->value('service'));
    Assert::equals(
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

  #[Test]
  public function cannot_authenticate_unknown_user() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'unknown',
      'password' => '...',
    ]);

    Assert::equals(
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

  #[Test]
  public function cannot_authenticate_user_with_incorrect_password() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'incorrect',
    ]);

    Assert::equals(
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

  #[Test]
  public function authenticate_registers_user() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'secret',
    ]);

    Assert::equals(
      [
        'username'   => 'root',
        'mfa'        => false,
        'attributes' => null,
      ],
      $session->value('user')
    );
  }

  #[Test]
  public function displays_success() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login');
    $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'secret',
    ]);

    Assert::equals(
      [
        'token'   => $token,
        'flow'    => $this->signed->id(4),
        'user'    => [
          'username'   => 'root',
          'mfa'        => false,
          'attributes' => null,
        ],
      ],
      $this->templates->rendered()['success']
    );
  }

  #[Test]
  public function queries_mfa_code() {
    $this->persistence->users()->newToken('root', 'CAS', $this->encryption->encrypt(self::TOTP_SECRET));
    try {
      $this->templates= new TestingTemplates();
      $session= $this->session(['token' => $token= uniqid()]);

      $this->handle($session, 'GET', '/login');
      $this->handle($session, 'POST', '/login', [
        'flow'     => $this->templates->rendered()['login']['flow'],
        'token'    => $token,
        'username' => 'root',
        'password' => 'secret',
      ]);

      Assert::equals(
        [
          'token'   => $token,
          'flow'    => $this->signed->id(2),
          'service' => null
        ],
        $this->templates->rendered()['mfa']
      );
    } finally {
      $this->persistence->users()->removeToken('root', 'CAS');
    }
  }

  #[Test, Values(['current', 'previous', 'next'])]
  public function continues_and_displays_success_after_querying_mfa_code($method) {
    $this->persistence->users()->newToken('root', 'CAS', $this->encryption->encrypt(self::TOTP_SECRET));
    try {
      $this->templates= new TestingTemplates();
      $session= $this->session(['token' => $token= uniqid()]);

      $this->handle($session, 'GET', '/login');
      $this->handle($session, 'POST', '/login', [
        'flow'     => $this->templates->rendered()['login']['flow'],
        'token'    => $token,
        'username' => 'root',
        'password' => 'secret',
      ]);
      $this->handle($session, 'POST', '/login', [
        'flow'     => $this->templates->rendered()['mfa']['flow'],
        'token'    => $token,
        'code'     => new TimeBased(new SecretBytes(self::TOTP_SECRET))->{$method}(),
      ]);

      Assert::equals(
        [
          'token'   => $token,
          'flow'    => $this->signed->id(4),
          'user'    => [
            'username'   => 'root',
            'mfa'        => true,
            'attributes' => null,
          ],
        ],
        $this->templates->rendered()['success']
      );
    } finally {
      $this->persistence->users()->removeToken('root', 'CAS');
    }
  }

  #[Test]
  public function issues_ticket_and_redirect_to_service() {
    $session= $this->session(['token' => $token= uniqid()]);

    $this->handle($session, 'GET', '/login?service='.self::SERVICE);
    $res= $this->handle($session, 'POST', '/login', [
      'flow'     => $this->templates->rendered()['login']['flow'],
      'token'    => $token,
      'username' => 'root',
      'password' => 'secret',
    ]);

    Assert::equals(
      [
        'service' => self::SERVICE,
        'user'    => [
          'username'   => 'root',
          'mfa'        => false,
          'attributes' => null,
        ],
      ],
      $this->persistence->tickets()->validate(0),
    );
    Assert::equals(
      self::SERVICE.'?ticket='.$this->signed->id(0, $this->persistence->tickets()->prefix()),
      $res->headers()['Location']
    );
  }

  #[Test]
  public function issues_ticket_and_redirect_to_service_directly_when_user_in_session() {
    $session= $this->session(['token' => $token= uniqid(), 'user' => [
      'username'   => 'root',
      'mfa'        => false,
      'attributes' => null,
    ]]);
    $res= $this->handle($session, 'GET', '/login?service='.self::SERVICE);

    Assert::equals(
      [
        'service' => self::SERVICE,
        'user'    => [
          'username'   => 'root',
          'mfa'        => false,
          'attributes' => null,
        ],
      ],
      $this->persistence->tickets()->validate(1),
    );
    Assert::equals(
      self::SERVICE.'?ticket='.$this->signed->id(1, $this->persistence->tickets()->prefix()),
      $res->headers()['Location']
    );
  }

  #[Test]
  public function renews_authentication_when_renew_parameter_is_given() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid(), 'user' => [
      'username'   => 'root',
      'mfa'        => false,
      'attributes' => null,
    ]]);
    $res= $this->handle($session, 'GET', '/login?renew=true');

    Assert::equals(
      [
        'login' => [
          'service' => null,
          'token'   => $token,
          'flow'    => $this->signed->id(1),
        ]
      ],
      $this->templates->rendered()
    );
  }

  #[Test]
  public function renews_authentication_when_session_user_no_longer_exists() {
    $this->templates= new TestingTemplates();
    $session= $this->session(['token' => $token= uniqid(), 'user' => [
      'username'   => 'admin',
      'mfa'        => false,
      'attributes' => null,
    ]]);
    $res= $this->handle($session, 'GET', '/login');

    Assert::equals(
      [
        'login' => [
          'service' => null,
          'token'   => $token,
          'flow'    => $this->signed->id(1),
        ]
      ],
      $this->templates->rendered()
    );
  }
}