<?php namespace de\thekid\cas;

use de\thekid\cas\impl\{Login, Logout, Validate, AuthenticationFlow};
use inject\{Injector, ConfiguredBindings};
use security\credentials\{Credentials, FromEnvironment, FromFile};
use web\Application;
use web\handler\FilesFrom;

/**
 * CAS application implementing /login, /logout and /serviceValidate. The CAS 1.0
 * endpoint /validate as well as proxy authentication are not implemented.
 *
 * @see  https://apereo.github.io/cas/6.0.x/protocol/CAS-Protocol-Specification.html
 */
class App extends Application {

  /** @return var */
  public function routes() {
    $credentials= new Credentials(new FromEnvironment(), new FromFile($this->environment->path('credentials')));
    $inject= new Injector(
      new ConfiguredBindings($credentials->expanding($this->environment->properties('inject'))),
      new Implementations(),
      new Frontend($this->environment->path('src/main/handlebars'), 'dev' === $this->environment->profile()),
      new AuthenticationFlow(),
    ); 

    return [
      '/static'          => new FilesFrom($this->environment->path('src/main/webapp')),
      '/serviceValidate' => $inject->get(Validate::class),
      '/login'           => $inject->get(Login::class),
      '/logout'          => $inject->get(Logout::class),
      '/'                => fn($req, $res) => {
        $res->answer(301);
        $res->header('Location', $req->uri()->using()->path('/login')->params($req->params())->create());
      }
    ];
  }
}