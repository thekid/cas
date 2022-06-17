<?php namespace de\thekid\cas;

use util\Secret;

/**
 * Signed ID values using `MD5` hash with a secret value
 *
 * @test  de.thekid.cas.unittest.SignedTest
 */
class Signed {
  private $secret;

  /** Creates a new signed instance with a given secret */
  public function __construct(string|Secret $secret) {
    $this->secret= $secret instanceof Secret ? $secret : new Secret($secret);
  }

  /** Signs an ID */
  public function id(int|string $id, string $prefix= ''): string {
    return $prefix.md5($id.$this->secret->reveal()).'-'.$id;
  }

  /** Verifies a signed string, returning the underlying ID */
  public function verify(?string $signed, string $prefix= ''): ?string {
    if (null !== $signed && 2 === sscanf($signed, $prefix.'%[0-9a-f]-%s', $hash, $id)) {
      if (hash_equals($hash, md5($id.$this->secret->reveal()))) return $id;
    }
    return null;
  } 
}