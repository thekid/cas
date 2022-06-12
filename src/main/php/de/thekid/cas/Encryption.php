<?php namespace de\thekid\cas;

use lang\{FormatException, IllegalAccessException};
use util\{Random, Secret};

/**
 * Encryption using either Sodium or OpenSSL libraries for encryption
 *
 * @test  de.thekid.cas.unittest.EncryptionTest
 * @see   https://deliciousbrains.com/php-encryption-methods/
 */
class Encryption {
  private $key;
  private static $random, $nlength, $klength, $encrypt, $decrypt;

  static {
    if (extension_loaded('sodium')) {
      self::$nlength= SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
      self::$klength= SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
      self::$encrypt= fn($value, $nonce, $key) => sodium_crypto_secretbox($value, $nonce, $key->reveal());
      self::$decrypt= fn($cipher, $nonce, $key) => {
        try {
          $r= sodium_crypto_secretbox_open($cipher, $nonce, $key->reveal());
        } catch ($e) {
          throw new FormatException('Errors: ['.$e->getMessage().']', $e);
        }
        if (false === ($r= sodium_crypto_secretbox_open($cipher, $nonce, $key->reveal()))) {
          $e= new FormatException('Decryption failed');
          \xp::gc(__FILE__);
          throw $e;
        }
        return $r;
      };
    } else if (extension_loaded('openssl')) {
      self::$nlength= self::$klength= openssl_cipher_iv_length('AES-128-CBC');
      self::$encrypt= fn($value, $nonce, $key) => openssl_encrypt($value, 'AES-128-CBC', $key->reveal(), 0, $nonce);
      self::$decrypt= fn($cipher, $nonce, $key) => {
        if (false === ($r= openssl_decrypt($cipher, 'AES-128-CBC', $key->reveal(), 0, $nonce))) {
          $errors= [];
          while ($error= openssl_error_string()) {
            $errors[]= $error;
          }
          $e= new FormatException('Errors: ['.implode(', ', $errors).']');
          \xp::gc(__FILE__);
          throw $e;
        }
        return $r;
      };
    } else {
      throw new IllegalAccessException('Expected either sodium or openssl extension to be loaded');
    }

    self::$random= new Random();
  }

  /** Creates a new Encryption instance with a given secret key */
  public function __construct(string|Secret $key) {
    $this->key= $key instanceof Secret ? $key : new Secret($key);
  }

  /** Creates a new random key */
  public static function randomKey(): Secret {
    return new Secret((string)self::$random->bytes(self::$klength));
  }

  /**
   * Encrypt a value
   *
   * @param  string $value
   * @return string Base64 encoded
   */
  public function encrypt($value) {
    $nonce= (string)self::$random->bytes(self::$nlength);
    $cipher= (self::$encrypt)($value, $nonce, $this->key);
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
    $nonce= substr($bytes, 0, self::$nlength);
    $cipher= substr($bytes, self::$nlength);

    return (self::$decrypt)($cipher, $nonce, $this->key);
  }
}