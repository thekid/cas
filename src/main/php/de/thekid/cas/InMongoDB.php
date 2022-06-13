<?php namespace de\thekid\cas;

use com\mongodb\MongoConnection;
use de\thekid\cas\mongodb\{UserCollection, TicketCollection};
use de\thekid\cas\tickets\Tickets;
use de\thekid\cas\users\Users;

/**
 * Persists users and tickets in a MongoDB database, to which the
 * connection is passed in the constructor.
 *
 * @see de.thekid.cas.Implementations
 */
class InMongoDB implements Persistence {
  private $database, $users, $tickets;

  /** Creates a new MongoDB-driven datasource */
  public function __construct(string|MongoConnection $conn, string $database) {
    $this->database= ($conn instanceof MongoConnection ? $conn : new MongoConnection($conn))->database($database);
  }

  /** Returns users persistence */
  public function users(): Users {
    return $this->users??= new UserCollection($this->database->collection('users'));
  }

  /** Returns tickets persistence */
  public function tickets(): Tickets {
    return $this->tickets??= new TicketCollection($this->database->collection('tickets'));
  }
}