<?php namespace de\thekid\cas;

use inject\Bindings;
use lang\Environment;
use web\session\{Sessions, InFileSystem};

/** Sessioning and templates */
class Frontend extends Bindings {

  public function __construct(private string $templates, private bool $dev) { }

  /** @param inject.Injector */
  public function configure($inject) {

    // Allow session cookies to be sent via `http` during development
    $sessions= new InFileSystem(Environment::tempDir())->named('auth');
    $sessions->cookies()->insecure($this->dev);

    $inject->bind(Sessions::class, $sessions);
    $inject->bind(Templating::class, new TemplateEngine($this->templates));
  }
}