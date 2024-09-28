<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Factories;

use Illuminate\Contracts\Container\Container as LaravelContainer;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Ssmiff\LaravelEventSauce\MessageRepositoryFactory;

abstract class AbstractMessageRepositoryFactory implements MessageRepositoryFactory
{
    public function __construct(protected readonly LaravelContainer $container)
    {
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
    protected function config(string $key): mixed
    {
        return $this->container->get('config')->get($key);
    }
}
