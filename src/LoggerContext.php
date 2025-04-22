<?php

declare(strict_types=1);

namespace LoggerExtra;

use LoggerExtra\ContextVariable;

class LoggerContext {
  static private ?ContextVariable $_ctx = null;

  static function capture(array $data, callable $callback): void {
      $ctx = self::initialize();
      $merged = array_merge($ctx->get([]), $data);
      $token = $ctx->set($merged);

      try {
        $callback();
      } finally {
        $ctx->reset($token);
      }
  }

  static function get(): array {
    $ctx = self::initialize();
    return $ctx->get();
  }

  static function initialize(): ContextVariable {
    if (self::$_ctx === null) {
      self::$_ctx = new ContextVariable("LoggerContext", []);
    }

    return self::$_ctx;
  }

  static function uninitialize(): void {
    self::$_ctx = null;
  }
}

?>