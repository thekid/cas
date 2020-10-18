<?php namespace de\thekid\cas\unittest;

use com\github\mustache\{InMemory, TemplateNotFoundException};
use de\thekid\cas\TemplateEngine;
use io\Path;
use unittest\TestCase;
use web\Response;
use web\io\{TestOutput, Buffered};

class TemplateEngineTest extends TestCase {

  #[Test]
  public function can_create_with_path() {
    new TemplateEngine(new Path('.'));
  }

  #[Test]
  public function can_create_with_template_loader() {
    new TemplateEngine(new InMemory([]));
  }

  #[Test]
  public function render_template_to_response() {
    $fixture= new TemplateEngine(new InMemory(['test' => 'Hello {{name}}']));

    $res= new Response(new TestOutput()->using(Buffered::class));
    $fixture->render($res, 'test', ['name' => 'World']);

    [$headers, $body]= explode("\r\n\r\n", $res->output()->bytes(), 2);
    $this->assertEquals('Hello World', $body);
  }

  #[Test, Expect(TemplateNotFoundException::class)]
  public function non_existant_template() {
    $fixture= new TemplateEngine(new InMemory([]));
    $fixture->render(new Response(new TestOutput()), 'non-existant');
  }
}