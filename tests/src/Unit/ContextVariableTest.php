<?php

declare(strict_types=1);

namespace LoggerExtra\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

use LoggerExtra\ContextVariable;

/**
 * Tests that the context variable is behaving as expected.
 * @group logger_context
 */
#[CoversClass(ContextVariable::class)]
class ContextVariableTest extends TestCase {
  protected ContextVariable $ctx;

  public function setUp(): void {
    parent::setUp();
    $this->ctx = new ContextVariable("Test");
  }

  public function testExpectToThrowWithoutDefault() {
    $this->expectException(\Exception::class);
    $this->ctx->get();
  }

  public function testExpectNotToThrowWithDefaultGet() {
    $actual = $this->ctx->get(null);
    $this->assertEquals(null, $actual);
  }

  public function testExpectNotToThrowWithDefaultCtor() {
    $this->ctx = new ContextVariable("Test", null);
    $actual = $this->ctx->get();
    $this->assertEquals(null, $actual);
  }

  public function testReset() {
    $value1 = "foo";
    $token1 = $this->ctx->set($value1);
    $this->assertEquals($value1, $this->ctx->get(null));

    $value2 = "bar";
    $token2 = $this->ctx->set($value2);
    $this->assertEquals($value2, $this->ctx->get(null));

    $value3 = "baz";
    $token3 = $this->ctx->set($value3);
    $this->assertEquals($value3, $this->ctx->get(null));

    $this->ctx->reset($token3);
    $this->assertEquals($value2, $this->ctx->get(null));

    $this->ctx->reset($token2);
    $this->assertEquals($value1, $this->ctx->get(null));

    $this->ctx->reset($token1);
    $this->assertEquals(null, $this->ctx->get(null));
  }
}

?>