<?php namespace de\thekid\cas\flow;

class Flow {

  /** Creates a new flow definition */
  public function __construct(public array<Step> $steps) { }

  /** Starts this flow at the beginning */
  public function start(): State { return new State($this, 0); }

  /** Resumes this flow at a given step */
  public function resume(int $step): State { return new State($this, $step); }
}