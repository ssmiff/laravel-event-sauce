<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test\test_mocks;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

class UserChangedPasswordEvent implements SerializablePayload
{
    public function __construct(public UserId $userId, public string $password)
    {
    }

    public function toPayload(): array
    {
        return [
            'userId' => $this->userId->toString(),
            'password' => $this->password,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            UserId::fromString($payload['userId']),
            $payload['password'],
        );
    }
}
