<?php namespace de\thekid\cas\rdbms;

use de\thekid\cas\tickets\Tickets;
use rdbms\{DBConnection, DriverManager};
use util\{Date, DateUtil};

/**
 * Minimalistic ticket database implementation
 *
 * ticket
 * ======
 * ticket_id int PK
 * value     tinytext
 * created   datetime
 */
class TicketDatabase implements Tickets {
  private $conn;

  /** Creates a new database-driven tickets data source */
  public function __construct(string|DBConnection $conn, private int $timeout= 10) {
    $this->conn= $conn instanceof DBConnection ? $conn : DriverManager::getConnection($conn);
  }

  /** @return string */
  public fn prefix() => 'ST-';

  /**
   * Creates a new ticket
   *
   * @param  var $value
   * @return int|string
   */
  public function create($value) {
    $this->conn->insert('into ticket (value, created) values (%s, %s)', json_encode($value), Date::now());
    return $this->conn->identity();
  }

  /**
   * Looks up and verifies a ticket by a given ID.
   *
   * @param   int|string $id
   * @return  var or NULL to indicate the ticket could not be verified.
   */
  public function validate($id) {

    // Check for ticket
    $ticket= $this->conn->query('select * from ticket where ticket_id = %d', $id)->next();
    if (null === $ticket) return null;

    // Clean up tickets older than maximum timeout
    $timeout= DateUtil::addSeconds(Date::now(), -$this->timeout);
    $this->conn->delete('from ticket where created < %s', $timeout);

    // Verify timeout has not been reached
    $created= $ticket['created'] instanceof Date ? $ticket['created'] : new Date($ticket['created']);
    if ($timeout->isAfter($created)) return null;

    return json_decode($ticket['value'], true);
  }
}
