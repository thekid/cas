<?php namespace de\thekid\cas\flow;

class View {

  public function __construct(private string $name, private array<string, mixed> $context= []) { }
  
  public function transmit($res, $templates, $context) {
    $templates->render($res, $this->name, $context + $this->context);
  }
}