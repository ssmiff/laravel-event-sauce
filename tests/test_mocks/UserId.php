<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test\test_mocks;

use EventSauce\EventSourcing\AggregateRootId;

readonly class UserId implements AggregateRootId
{
    private function __construct(private string $id)
    {
    }

    public function toString(): string
    {
        return $this->id;
    }

    public static function fromString(string $aggregateRootId): static
    {
        return new static($aggregateRootId);
    }
}
