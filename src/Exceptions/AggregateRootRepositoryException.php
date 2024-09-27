<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Exceptions;

use EventSauce\EventSourcing\AggregateRoot;
use InvalidArgumentException;

final class AggregateRootRepositoryException extends InvalidArgumentException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function aggregateRootClassNameNotFound(string $aggregateRootClassName): self
    {
        return new self(
            sprintf(
                'aggregateRootClassName "%s" not instance of "%s"',
                $aggregateRootClassName,
                AggregateRoot::class
            )
        );
    }
}
