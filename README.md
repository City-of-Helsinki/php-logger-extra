
# Logger Extras for simplified structured logging

`php-logger-extra` is a collection of utilities that simplify structured logging setup in PHP applications. It builds on top of [Monolog](https://github.com/Seldaek/monolog), and is designed to let you accumulate logging context incrementally, without needing to pass everything explicitly through the call stack.

This is accomplished using the static method `LoggerContext::use`, in combination with the `LoggerContextProcessor` class, which injects context data into the logger automatically.

---

## Installation

To install this package using Composer, first add the repository to your `composer.json`:

```json
"repositories": [
  ...,
  {
    "type": "vcs",
    "url": "https://github.com/City-of-Helsinki/php-resilient-logger.git"
  }
]
```

Then install the package:

```bash
composer require city-of-helsinki/php-resilient-logger
```

---

## Configuration

### Context Processor

#### YAML Configuration

To use the context processor with Monolog in Drupal, update your YAML configuration like this:

```yaml
parameters:
  ...
  monolog.processors: [..., 'logger_context']

services:
  ...
  monolog.processor.logger_context:
    class: LoggerExtra\LoggerContextProcessor
```

#### Programmatic Configuration

You can also register the processor in code using a custom service provider:

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use LoggerExtra\LoggerContextProcessor;

class MyServiceProvider extends ServiceProviderBase {
  public function register(ContainerBuilder $container): void {
    $container->setParameter('monolog.channel_handlers', [
      'default' => [
        'handlers' => [
          [
            ...,
            'processors' => [
              ...,
              'logger_context',
            ],
          ],
        ],
      ],
    ]);

    if (!$container->has('monolog.processor.logger_context')) {
      $container->register('monolog.processor.logger_context', LoggerContextProcessor::class);
    }
  }
}
```

---

### Request ID Middleware

#### YAML Configuration

```yaml
services:
  logger_extra.request_id_middleware:
    class: LoggerExtra\RequestIdMiddleware
    tags:
      - { name: http_middleware, priority: 250 }
```

#### Programmatic Configuration

Alternatively, register the middleware via a service provider:

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use LoggerExtra\RequestIdMiddleware;

class MyServiceProvider extends ServiceProviderBase {
  public function alter(ContainerBuilder $container): void {
    $definition = $container->getDefinition('http_kernel');
    $definition->setClass(RequestIdMiddleware::class);
    $definition->setArguments([
      new Reference('http_kernel'),
    ]);
  }
}
```

## Usage: Logger Context

You can add context using `LoggerContext::use`, which temporarily sets context values for the duration of a callback. The current context can be retrieved with `LoggerContext::get`.

```php
use LoggerExtra\LoggerContext;

$logger = \Drupal::logger("test");

function bar() {
  LoggerContext::use(['who' => 'World'], function () use ($logger) {
    $ctx = LoggerContext::get();
    $logger->info("{$ctx['greet']} {$ctx['who']}");
  });
}

function foo() {
  LoggerContext::use(['greet' => 'Hello'], function () {
    bar();
    return ['result' => 'OK'];
  });
}
```

This results in a log entry similar to:

```json
{
  "message": "Hello World",
  "level": "INFO",
  "time": "2025-04-14T11:08:22.962222+00:00",
  "context": {
    "greet": "Hello",
    "who": "World"
  }
}
```

---

## Development

### Install Dependencies

```bash
composer install
```

### Running Tests

```bash
./vendor/bin/phpunit
```

---

## Code Formatting

This project uses [PHP_CodeSniffer (phpcs)](https://github.com/squizlabs/PHP_CodeSniffer) for formatting and code quality checks.

Common commands:

- Lint code:

  ```bash
  ./vendor/bin/phpcs
  ```

- Apply safe fixes:

  ```bash
  ./vendor/bin/phpcbf
  ```

---

## Commit Message Format

All commit messages must follow the [Conventional Commits](https://www.conventionalcommits.org/) specification, and each line must not exceed 72 characters.

If you're using [`pre-commit`](https://pre-commit.com/), [`commitlint`](https://github.com/conventional-changelog/commitlint) will validate your commit messages automatically.
