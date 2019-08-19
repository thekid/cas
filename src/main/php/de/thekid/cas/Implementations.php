<?php namespace de\thekid\cas;

use de\thekid\cas\services\{Services, AllowMatching};
use de\thekid\cas\tickets\{Tickets, TicketDatabase};
use de\thekid\cas\users\{Users, UserDatabase};
use inject\Bindings;
use rdbms\DriverManager;

/** Default implementations for services, users and tickets */
class Implementations extends Bindings {

  /** @param inject.Injector */
  public function configure($inject) {
    $conn= DriverManager::getConnection($inject->get('string', 'dsn'));
    $secret= $inject->get('string', 'secret');

    $inject->bind(Signed::class, new Signed($secret));
    $inject->bind(Encryption::class, new Encryption($secret));
    $inject->bind(Services::class, new AllowMatching($inject->get('string', 'services')));
    $inject->bind(Users::class, new UserDatabase($conn));
    $inject->bind(Tickets::class, new TicketDatabase($conn));
  }
}