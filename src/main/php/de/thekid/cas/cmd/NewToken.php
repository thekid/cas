<?php namespace de\thekid\cas\cmd;

use com\google\authenticator\Secrets;
use de\thekid\cas\Encryption;
use de\thekid\cas\users\Users;
use lang\IllegalArgumentException;
use util\cmd\Arg;

class NewToken extends Administration {
  private $user, $name;

  public function __construct(private Users $users, private Encryption $encryption) { }

  #[Arg(position: 0)]
  public function setUser(string $user) {
    if (null === ($this->user= $this->users->named($user))) {
      throw new IllegalArgumentException('No such user '.$user);
    }
  }

  #[Arg]
  public function setName(?string $name= 'CAS') {
    $tokens= $this->user->tokens();
    if (isset($tokens[$name])) {
      throw new IllegalArgumentException('Name "'.$name.'" already used');
    }
    $this->name= $name;
  }
 
  public function run(): int {
    $random= Secrets::random();
    $this->out->writeLinef(
      '* otpauth://totp/%s?secret=%s&label=%s',
      $this->user->username(),
      $random->encoded(),
      $this->name,
    );

    $this->users->newToken($this->user, $this->name, $this->encryption->encrypt($random->bytes()));
    return 0;
  }
}