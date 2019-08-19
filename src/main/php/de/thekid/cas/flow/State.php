<?php namespace de\thekid\cas\flow;

class State {

  public function __construct(private Flow $flow, private int $step) { }

  public function setup($req, $res, $session) {
    return $this->flow->steps[$this->step]->setup($req, $res, $session);
  }

  public function complete($req, $res, $session) {
    return $this->flow->steps[$this->step]->complete($req, $res, $session);
  }

  /** Returns current step */
  public fn step(): int => $this->step;

  /** Goes to the next step */
  public function next(): bool {
    $max= sizeof($this->flow->steps) - 1;
    if (++$this->step > $max) {
      $this->step= $max;
      return false;
    }
    return true;
  }
}