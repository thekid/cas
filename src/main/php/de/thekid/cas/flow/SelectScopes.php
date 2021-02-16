<?php namespace de\thekid\cas\flow;

/**
 * Screen where the user grants scopes.
 *
 * @see   https://developer.github.com/apps/building-oauth-apps/understanding-scopes-for-oauth-apps/#requested-scopes-and-granted-scopes
 */
class SelectScopes implements Step {

  public function setup($req, $res, $session) {
    $auth= $session->value('oauth');
    $granted= $session->value('scopes') ?? [];

    // If additional scopes are requested, re-show consent screen
    if ($delta= array_diff_key($auth['scopes'], $granted)) {
      return new View('scopes', [
        'app'     => $auth['client']['name'],
        'scopes'  => $auth['scopes'],
        'service' => $session->value('service'),
        'granted' => $granted,
        'delta'   => $delta,
      ]);
    }

    return null;
  }

  public function complete($req, $res, $session) {
    $scopes= $req->param('scopes');
    if (empty($scopes)) return ['failed' => 'no-scopes'];

    // Store user selection in session
    $granted= [];
    foreach ($session->value('oauth')['scopes'] as $scope => $_) {
      $granted[$scope]= isset($scopes[$scope]);
    }
    $session->register('scopes', $granted);
    return null;
  }
}