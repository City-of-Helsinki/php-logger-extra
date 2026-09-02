<?php

declare(strict_types=1);

namespace LoggerExtra;

/**
 * Restores a ContextVariable to its previous value once the guard is
 * destroyed, i.e. once it goes out of scope.
 *
 * This lets LoggerContext::use() guarantee cleanup on both normal return
 * and exceptions without a try/finally block: PHP destroys local variables
 * of a stack frame as it is torn down, including while an exception is
 * unwinding, so the restore always happens.
 */
final class ContextVariableResetGuard {
  public function __construct(
    private readonly ContextVariable $ctx,
    private readonly ContextVariableToken $token,
  ) {
  }

  public function __destruct() {
    $this->ctx->reset($this->token);
  }
}
