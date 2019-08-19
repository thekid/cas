<?php namespace de\thekid\cas\tickets;

interface Tickets {

  /** @return string */
  public function prefix();

  /**
   * Creates a new ticket
   *
   * @param  var $value
   * @return int
   */
  public function create($user);

  /**
   * Looks up and verifies a ticket by a given ID.
   *
   * @param   int $id
   * @return  var or NULL to indicate the ticket could not be verified.
   */
  public function validate($id);
}
