<?php

declare(strict_types=1);

namespace LoggerExtra;

class ContextVariableState {
    public string $token;
    public mixed $data;
    public int $position;

    function __construct($data, $position) {
        $this->token = bin2hex(openssl_random_pseudo_bytes(16));
        $this->data = $data;
        $this->position = $position;
    }
}

class ContextVariable {
    private string $name;
    private mixed $default;
    private int $position;

    /** @var ContextVariableState[] $states */
    private array $states;

    /** Sentinel value for object being undefined instead of null that can be valid value. */
    private const UNDEFINED = '__UNDEFINED__';

    function __construct(string $name, mixed $default = self::UNDEFINED) {
        $this->name = $name;
        $this->default = $default;
        $this->position = -1;
        $this->states = [];
    }

    public function set(mixed $value): string {
        $this->position = sizeof($this->states);
        $state = new ContextVariableState($value, $this->position);
        array_push($this->states, $state);
        return $state->token;
    }

    public function get(mixed $default = self::UNDEFINED): mixed {
        if (array_key_exists($this->position, $this->states)) {
            return $this->states[$this->position]->data;
        }

        $default = self::valueOrFallback($default, $this->default);

        if (!self::isDefined($default)) {
          throw new \Exception(sprintf("ContextVariable %s does not have any value", $this->name));
        }

        return $default;
    }

    public function reset(string $token): void {
        /** @var ?ContextVariableState $state */
        $state = $this->find($token);

        if (!isset($state)) {
            throw new \Exception(sprintf("ContextVariable %s does not have any token %s", $this->name, $token));
        }

        $this->position = $state->position - 1;
        $this->states = array_slice($this->states, 0, $state->position, true);
    }

    private function find(string $token): ?ContextVariableState {
        $size = count($this->states);

        // Iterate the array backwards, most likely the user is looking for last entry
        for ($i = $size - 1; $i >= 0; $i--) {
            $state = $this->states[$i];
            
            if ($state->token === $token) {
                return $state;
            }
        }

        return null;
    }

    private static function valueOrFallback(mixed $value, mixed $fallback): mixed {
        if (self::isDefined($value)) {
            return $value;
        }

        return $fallback;
    }

    private static function isDefined($value): bool {
        return ($value !== self::UNDEFINED);
      }
}

?>