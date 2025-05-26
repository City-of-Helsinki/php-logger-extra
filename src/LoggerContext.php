<?php

declare(strict_types=1);

namespace LoggerExtra;

use LoggerExtra\ContextVariable;

class LoggerContext {
  private static ?ContextVariable $ctx = null;

  /**
   * Captures given context and calls the passed function with
   * given context merged with previously active one.
   *
   * @template T
   * @param callable(): T $fn
   *   Function to be called with merged logger context.
   * @param array $data
   *   Variables to be merged into logger context
   * @return T Return value of the called function.
   */
  public static function use(array $data, callable $fn): mixed {
    /** @var ?string $token */
    $token = null;

    try {
      $ctx = self::initialize();
      $merged = array_merge($ctx->get([]), $data);
      $token = $ctx->set($merged);
      return $fn();
    } finally {
      $ctx->reset($token);
    }
  }

  /**
   * Returns the active logger context.
   */
  public static function get(): array {
    $ctx = self::initialize();
    return $ctx->get([]);
  }

  /**
   * Initializes the static context variable instance.
   */
  protected static function initialize(): ContextVariable {
    if (self::$ctx === null) {
      self::$ctx = new ContextVariable("LoggerContext", []);
    }

    return self::$ctx;
  }

  /**
   * Clears the static context variable instance.
   */
  protected static function uninitialize(): void {
    self::$ctx = null;
  }
}
