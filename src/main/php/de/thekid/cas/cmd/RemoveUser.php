<?php namespace de\thekid\cas\cmd;

use de\thekid\cas\users\Users;
use lang\IllegalArgumentException;
use util\cmd\Arg;

class RemoveUser extends Administration {
  private $user;

  public function __construct(private Users $users) { }

  #[Arg(position: 0)]
  public function setUser(string $user) {
    $this->user= $this->users->named($user) ?? throw new IllegalArgumentException('No such user '.$user);
  }

  public function run(): int {
    $this->users->remove($this->user);
    $this->out->writeLine('User removed');
    return 0;
  }
}