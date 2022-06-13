<?php namespace de\thekid\cas\tickets;

abstract class Tickets {

  /** @return string */
  public fn prefix() => 'ST-';

  /**
   * Creates a new ticket
   *
   * @param  var $value
   * @return int|string
   */
  public abstract function create($user);

  /**
   * Looks up and verifies a ticket by a given ID.
   *
   * @param   int|string $id
   * @return  var or NULL to indicate the ticket could not be verified.
   */
  public abstract function validate($id);
}
