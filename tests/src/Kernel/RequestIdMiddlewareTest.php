<?php

declare(strict_types=1);

namespace LoggerExtra\Tests\Kernel;

use LoggerExtra\LoggerContextProcessor;
use LoggerExtra\RequestIdMiddleware;
use LoggerExtra\Tests\Mock\MockRoutes;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

use Monolog\Logger;
use Monolog\Handler\TestHandler;

#[CoversClass(RequestIdMiddleware::class)]
class RequestIdMiddlewareTest extends TestCase {
  private TestHandler $testHandler;
  private HttpKernel $kernel;

  public function setUp(): void {
    $testHandler = new TestHandler();
    $processor = new LoggerContextProcessor();
    $logger = new Logger("logger", [$testHandler], [$processor]);
    $requestStack = new RequestStack();
    $requestContext = new RequestContext();
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(
      new RouterListener(
        new UrlMatcher(MockRoutes::create($logger), $requestContext),
        $requestStack
      )
    );

    $dispatcher->addSubscriber(new ErrorListener(null, $logger));
    $controllerResolver = new ControllerResolver();
    $argumentResolver = new ArgumentResolver();
    $kernel = new HttpKernel(
      $dispatcher,
      $controllerResolver,
      $requestStack,
      $argumentResolver
    );

    $this->testHandler = $testHandler;
    $this->kernel = $kernel;
  }

  public function tearDown(): void {
  }
  
  public function testGenerateRequestIdIfNotSet() {
    $middleware = new RequestIdMiddleware($this->kernel);
    $request = Request::create("/hello");
    $middleware->handle($request);

    $records = $this->testHandler->getRecords();
    $this->assertTrue(count($records) === 1);
    
    $record = $records[0];
    $this->assertNotNull($record);
    $this->assertNotNull($record->extra["request_id"]);
  }

  public function testUseRequestIdFromHeader() {
    $middleware = new RequestIdMiddleware($this->kernel);
    $request = Request::create("/hello", );
    $request->headers->set('X-Request-ID', "foo");
    $middleware->handle($request);
    
    $records = $this->testHandler->getRecords();
    $this->assertTrue(count($records) === 1);

    $record = $records[0];
    $this->assertNotNull($record);
    $this->assertEquals("foo", $record->extra["request_id"]);
  }


  public function testAddLoggerContextInLogRecord() {
    $middleware = new RequestIdMiddleware($this->kernel);
    $request = Request::create("/parrot", );
    $request->query->set('foo', "bar");
    $middleware->handle($request);

    $records = $this->testHandler->getRecords();
    $this->assertTrue(count($records) === 1);

    $record = $records[0];
    $this->assertNotNull($record);
    $this->assertEquals("bar", $record->extra["foo"]);
  } 

  public function testRequestIdIsLoggedOnError() {
    $middleware = new RequestIdMiddleware($this->kernel);
    $request = Request::create("/error");
    $request->headers->set('X-Request-ID', "foo");

    $this->expectException(\RuntimeException::class);
    $middleware->handle($request);

    $records = $this->testHandler->getRecords();
    $this->assertTrue(count($records) === 1);
    
    $record = $records[0];
    $this->assertNotNull($record);
    $this->assertEquals("foo", $record->extra["request_id"]);
  }
}
