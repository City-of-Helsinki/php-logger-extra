<?php

declare(strict_types=1);

namespace LoggerExtra;

use LoggerExtra\LoggerContext;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class LoggerContextProcessor implements ProcessorInterface {
    public function __invoke(LogRecord $record): LogRecord {
        $context = LoggerContext::get();
        $record->extra = array_merge($record->extra, $context);
        return $record;
    }
}