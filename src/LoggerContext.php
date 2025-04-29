<?php

declare(strict_types=1);

namespace LoggerExtra;

use LoggerExtra\ContextVariable;

class LoggerContext {
  private static ?ContextVariable $_ctx = null;

  /**
   * Captures given context and calls the passed function with
   * given context merged with previously active one.
   * 
   * @template T
   * @param callable(): T $fn
   *   Function to be called with newly merged contex.
   * @param array $data
   *   Variables to be appended to logger context
   * @return T Return value of the called function.
   */
  static function use(array $data, callable $fn): mixed {
    $ctx = self::initialize();
    $merged = array_merge($ctx->get([]), $data);
    $token = $ctx->set($merged);

    try {
      return $fn();
    } finally {
      $ctx->reset($token);
    }
  }

  /**
   * Returns the currently active logger context.
   */
  static function get(): array {
    $ctx = self::initialize();
    return $ctx->get();
  }

  /**
   * Initializes the static context variable instance.
   */
  protected static function initialize(): ContextVariable {
    if (self::$_ctx === null) {
      self::$_ctx = new ContextVariable("LoggerContext", []);
    }
  
    return self::$_ctx;
  }
  
  /**
   * Clears the static context variable instance.
   */
  protected static function uninitialize(): void {
    self::$_ctx = null;
  }
}

?>