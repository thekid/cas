<?php namespace de\thekid\cas;

interface Templating {

  /**
   * Renders a named template
   *
   * @param  web.Response $response
   * @param  string $name Template name
   * @param  [:var] $context
   */
  public function render($response, $name, $context= []);

}