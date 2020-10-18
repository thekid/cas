<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\Encryption;
use lang\FormatException;
use unittest\TestCase;
use util\{Random, Secret};

class EncryptionTest extends TestCase {
  private $key;

  /** @return void */
  public function setUp() {
    $this->key= new Random()->bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
  }

  #[Test]
  public function can_create() {
    new Encryption($this->key);
  }

  #[Test]
  public function can_create_with_secret() {
    new Encryption(new Secret($this->key));
  }

  #[Test, Values([
    '',
    'Hello',
    'A longer string containing ümlauts',
  ])]
  public function roundtrip($value) {
    $fixture= new Encryption($this->key);
    $this->assertEquals($value, $fixture->decrypt($fixture->encrypt($value)));
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