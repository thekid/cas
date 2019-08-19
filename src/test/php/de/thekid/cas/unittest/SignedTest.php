<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\Signed;
use unittest\TestCase;
use util\Secret;

class SignedTest extends TestCase {
  private const SECRET = 'testing-secret';

  <<test>>
  public function can_create() {
    new Signed(self::SECRET);
  }

  <<test>>
  public function can_create_with_secret() {
    new Signed(new Secret(self::SECRET));
  }

  <<test, values([0, 1, 6100])>>
  public function roundtrip($value) {
    $fixture= new Signed(self::SECRET);
    $this->assertEquals($value, $fixture->verify($fixture->id($value)));
  }

  <<test, values([0, 1, 6100])>>
  public function roundtrip_with_prefix($value) {
    $fixture= new Signed(self::SECRET);
    $this->assertEquals($value, $fixture->verify($fixture->id($value, 'ST-'), 'ST-'));
  }

  <<test, values(['0-', '1-ABC', '6100-3c3b7591af75211812281d52-missing'])>>
  public function incorrect_hash($value) {
    $fixture= new Signed(self::SECRET);
    $this->assertNull($fixture->verify($value));
  }

  <<test>>
  public function missing_prefix() {
    $fixture= new Signed(self::SECRET);
    $this->assertNull($fixture->verify($fixture->id(1), 'ST-'));
  }

  <<test>>
  public function superfluous_prefix() {
    $fixture= new Signed(self::SECRET);
    $this->assertNull($fixture->verify($fixture->id(1, 'ST-')));
  }
}