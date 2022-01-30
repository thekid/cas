<?php namespace de\thekid\cas\flow;

interface Step {

  /**
   * Sets up this step. Return a transmittable response, e.g. a View
   * or a Redirect instance or NULL to proceed with the `complete()`
   * function.
   */
  public function setup($req, $res, $session);

  /**
   * Complete this step. Returns NULL to complete this step and proceed
   * with the next one, or an error to be shown.
   */
  public function complete($req, $res, $session);
}