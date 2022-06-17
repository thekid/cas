<?php namespace de\thekid\cas\cmd;

use lang\IllegalArgumentException;
use util\cmd\Arg;

trait UserBased {
  protected $user;

  #[Arg(position: 0)]
  public function setUser(string $user) {
    $this->user= $this->persistence->users()->named($user) ?? throw new IllegalArgumentException('No such user '.$user);
  }
}