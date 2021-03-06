<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\Signed;
use unittest\{Assert, Test, Values};
use util\Secret;

class SignedTest {
  private const SECRET = 'testing-secret';

  #[Test]
  public function can_create() {
    new Signed(self::SECRET);
  }

  #[Test]
  public function can_create_with_secret() {
    new Signed(new Secret(self::SECRET));
  }

  #[Test, Values([0, 1, 6100])]
  public function roundtrip($value) {
    $fixture= new Signed(self::SECRET);
    Assert::equals($value, $fixture->verify($fixture->id($value)));
  }

  #[Test, Values([0, 1, 6100])]
  public function roundtrip_with_prefix($value) {
    $fixture= new Signed(self::SECRET);
    Assert::equals($value, $fixture->verify($fixture->id($value, 'ST-'), 'ST-'));
  }

  #[Test, Values(['0-', '1-ABC', '6100-3c3b7591af75211812281d52-missing'])]
  public function incorrect_hash($value) {
    $fixture= new Signed(self::SECRET);
    Assert::null($fixture->verify($value));
  }

  #[Test]
  public function missing_prefix() {
    $fixture= new Signed(self::SECRET);
    Assert::null($fixture->verify($fixture->id(1), 'ST-'));
  }

  #[Test]
  public function superfluous_prefix() {
    $fixture= new Signed(self::SECRET);
    Assert::null($fixture->verify($fixture->id(1, 'ST-')));
  }
}