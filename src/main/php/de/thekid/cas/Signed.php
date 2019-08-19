<?php namespace de\thekid\cas;

use util\Secret;

/**
 * Signed ID values using `MD5` hash with a secret value
 *
 * @test  xp://de.thekid.cas.unittest.SignedTest
 */
class Signed {
  private $secret;

  /** Creates a new signed instance with a given secret */
  public function __construct(string|Secret $secret) {
    $this->secret= $secret instanceof Secret ? $secret : new Secret($secret);
  }

  /** Signs an integer ID */
  public function id(int $id, string $prefix= ''): string {
    return $prefix.$id.'-'.md5($id.$this->secret->reveal());
  }

  /** Verifies a signed string, returning the underlying ID */
  public function verify(?string $signed, string $prefix= ''): ?int {
    if (2 === sscanf($signed, $prefix.'%d-%s', $id, $hash)) {
      if ($hash === md5($id.$this->secret->reveal())) return $id;
    }
    return null;
  } 
}