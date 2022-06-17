<?php namespace de\thekid\cas\cmd;

use util\cmd\Arg;

class RemoveToken extends Administration {
  use UserBased;

  #[Arg(position: 1)]
  public function setName(private string $name) { }
 
  public function run(): int {
    $tokens= $this->user->tokens();
    if (!isset($tokens[$this->name])) {
      $this->err->writeLine('No token named "'.$this->name.'" for ', $this->user);
      return 1;
    }

    $this->persistence->users()->removeToken($this->user, $this->name);
    $this->out->writeLine('Token removed');
    return 0;
  }
}