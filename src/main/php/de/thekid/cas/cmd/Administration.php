<?php namespace de\thekid\cas\cmd;

use de\thekid\cas\{Encryption, Implementations, Persistence};
use inject\{Injector, ConfiguredBindings};
use security\credentials\{Credentials, FromEnvironment, FromFile};
use util\cmd\{Command, Config};

abstract class Administration extends Command {

  public function __construct(protected Persistence $persistence, protected Encryption $encryption) { }

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