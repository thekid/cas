<?php namespace de\thekid\cas\cmd;

use util\cmd\Arg;
use util\{Secret, Random};

class ChangePassword extends Administration {
  use UserBased;

  private $password;

  #[Arg]
  public function setPassword(?string $password= null) {
    if (null === $password) {
      $password= bin2hex(new Random()->bytes(8));
      $this->out->writeLine('Generated password: ', $password);
    }
    $this->password= new Secret($password);
  }
 
  public function run(): int {
    $this->persistence->users()->password($this->user, $this->password);
    $this->out->writeLine('Password updated');
    return 0;
  }
}