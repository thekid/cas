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

  #[Test, Values([0, 1, 6100, '62a6179f4c9ac9ab5cb437e3', '4cf960df-2019-4963-9668-f17b3d08115c'])]
  public function roundtrip($value) {
    $fixture= new Signed(self::SECRET);
    Assert::equals((string)$value, $fixture->verify($fixture->id($value)));
  }

  #[Test, Values([0, 1, 6100, '62a6179f4c9ac9ab5cb437e3', '4cf960df-2019-4963-9668-f17b3d08115c'])]
  public function roundtrip_with_prefix($value) {
    $fixture= new Signed(self::SECRET);
    Assert::equals((string)$value, $fixture->verify($fixture->id($value, 'ST-'), 'ST-'));
  }

  #[Test, Values(['-', '-0', 'ABC-1', '3c3b7591af75211812281d52-6100'])]
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