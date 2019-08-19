<?php namespace de\thekid\cas;

use inject\{Injector, ConfiguredBindings};
use io\Path;
use security\credentials\{Credentials, FromEnvironment, FromFile};
use web\handler\FilesFrom;
use web\{Application, Filters};

/**
 * CAS application implementing /login, /logout and /serviceValidate. The CAS 1.0
 * endpoint /validate as well as proxy authentication are not implemented.
 *
 * @see  https://apereo.github.io/cas/6.0.x/protocol/CAS-Protocol-Specification.html
 */
class App extends Application {

  /** @return var */
  public function routes() {
    $webroot= $this->environment->webroot();
    $credentials= new Credentials(new FromEnvironment(), new FromFile(new Path($webroot, 'credentials')));
    $inject= new Injector(
      new ConfiguredBindings($credentials->expanding($this->environment->properties('inject'))),
      new Implementations(),
      new Frontend($webroot, 'dev' === $this->environment->profile()),
      new AuthenticationFlow(),
    ); 

    $files= new FilesFrom(new Path($webroot, 'src/main/webapp'));
    return [
      '/favicon.ico'     => $files,
      '/static'          => $files,
      '/serviceValidate' => $inject->get(Validate::class),
      '/login'           => $inject->get(Login::class),
      '/logout'          => $inject->get(Logout::class),
      '/'                => fn($req, $res) => $req->dispatch('/login', $req->params()),
    ];
  }
}