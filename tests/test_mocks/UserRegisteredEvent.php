<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test\test_mocks;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class UserRegisteredEvent implements SerializablePayload
{
    public function __construct(public UserId $userId, public string $name)
    {
    }

    public function toPayload(): array
    {
        return [
            'userId' => $this->userId->toString(),
            'name' => $this->name,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            UserId::fromString($payload['userId']),
            $payload['name'],
        );
    }
}
