<?php namespace de\thekid\cas;

use inject\Bindings;
use lang\Environment;
use web\session\{Sessions, Cookies, InFileSystem};

/** Sessioning and templates */
class Frontend extends Bindings {

  public function __construct(private string $templates, private bool $dev) { }

  /** @param inject.Injector */
  public function configure($inject) {
    $cookies= new Cookies()->insecure($this->dev);
    $inject->bind(Sessions::class, new InFileSystem(Environment::tempDir())->named('auth')->via($cookies));
    $inject->bind(Templating::class, new TemplateEngine($this->templates));
  }
}