<?php namespace de\thekid\cas\cmd;

class RemoveUser extends Administration {
  use UserBased;

  public function run(): int {
    $this->persistence->users()->remove($this->user);
    $this->out->writeLine('User removed');
    return 0;
  }
}