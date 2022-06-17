<?php namespace de\thekid\cas\cmd;

use lang\IllegalArgumentException;
use util\cmd\Arg;
use util\{Secret, Random};

class NewUser extends Administration {
  private $user, $password;

  #[Arg(position: 0)]
  public function setUser(string $user) {
    if (null !== $this->persistence->users()->named($user)) {
      throw new IllegalArgumentException('User '.$user.' already exists');
    }
    $this->user= $user;
  }

  #[Arg]
  public function setPassword(?string $password= null) {
    if (null === $password) {
      $password= bin2hex(new Random()->bytes(8));
      $this->out->writeLine('Generated password: ', $password);
    }
    $this->password= new Secret($password);
  }
 
  public function run(): int {
    $this->persistence->users()->create($this->user, $this->password);
    $this->out->writeLine('User created');
    return 0;
  }
}