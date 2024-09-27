<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test\test_mocks;

use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;

class NopMessageDecorator implements MessageDecorator
{
    public function decorate(Message $message): Message
    {
        return $message;
    }
}
