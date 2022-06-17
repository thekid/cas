<?php namespace de\thekid\cas\cmd;

use com\google\authenticator\SecretBytes;

class ListTokens extends Administration {
  use UserBased;

  public function run(): int {
    $count= 0;
    foreach ($this->user->tokens() as $name => $secret) {
      $this->out->writeLinef(
        '* otpauth://totp/%s?secret=%s&label=%s',
        $this->user->username(),
        new SecretBytes($this->encryption->decrypt($secret))->encoded(),
        urlencode($name),
      );
      $count++;
    }
    $this->out->writeLine();
    $this->out->writeLinef('%d tokens found', $count);
    return 0;
  }
}