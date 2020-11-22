<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\Encryption;
use lang\FormatException;
use unittest\{Assert, Test, Expect, Values};
use util\{Random, Secret};

class EncryptionTest {
  private $key;

  #[Before]
  public function randomKey() {
    $this->key= Encryption::randomKey();
  }

  #[Test]
  public function can_create() {
    new Encryption($this->key);
  }

  #[Test]
  public function can_create_with_string() {
    new Encryption($this->key->reveal());
  }

  #[Test, Values([
    '',
    'Hello',
    'A longer string containing ümlauts',
  ])]
  public function roundtrip($value) {
    $fixture= new Encryption($this->key);
    Assert::equals($value, $fixture->decrypt($fixture->encrypt($value)));
  }

  #[Test, Expect(FormatException::class), Values([
    '',
    'not-base64',
    'jmDA+XPze33f1H4QXSzHZnqGIiwIiY5G6+3fIKAetIUo3SYyNptNLOAVS/h+US--missing',
  ])]
  public function cannot_decrypt($value) {
    $fixture= new Encryption($this->key);
    $fixture->decrypt($value);
  }
}