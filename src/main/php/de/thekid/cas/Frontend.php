<?php namespace de\thekid\cas;

use inject\Bindings;
use io\Path;
use lang\Environment;
use web\session\{Sessions, InFileSystem};

/** Sessioning and templates */
class Frontend extends Bindings {

  public function __construct(private string $webroot, private bool $dev) { }

  /** @param inject.Injector */
  public function configure($inject) {
    $inject->bind(Sessions::class, new InFileSystem(Environment::tempDir())->named('auth')->insecure($this->dev));
    $inject->bind(Templating::class, new TemplateEngine(new Path($this->webroot, 'src/main/handlebars')));
  }
}