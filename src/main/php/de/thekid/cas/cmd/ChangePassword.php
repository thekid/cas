<?php namespace de\thekid\cas\cmd;

use de\thekid\cas\users\Users;
use lang\IllegalArgumentException;
use util\cmd\Arg;
use util\{Secret, Random};

class ChangePassword extends Administration {
  private $user, $password;

  public function __construct(private Users $users) { }

  #[Arg(position: 0)]
  public function setUser(string $user) {
    $this->user= $this->users->named($user) ?? throw new IllegalArgumentException('No such user '.$user);
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
    $this->users->password($this->user, $this->password);
    $this->out->writeLine('Password updated');
    return 0;
  }
}