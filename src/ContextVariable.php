<?php

declare(strict_types=1);

namespace LoggerExtra;

use LoggerExtra\ContextVariableToken;

class ContextVariable {
  private readonly string $identity;
  private readonly string $name;
  private readonly mixed $default;
  private mixed $value;

  /** Sentinel value for object being undefined instead of null that can be valid value. */
  private const UNDEFINED = '__UNDEFINED__';

  public function __construct(string $name, mixed $default = self::UNDEFINED) {
    $this->identity = bin2hex(openssl_random_pseudo_bytes(32));
    $this->name = $name;
    $this->default = $default;
    $this->value = self::UNDEFINED;
  }

  public function set(mixed $value): ContextVariableToken {
    $token = new ContextVariableToken($this->identity, $this->value);
    $this->value = $value;
    return $token;
  }

  public function get(mixed $default = self::UNDEFINED): mixed {
    if (self::isDefined($this->value)) {
      return $this->value;
    }

    if (self::isDefined($default)) {
      return $default;
    }

    if (self::isDefined($this->default)) {
      return $this->default;
    }

    throw new \Exception(sprintf("ContextVariable %s does not have any value", $this->name));
  }

  public function reset(ContextVariableToken $token): void {
    if ($token->owner !== $this->identity) {
      throw new \Exception(sprintf("Provided token was created in another context"));
    }

    $this->value = $token->oldValue;
  }

  private static function isDefined($value): bool {
    return ($value !== self::UNDEFINED);
  }
}
