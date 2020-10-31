<?php namespace de\thekid\cas\impl;

use de\thekid\cas\flow\{Flow, UseService, EnterCredentials, QueryMFACode, RedirectToService, DisplaySuccess};
use inject\Injector;

/* CAS authentication flow, including MFA */
class CasFlow extends Flow {

  public function __construct(Injector $inject) {
    parent::__construct([
      $inject->get(UseService::class),
      $inject->get(EnterCredentials::class),
      $inject->get(QueryMFACode::class),
      $inject->get(RedirectToService::class),
      $inject->get(DisplaySuccess::class),
    ]);
  }
}