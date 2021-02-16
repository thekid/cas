<?php namespace de\thekid\cas\impl;

use de\thekid\cas\flow\{Flow, Authorize, EnterCredentials, QueryMFACode, SelectScopes, ContinueAuthorization};
use inject\Injector;

/* OAuth authentication flow, including MFA */
class OAuthFlow extends Flow {

  public function __construct(Injector $inject) {
    parent::__construct([
      $inject->get(Authorize::class),
      $inject->get(EnterCredentials::class),
      $inject->get(QueryMFACode::class),
      $inject->get(SelectScopes::class),
      $inject->get(ContinueAuthorization::class)
    ]);
  }
}