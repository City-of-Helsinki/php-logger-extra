<?php

declare(strict_types=1);

namespace LoggerExtra\Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use LoggerExtra\LoggerContext;

/**
 * Tests that the logger context is behaving as expected.
 * @group logger_context
 */
#[CoversClass(LoggerContext::class)]
 class LoggerContextTest extends TestCase {
  public function setUp(): void {
    parent::setUp();
    LoggerContext::initialize(); 
  }

  public function tearDown(): void {
    parent::tearDown();
    LoggerContext::uninitialize();
  }

  public function testCapture() {
    $key1 = "key1";
    $value1 = "foo";

    LoggerContext::capture([$key1 => $value1], function () use ($key1, $value1) {
      $this->assertEquals([
        $key1 => $value1
      ], LoggerContext::get());
      
      $key2 = "key2";
      $value2 = "bar";

      LoggerContext::capture([$key2 => $value2], function () use ($key1, $key2, $value1, $value2) {
        $this->assertEquals([
          $key1 => $value1,
          $key2 => $value2
        ], LoggerContext::get());
        
        $key3 = "key3";
        $value3 = "baz";

        LoggerContext::capture([$key3 => $value3], function () use ($key1, $key2, $key3, $value1, $value2, $value3) {
          $this->assertEquals([
            $key1 => $value1,
            $key2 => $value2,
            $key3 => $value3
          ], LoggerContext::get());
        });

        $this->assertEquals([
          $key1 => $value1,
          $key2 => $value2
        ], LoggerContext::get());
      });

      $this->assertEquals([
        $key1 => $value1
      ], LoggerContext::get());
    });
  }
}

?>