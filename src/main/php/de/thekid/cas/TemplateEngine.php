<?php namespace de\thekid\cas;

use com\github\mustache\templates\Templates;
use com\handlebarsjs\{HandlebarsEngine, FilesIn};
use io\Path;

/**
 * Template engine based on Handlebars
 *
 * @test  xp://de.thekid.cas.unittest.TemplateEngineTest
 */
class TemplateEngine {
  private $backing;

  /** Creates a new template engine */
  public function __construct(Path|Templates $templates) {
    $this->backing= new HandlebarsEngine()
      ->withHelper('size', fn($in, $context, $options) => sizeof($options[0]))
      ->withHelper('encode', fn($in, $context, $options) => rawurlencode($options[0]))
      ->withHelper('equals', fn($in, $context, $options) => ($options[0] ?? '') === ($options[1] ?? ''))
      ->withTemplates($templates instanceof Templates ? $templates : new FilesIn($templates))
    ;
  }

  /**
   * Renders a named template
   *
   * @param  web.Response $response
   * @param  string $name Template name
   * @param  [:var] $context
   */
  public function render($response, $name, $context= []) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $out= $response->stream();
    try {
      $this->backing->write($this->backing->load($name), $context + ['scope' => $name], $out);
    } finally {
      $out->close();
    }
  }
}
