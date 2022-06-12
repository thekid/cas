<?php namespace de\thekid\cas;

use de\thekid\cas\services\{Services, AllowMatching};
use de\thekid\cas\tickets\Tickets;
use de\thekid\cas\users\Users;
use inject\Bindings;
use lang\IllegalStateException;

/** Default implementations for services, users and tickets */
class Implementations extends Bindings {

  /** @param inject.Injector */
  public function configure($inject) {
    $secret= $inject->get('string', 'secret');

    $inject->bind(Signed::class, new Signed($secret));
    $inject->bind(Encryption::class, new Encryption($secret));
    $inject->bind(Services::class, new AllowMatching($inject->get('string', 'services')));

    $persistence= $inject->get(Persistence::class) ?? throw new IllegalStateException('No persistence configured');
    $inject->bind(Users::class, $persistence->users());
    $inject->bind(Tickets::class, $persistence->tickets());
  }
}