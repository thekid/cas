<?php namespace de\thekid\cas\tickets;

use rdbms\DBConnection;
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

  /** Creates a new database-driven tickets data source */
  public function __construct(private DBConnection $conn, private int $timeout= 10) { }

  /** @return string */
  public fn prefix() => 'ST-';

  /**
   * Creates a new ticket
   *
   * @param  var $value
   * @return int
   */
  public function create($value) {
    $this->conn->insert('into ticket (value, created) values (%s, now())', json_encode($value));
    return $this->conn->identity();
  }

  /**
   * Looks up and verifies a ticket by a given ID.
   *
   * @param   int $id
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
    if ($timeout->isAfter($ticket['created'])) return null;

    return json_decode($ticket['value'], true);
  }
}
