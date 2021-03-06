<?php namespace de\thekid\cas\cmd;

use com\google\authenticator\SecretBytes;
use de\thekid\cas\Encryption;
use de\thekid\cas\users\Users;
use lang\IllegalArgumentException;
use util\cmd\Arg;

class ListTokens extends Administration {
  private $user;

  public function __construct(private Users $users, private Encryption $encryption) { }

  #[Arg(position: 0)]
  public function setUser(string $user) {
    $this->user= $this->users->named($user) ?? throw new IllegalArgumentException('No such user '.$user);
  }

  public function run(): int {
    $count= 0;
    foreach ($this->user->tokens() as $name => $secret) {
      $this->out->writeLinef(
        '* otpauth://totp/%s?secret=%s&label=%s',
        $this->user->username(),
        new SecretBytes($this->encryption->decrypt($secret))->encoded(),
        urlencode($name),
      );
      $count++;
    }
    $this->out->writeLine();
    $this->out->writeLinef('%d tokens found', $count);
    return 0;
  }
}