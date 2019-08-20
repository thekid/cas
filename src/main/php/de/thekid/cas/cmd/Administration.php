<?php namespace de\thekid\cas\cmd;

use de\thekid\cas\Implementations;
use inject\{Injector, ConfiguredBindings};
use io\Path;
use security\credentials\{Credentials, FromEnvironment, FromFile};
use util\cmd\{Command, Config};

abstract class Administration extends Command {

  /** Instantiates command using injector */
  public static function newInstance(Config $config): self {
    $credentials= new Credentials(new FromEnvironment(), new FromFile('credentials'));
    $inject= new Injector(
      new ConfiguredBindings($credentials->expanding($config->properties('inject'))),
      new Implementations(),
    ); 

    return $inject->get(static::class);
  }
}