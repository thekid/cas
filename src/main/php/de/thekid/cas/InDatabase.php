<?php namespace de\thekid\cas;

use de\thekid\cas\rdbms\{UserDatabase, TicketDatabase};
use de\thekid\cas\tickets\Tickets;
use de\thekid\cas\users\Users;
use rdbms\{DBConnection, DriverManager};

/**
 * Persists users and tickets in a relational database, to which the
 * connection is passed in the constructor.
 *
 * @see de.thekid.cas.Implementations
 */
class InDatabase implements Persistence {
  private $conn, $users, $tickets;

  /** Creates a new database-driven datasource */
  public function __construct(string|DBConnection $conn) {
    $this->conn= $conn instanceof DBConnection ? $conn : DriverManager::getConnection($conn);
  }

  /** Returns users persistence */
  public function users(): Users {
    return $this->users??= new UserDatabase($this->conn);
  }

  /** Returns tickets persistence */
  public function tickets(): Tickets {
    return $this->tickets??= new TicketDatabase($this->conn);
  }
}