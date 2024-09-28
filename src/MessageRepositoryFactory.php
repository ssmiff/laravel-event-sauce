<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce;

use EventSauce\EventSourcing\MessageRepository;

interface MessageRepositoryFactory
{
    public function build(?string $connectionName = null, ?string $tableName = 'domain_messages'): MessageRepository;
}
