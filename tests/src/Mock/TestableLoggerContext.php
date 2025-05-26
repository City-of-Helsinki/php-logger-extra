<?php

declare(strict_types=1);

namespace LoggerExtra\Tests\Mock;

use LoggerExtra\ContextVariable;
use LoggerExtra\LoggerContext;

/**
 * Make the initialize and uninitialize calls public for testing purposes.
 */
class TestableLoggerContext extends LoggerContext {
  public static function initialize(): ContextVariable {
    return parent::initialize();
  }

  public static function uninitialize(): void {
    parent::uninitialize();
  }
}
