<?php

declare(strict_types=1);

namespace LoggerExtra;

use LoggerExtra\LoggerContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class RequestIdMiddleware implements HttpKernelInterface {
  /**
   * Constructor for RequestIdMiddleware
   *
   * @param HttpKernelInterface $app The next http middleware.
   */
  public function __construct(
    protected HttpKernelInterface $app
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = HttpKernelInterface::MAIN_REQUEST, $catch = true): Response {
    $requestId = $request->headers->get('X-Request-ID', $this->generateId());
    $context = ["request_id" => $requestId];

    $response = LoggerContext::use($context, fn() => $this->app->handle($request, $type, $catch));
    $response->headers->set('X-Request-ID', $requestId);

    return $response;
  }

  /**
   * Generates UUIDv4 style random ID.
   */
  private function generateId(): string {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }
}
