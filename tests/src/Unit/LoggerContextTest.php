<?php

declare(strict_types=1);

namespace LoggerExtra\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use LoggerExtra\LoggerContext;
use LoggerExtra\Tests\Mock\TestableLoggerContext;

/**
 * Tests that the logger context is behaving as expected.
 * @group logger_context
 */
#[CoversClass(LoggerContext::class)]
class LoggerContextTest extends TestCase {
  public function setUp(): void {
    parent::setUp();
    TestableLoggerContext::initialize();
  }

  public function tearDown(): void {
    parent::tearDown();
    TestableLoggerContext::uninitialize();
  }

  public function testCapture() {
    $key1 = "key1";
    $value1 = "foo";

    TestableLoggerContext::use([$key1 => $value1], function () use ($key1, $value1) {
      $this->assertEquals([
        $key1 => $value1
      ], TestableLoggerContext::get());

      $key2 = "key2";
      $value2 = "bar";

      TestableLoggerContext::use([$key2 => $value2], function () use ($key1, $key2, $value1, $value2) {
        $this->assertEquals([
          $key1 => $value1,
          $key2 => $value2
        ], TestableLoggerContext::get());

        $key3 = "key3";
        $value3 = "baz";

        TestableLoggerContext::use([$key3 => $value3], function () use ($key1, $key2, $key3, $value1, $value2, $value3) {
          $this->assertEquals([
            $key1 => $value1,
            $key2 => $value2,
            $key3 => $value3
          ], TestableLoggerContext::get());
        });

        $this->assertEquals([
          $key1 => $value1,
          $key2 => $value2
        ], TestableLoggerContext::get());
      });

      $this->assertEquals([
        $key1 => $value1
      ], TestableLoggerContext::get());
    });
  }

  public function testContextIsRestoredWhenCallbackThrows() {
    $key1 = "key1";
    $value1 = "foo";

    TestableLoggerContext::use([$key1 => $value1], function () use ($key1, $value1) {
      $this->assertEquals([
        $key1 => $value1
      ], TestableLoggerContext::get());

      $key2 = "key2";
      $value2 = "bar";

      try {
        TestableLoggerContext::use([$key2 => $value2], function () {
          throw new \RuntimeException("boom");
        });
        $this->fail("Expected exception was not thrown.");
      }
      catch (\RuntimeException $e) {
        $this->assertEquals("boom", $e->getMessage());
      }

      // The context set by the throwing call must be restored, even though
      // it was never returned normally.
      $this->assertEquals([
        $key1 => $value1
      ], TestableLoggerContext::get());
    });
  }
}
