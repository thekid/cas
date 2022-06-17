<?php namespace de\thekid\cas\cmd;

class ListUsers extends Administration {
  private $filter;

  #[Arg(position: 0)]
  public function setFilter(?string $filter= null) {
    $this->filter= $filter;
  }

  public function run(): int {
    $count= 0;
    foreach ($this->persistence->users()->all($this->filter) as $user) {
      $this->out->writeLine($user);
      $count++;
    }
    $this->out->writeLine();
    $this->out->writeLinef('%d user(s) found%s', $count, $this->filter ? ' via '.$this->filter : '');
    return 0;
  }
}