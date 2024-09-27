<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test\test_mocks;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootBehaviour;

class UserAggregateRoot implements AggregateRoot
{
    use AggregateRootBehaviour;

    // Ignore missing event methods
    public function __call($name, $arguments)
    {
    }

    public static function registerUser(UserId $userId, string $name): static
    {
        $root = new static($userId);
        $root->recordThat(new UserRegisteredEvent($userId, $name));
        return $root;
    }

    public function userChangedPassword(UserId $userId, string $password): void
    {
        $this->recordThat(new UserChangedPasswordEvent($userId, $password));
    }
}
