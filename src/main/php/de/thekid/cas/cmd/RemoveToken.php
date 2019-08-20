<?php namespace de\thekid\cas\cmd;

use de\thekid\cas\users\Users;
use lang\IllegalArgumentException;

class RemoveToken extends Administration {
  private $user, $name;

  public function __construct(private Users $users) { }

  <<arg(['position' => 0])>>
  public function setUser(string $user) {
    if (null === ($this->user= $this->users->named($user))) {
      throw new IllegalArgumentException('No such user '.$user);
    }
  }

  <<arg(['position' => 1])>>
  public function setName(string $name) {
    $tokens= $this->user->tokens();
    if (!isset($tokens[$name])) {
      throw new IllegalArgumentException('No token named "'.$name.'"');
    }
    $this->name= $name;
  }
 
  public function run(): int {
    $this->users->removeToken($this->user, $this->name);
    $this->out->writeLine('Token removed');
    return 0;
  }
}