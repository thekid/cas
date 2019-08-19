<?php namespace de\thekid\cas;

use lang\FormatException;
use util\{Random, Secret};

/**
 * Encryption using Sodium library
 *
 * @test  xp://de.thekid.cas.unittest.EncryptionTest
 * @see   https://deliciousbrains.com/php-encryption-methods/
 */
class Encryption {
  private $key, $random;

  /** Creates a new Encryption instance with a given secret key */
  public function __construct(string|Secret $key) {
    $this->key= $key instanceof Secret ? $key : new Secret($key);
    $this->random= new Random();
  }

  /**
   * Encrypt a value
   *
   * @param  string $value
   * @return string Base64 encoded
   */
  public function encrypt($value) {
    $nonce= (string)$this->random->bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $cipher= sodium_crypto_secretbox($value, $nonce, $this->key->reveal());
    return base64_encode($nonce.$cipher);
  }

  /**
   * Decrypt a value
   *
   * @param  string $encoded Base64 encoded
   * @return string
   * @throws lang.FormatException
   */
  public function decrypt($encoded) {
    $bytes= base64_decode($encoded);
    $nonce= substr($bytes, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $cipher= substr($bytes, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

    try {
      $r= sodium_crypto_secretbox_open($cipher, $nonce, $this->key->reveal());
    } catch (\SodiumException $e) {
      throw new FormatException('Internal decryption error', $e);
    }

    if (false === $r) {
      throw new FormatException('Cannot decrypt given value');
    }
    return $r;
  }
}