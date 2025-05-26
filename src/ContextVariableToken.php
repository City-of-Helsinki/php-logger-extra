<?php

declare(strict_types=1);

namespace LoggerExtra;

class ContextVariableToken {
  public readonly string $owner;
  public readonly mixed $oldValue;

  public function __construct(string $owner, mixed $oldValue) {
    $this->owner = $owner;
    $this->oldValue = $oldValue;
  }
}
