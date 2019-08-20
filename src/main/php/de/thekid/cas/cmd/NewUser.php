<?php namespace de\thekid\cas\cmd;

use de\thekid\cas\users\Users;
use lang\IllegalArgumentException;
use util\{Secret, Random};

class NewUser extends Administration {
  private $user, $password;

  public function __construct(private Users $users) { }

  <<arg(['position' => 0])>>
  public function setUser(string $user) {
    if (null !== $this->users->named($user)) {
      throw new IllegalArgumentException('User '.$user.' already exists');
    }
    $this->user= $user;
  }

  <<arg>>
  public function setPassword(?string $password= null) {
    if (null === $password) {
      $password= bin2hex(new Random()->bytes(8));
      $this->out->writeLine('Generated password: ', $password);
    }
    $this->password= new Secret($password);
  }
 
  public function run(): int {
    $this->users->create($this->user, $this->password);
    $this->out->writeLine('User created');
    return 0;
  }
}