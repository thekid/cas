<?php namespace de\thekid\cas\unittest;

use de\thekid\cas\Templating;
use unittest\Assert;

class TestingTemplates implements Templating {
  private $rendered= [];

  /**
   * Renders a named template
   *
   * @param  web.Response $response
   * @param  string $name Template name
   * @param  [:var] $context
   */
  public function render($response, $name, $context= []) {
    unset($context['request']); // Ignore for comparison
    $this->rendered[$name]= $context;
  }

  /** Returns rendered templates */
  public fn rendered(): array<string, mixed> => $this->rendered;
}