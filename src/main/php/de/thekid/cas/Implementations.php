<?php namespace de\thekid\cas;

use de\thekid\cas\services\{Services, AllowMatching};
use de\thekid\cas\tickets\{Tickets, TicketDatabase};
use de\thekid\cas\users\{Users, UserDatabase};
use inject\Bindings;

/** Default implementations for services, users and tickets */
class Implementations extends Bindings {

  /** @param inject.Injector */
  public function configure($inject) {
    $secret= $inject->get('string', 'secret');

    $inject->bind(Signed::class, new Signed($secret));
    $inject->bind(Encryption::class, new Encryption($secret));
    $inject->bind(Services::class, new AllowMatching($inject->get('string', 'services')));
    $inject->bind(Users::class, $inject->get(UserDatabase::class));
    $inject->bind(Tickets::class, $inject->get(TicketDatabase::class));
  }
}