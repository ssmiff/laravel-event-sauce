<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce;

use EventSauce\EventSourcing\AggregateRoot;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository as BaseAggregateRootRepository;
use EventSauce\EventSourcing\EventSourcedAggregateRootRepository;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use EventSauce\EventSourcing\MessageRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Ssmiff\LaravelEventSauce\Exceptions\AggregateRootRepositoryException;
use Ssmiff\LaravelEventSauce\Factories\LaravelMessageRepositoryFactory;

abstract class AggregateRootRepository implements BaseAggregateRootRepository
{
    private ?BaseAggregateRootRepository $repository = null;

    protected string $aggregateRootClassName = '';

    protected ?string $connectionName = null;

    protected string $tableName = 'domain_messages';

    protected ?string $messageRepositoryClassName = null;

    protected ?MessageDispatcher $messageDispatcher = null;

    protected ?MessageDecorator $messageDecorator = null;

    public function __construct(private readonly LaravelMessageRepositoryFactory $messageRepositoryFactory)
    {
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->repository()->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot): void
    {
        $this->repository()->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events): void
    {
        $this->repository()->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function repository(): BaseAggregateRootRepository
    {
        if ($this->repository) {
            return $this->repository;
        }

        return $this->repository = new EventSourcedAggregateRootRepository(
            $this->aggregateRootClassName(),
            $this->messageRepository(),
            $this->messageDispatcher(),
            $this->messageDecorator()
        );
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function messageRepository(): MessageRepository
    {
        return $this->messageRepositoryFactory->buildMessageRepository(
            $this->messageRepositoryClassName(),
            $this->connectionName(),
            $this->tableName()
        );
    }

    protected function aggregateRootClassName(): string
    {
        if (!is_a($this->aggregateRootClassName, AggregateRoot::class, true)) {
            throw AggregateRootRepositoryException::aggregateRootClassNameNotFound($this->aggregateRootClassName);
        }

        return $this->aggregateRootClassName;
    }

    protected function connectionName(): ?string
    {
        return $this->connectionName;
    }

    protected function tableName(): string
    {
        return $this->tableName;
    }

    protected function messageRepositoryClassName(): ?string
    {
        return $this->messageRepositoryClassName;
    }

    protected function messageDispatcher(): ?MessageDispatcher
    {
        return $this->messageDispatcher;
    }

    protected function messageDecorator(): ?MessageDecorator
    {
        return $this->messageDecorator;
    }
}
