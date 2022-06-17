<?php namespace de\thekid\cas\cmd;

use com\google\authenticator\Secrets;
use util\cmd\Arg;

class NewToken extends Administration {
  use UserBased;

  #[Arg]
  public function setName(private ?string $name= 'CAS') { }
 
  public function run(): int {
    $tokens= $this->user->tokens();
    if (isset($tokens[$this->name])) {
      $this->err->writeLine('Name "'.$this->name.'" already used for ', $this->user);
      return 1;
    }

    $random= Secrets::random();
    $this->out->writeLinef(
      '* otpauth://totp/%s?secret=%s&label=%s',
      $this->user->username(),
      $random->encoded(),
      urlencode($this->name),
    );

    $this->persistence->users()->newToken($this->user, $this->name, $this->encryption->encrypt($random->bytes()));
    return 0;
  }
}