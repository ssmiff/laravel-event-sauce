<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Factories;

use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\EventSourcing\Serialization\ConstructingPayloadSerializer;
use EventSauce\IdEncoding\StringIdEncoder;
use EventSauce\MessageRepository\IlluminateMessageRepository\IlluminateMessageRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IlluminateMessageRepositoryFactory extends AbstractMessageRepositoryFactory {

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function build(?string $connectionName = null, ?string $tableName = 'domain_messages'): MessageRepository
    {
        return $this->container->makeWith(
            IlluminateMessageRepository::class,
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
}
