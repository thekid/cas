<?php namespace de\thekid\cas\mongodb;

use com\mongodb\{Collection, Document, ObjectId};
use de\thekid\cas\tickets\Tickets;
use util\{Date, Dates};

/**
 * Users implementation storing users in a MongoDB database
 */
class TicketCollection extends Tickets {

  /** Creates a new MongoDB-based tickets data source */
  public function __construct(private Collection $collection, private int $timeout= 10) { }

  /**
   * Creates a new ticket
   *
   * @param  var $value
   * @return int
   */
  public function create($value) {
    $result= $this->collection->insert(new Document([
      'value'   => $value,
      'created' => Date::now()
    ]));
    return $result->id()->string();
  }

  /**
   * Looks up and verifies a ticket by a given ID.
   *
   * @param   int|string $id
   * @return  var or NULL to indicate the ticket could not be verified.
   */
  public function validate($id) {

    // Check for ticket
    $ticket= $this->collection->find(new ObjectId($id))->first();
    if (null === $ticket) return null;

    // Clean up tickets older than maximum timeout
    $timeout= Dates::add(Date::now(), -$this->timeout);
    $this->collection->delete(['created' => ['$lt' => $timeout]]);

    // Verify timeout has not been reached
    if ($timeout->isAfter($ticket['created'])) return null;

    return $ticket['value'];
  }
}
