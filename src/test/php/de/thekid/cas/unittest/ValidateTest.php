<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\{Signed, Validate};
use unittest\{Assert, Test};
use web\io\{TestInput, TestOutput};
use web\{Request, Response};

class ValidateTest {
  private $tickets, $signed;

  /** @return void */
  #[Before]
  public function setUp() {
    $this->tickets= new TestingTickets();
    $this->signed= new Signed('testing-secret');
  }

  /**
   * Assertion helper comparing XML without whitespace
   *
   * @param  string $expected
   * @param  string $actual
   * @throws unittest.AssertionFailedError
   */
  private function assertResponse($expected, $actual) {
    Assert::equals(preg_replace('/\s+/', '', $expected), preg_replace('/\s+/', '', $actual));
  }

  /**
   * Handles a given URI and returns the response body
   *
   * @param  string $uri
   * @return string
   */
  private function handle($uri) {
    $fixture= new Validate($this->tickets, $this->signed);

    $req= new Request(new TestInput('GET', $uri));
    $res= new Response(TestOutput::buffered());
    $fixture->handle($req, $res);

    return $res->output()->body();
  }

  #[Test]
  public function can_create() {
    new Validate($this->tickets, $this->signed);
  }

  #[Test]
  public function validation_success() {
    $ticket= $this->signed->id(
      $this->tickets->create(['user' => ['username' => 'test'], 'service' => 'http://example.org']),
      $this->tickets->prefix(),
    );
    $this->assertResponse(
      '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationSuccess>
          <cas:user>test</cas:user>
        </cas:authenticationSuccess>
      </cas:serviceResponse>',
      $this->handle('/?ticket='.$ticket.'&service=http://example.org'),
    );
  }

  #[Test]
  public function missing_ticket_and_service_parameters_xml() {
    $this->assertResponse(
      '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationFailure code="INVALID_REQUEST">
          Parameters ticket and service are required, have []
        </cas:authenticationFailure>
      </cas:serviceResponse>',
      $this->handle('/'),
    );
  }

  #[Test]
  public function missing_ticket_parameter() {
    $this->assertResponse(
      '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationFailure code="INVALID_REQUEST">
          Parameters ticket and service are required, have [service]
        </cas:authenticationFailure>
      </cas:serviceResponse>',
      $this->handle('/?service=http://example.org'),
    );
  }

  #[Test]
  public function missing_service_parameter() {
    $this->assertResponse(
      '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationFailure code="INVALID_REQUEST">
          Parameters ticket and service are required, have [ticket]
        </cas:authenticationFailure>
      </cas:serviceResponse>',
      $this->handle('/?ticket=ST-0-ABC'),
    );
  }

  #[Test]
  public function invalid_ticket_parameter() {
    $this->assertResponse(
      '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationFailure code="INVALID_TICKET_SPEC">
          Ticket not.a.ticket
        </cas:authenticationFailure>
      </cas:serviceResponse>',
      $this->handle('/?ticket=not.a.ticket&service=http://example.org'),
    );
  }

  #[Test]
  public function missing_ticket() {
    $ticket= $this->signed->id(1, $this->tickets->prefix());
    $response= sprintf('
      <cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationFailure code="INVALID_TICKET">
          Ticket %s not recognized
        </cas:authenticationFailure>
      </cas:serviceResponse>',
      $ticket,
    );
    $this->assertResponse($response, $this->handle('/?ticket='.$ticket.'&service=http://example.org'));
  }

  #[Test]
  public function invalid_service() {
    $ticket= $this->signed->id(
      $this->tickets->create(['user' => ['username' => 'test'], 'service' => 'http://example.org']),
      $this->tickets->prefix(),
    );
    $this->assertResponse(
      '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationFailure code="INVALID_SERVICE">
          Expected http://example.org, have http://another.example.org
        </cas:authenticationFailure>
      </cas:serviceResponse>',
      $this->handle('/?ticket='.$ticket.'&service=http://another.example.org'),
    );
  }

  #[Test]
  public function success_using_explicit_xml_format() {
    $ticket= $this->signed->id(
      $this->tickets->create(['user' => ['username' => 'test'], 'service' => 'http://example.org']),
      $this->tickets->prefix(),
    );
    $this->assertResponse(
      '<cas:serviceResponse xmlns:cas="http://www.yale.edu/tp/cas">
        <cas:authenticationSuccess>
          <cas:user>test</cas:user>
        </cas:authenticationSuccess>
      </cas:serviceResponse>',
      $this->handle('/?ticket='.$ticket.'&service=http://example.org&format=xml'),
    );
  }

  #[Test]
  public function success_using_json_format() {
    $ticket= $this->signed->id(
      $this->tickets->create(['user' => ['username' => 'test'], 'service' => 'http://example.org']),
      $this->tickets->prefix(),
    );
    $this->assertResponse(
      '{
        "serviceResponse" : {
          "authenticationSuccess" : {
            "user" : "test"
          }
        }
      }',
      $this->handle('/?ticket='.$ticket.'&service=http://example.org&format=json'),
    );
  }

  #[Test]
  public function failure_using_json_format() {
    $this->assertResponse(
      '{
        "serviceResponse" : {
          "authenticationFailure" : {
            "code"        : "INVALID_REQUEST",
            "description" : "Parameters ticket and service are required, have [format]"
          }
        }
      }',
      $this->handle('/?format=json'),
    );
  }
}