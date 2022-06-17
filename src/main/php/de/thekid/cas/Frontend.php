<?php namespace de\thekid\cas;

use inject\Bindings;
use lang\Environment;
use web\session\{Sessions, InFileSystem, InRedis};

/** Sessioning and templates */
class Frontend extends Bindings {

  public function __construct(private string $templates, private bool $dev) { }

  /** @param inject.Injector */
  public function configure($inject) {

    // Use file system for sessions and allow session cookies to be sent via
    // `http` during development, use Redis otherwise to be able to cluster
    if ($this->dev) {
      $sessions= new InFileSystem(Environment::tempDir());
      $sessions->cookies()->insecure(true);
    } else {
      $sessions= new InRedis($inject->get('string', 'redis'));
    }

    $inject->bind(Sessions::class, $sessions->named('auth'));
    $inject->bind(Templating::class, new TemplateEngine($this->templates));
  }
}