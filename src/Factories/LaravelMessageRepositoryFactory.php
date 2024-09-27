<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Factories;

use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\ConstructingPayloadSerializer;
use EventSauce\IdEncoding\StringIdEncoder;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container as LaravelContainer;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class LaravelMessageRepositoryFactory
{
    public function __construct(private readonly LaravelContainer $container)
    {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function buildMessageRepository(
        ?string $messageRepositoryClass = null,
        ?string $connectionName = null,
        ?string $tableName = 'domain_messages',
    ): MessageRepository {
        $messageRepositoryClass = $messageRepositoryClass ?? $this->config('eventsauce.message_repository');

        return $this->container->makeWith(
            $messageRepositoryClass,
            [
                'connection' => $this->connection($connectionName),
                'tableName' => $tableName,
                'serializer' => new ConstructingMessageSerializer(
                    payloadSerializer: new ConstructingPayloadSerializer()
                ),
                'aggregateRootIdEncoder' => new StringIdEncoder()
            ]
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function connection(?string $connectionName = null): Connection
    {
        $connectionName = $connectionName
            ?? $this->config('eventsauce.database_connection')
            ?? $this->config('database.default');

        return $this->container->get(DatabaseManager::class)->connection($connectionName);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function config(string $key): mixed
    {
        return $this->container->get('config')->get($key);
    }
}
