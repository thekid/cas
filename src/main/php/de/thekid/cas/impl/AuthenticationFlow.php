<?php namespace de\thekid\cas\impl;

use de\thekid\cas\flow\{Flow, UseService, EnterCredentials, QueryMFACode, RedirectToService, DisplaySuccess};
use inject\Bindings;

/* Default authentication flow, including MFA */
class AuthenticationFlow extends Bindings {

  /** @param inject.Injector */
  public function configure($inject) {
    $inject->bind(Flow::class, new Flow([
      $inject->get(UseService::class),
      $inject->get(EnterCredentials::class),
      $inject->get(QueryMFACode::class),
      $inject->get(RedirectToService::class),
      $inject->get(DisplaySuccess::class),
    ]));
  }
}