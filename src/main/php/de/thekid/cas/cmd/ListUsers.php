<?php namespace de\thekid\cas\cmd;

use de\thekid\cas\users\Users;

class ListUsers extends Administration {
  private $filter;

  public function __construct(private Users $users) { }

  #[Arg(position: 0)]
  public function setFilter(?string $filter= null) {
    $this->filter= $filter;
  }

  public function run(): int {
    $count= 0;
    foreach ($this->users->all($this->filter) as $user) {
      $this->out->writeLine($user);
      $count++;
    }
    $this->out->writeLine();
    $this->out->writeLinef('%d user(s) found%s', $count, $this->filter ? ' via '.$this->filter : '');
    return 0;
  }
}