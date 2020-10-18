<?php namespace de\thekid\cas;

use xml\{Tree, Node};

/** CAS service response - in XML and JSON */
abstract enum ServiceResponse {
  XML {
    public function success($user) {
      $n= new Node('cas:authenticationSuccess')->withChild(new Node('cas:user', $user['username']));
      if (isset($user['attributes'])) {
        $a= $n->addChild('cas:attributes');
        foreach ($user['attributes'] as $key => $value) {
          $a->addChild(new Node('cas:'.$key, $value));
        }
      }
      return $n;
    }

    public function failure($code, $message, ... $args) {
      return new Node('cas:authenticationFailure', vsprintf($message, $args), ['code' => $code]);
    }

    public function transmit($response, $result) {
      $tree= new Tree()
        ->withRoot(new Node('cas:serviceResponse', null, ['xmlns:cas' => 'http://www.yale.edu/tp/cas'])
          ->withChild($result)
        )
      ;

      $response->send($tree->getSource(INDENT_DEFAULT), 'text/xml');
    }
  },
  JSON {
    public function success($user) {
      $success= ['user' => $user['username']];
      if (isset($user['attributes'])) {
        $success['attributes']= [];
        foreach ($user['attributes'] as $key => $value) {
          $success['attributes'][$key]= $value;
        }
      }
      return ['authenticationSuccess' => $success];
    }

    public function failure($code, $message, ... $args) {
      return ['authenticationFailure' => ['code' => $code, 'description' => vsprintf($message, $args)]];
    }

    public function transmit($response, $result) {
      $response->send(json_encode(['serviceResponse' => $result]), 'application/json');
    }
  };

  /** Creates a new ServiceResponse instance for a given format name. Defaults to XML */
  public static function forFormat(?string $name): self {
    return 'json' === strtolower($name) ? self::$JSON : self::$XML;
  }

  /**
   * Creates a ServiceResponse indicating success
   *
   * @param  string $user
   * @return var
   */
  public abstract function success($user);

  /**
   * Creates a ServiceResponse indicating success
   *
   * @param  string $code
   * @param  string $message
   * @param  var... $args
   * @return var
   */
  public abstract function failure($code, $message, ... $args);

  /**
   * Transmits this ServiceResponse
   *
   * @param  web.Response $response
   * @param  var $result
   * @return void
   */
  public abstract function transmit($response, $result);
}
