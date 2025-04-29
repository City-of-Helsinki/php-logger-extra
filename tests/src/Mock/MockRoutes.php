<?php

declare(strict_types=1);

namespace LoggerExtra\Tests\Mock;

use LoggerExtra\LoggerContext;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Exception\RuntimeException;

class MockRoutes {
  public static function create(Logger $logger): RouteCollection {
    $routes = new RouteCollection();
    $routes->add('nop', new Route('/nop', [
      '_controller' => function () {
        return new Response();
      }
    ]));

    /**
     * Generate a log entry.
     */
    $routes->add('hello', new Route('/hello', [
      '_controller' => function () use ($logger) {
        $logger->info("Hello, world!");
        return new Response("Hello, world!");
      }
    ]));

    /**
     * Generate a log entry with context taken from query parameters.
     * E.g. /parrot?q=123&foo=bar adds {"q": "123", "foo": "bar"} in context.
     */
    $routes->add('parrot', new Route('/parrot', [
      '_controller' => function (Request $request) use ($logger) {
        $ctx = $request->query->all();

        LoggerContext::use($ctx, function () use ($logger) {
          $logger->info("Polly wants a cookie!");
        });
        
        return new JsonResponse($ctx);
      }
    ]));

    $routes->add('error', new Route('/error', [
      '_controller' => function () {
        throw new RuntimeException("Oh no!", Response::HTTP_INTERNAL_SERVER_ERROR);
      }
    ]));

    return $routes;
  }
}