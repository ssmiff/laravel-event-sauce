<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test;

use EventSauce\EventSourcing\CollectingMessageDispatcher;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\InMemoryMessageRepository;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageDecorator;
use EventSauce\EventSourcing\MessageDispatcher;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Ssmiff\LaravelEventSauce\AggregateRootRepository;
use Ssmiff\LaravelEventSauce\MessageRepositoryFactory;
use Ssmiff\LaravelEventSauce\Test\test_mocks\NopMessageDecorator;
use Ssmiff\LaravelEventSauce\Test\test_mocks\UserAggregateRoot;
use Ssmiff\LaravelEventSauce\Test\test_mocks\UserChangedPasswordEvent;
use Ssmiff\LaravelEventSauce\Test\test_mocks\UserId;
use Ssmiff\LaravelEventSauce\Test\test_mocks\UserRegisteredEvent;

class AggregateRootRepositoryTest extends TestCase
{
    #[Test]
    public function it_can_persist_expected_event_to_message_repository(): void
    {
        $messageRepository = new InMemoryMessageRepository();

        /** @var MessageRepositoryFactory $mockMessageRepositoryFactory */
        $mockMessageRepositoryFactory = Mockery::mock(
            MessageRepositoryFactory::class,
            fn(MockInterface $mock) => $mock
                ->expects('buildMessageRepository')
                ->with(
                    'my_message_repository',
                    'my-connection-name',
                    'my_domain_messages'
                )
                ->andReturn($messageRepository)
        );

        $aggregateRootRepository = new class($mockMessageRepositoryFactory) extends AggregateRootRepository {
            protected string $aggregateRootClassName = UserAggregateRoot::class;
            protected ?string $connectionName = 'my-connection-name';
            protected string $tableName = 'my_domain_messages';
            protected ?string $messageRepositoryClassName = 'my_message_repository';

            protected function messageDecorator(): ?MessageDecorator
            {
                return new NopMessageDecorator();
            }
        };

        $userId = UserId::fromString('123');
        $userAggregateRoot = UserAggregateRoot::registerUser($userId, 'scott');

        $aggregateRootRepository->persist($userAggregateRoot);

        $messages = [];
        foreach ($messageRepository->retrieveAll($userId) as $message) {
            $messages[] = $message;
        }

        $this->assertCount(1, $messages);

        $message = $messages[0];

        $payload = $message->payload();

        $this->assertInstanceOf(UserRegisteredEvent::class, $payload);
        $this->assertInstanceOf(UserId::class, $payload->userId);
        $this->assertSame('123', $payload->userId->toString());
        $this->assertSame('scott', $payload->name);
        $this->assertInstanceOf(UserId::class, $message->aggregateRootId());
        $this->assertSame('123', $message->aggregateRootId()->toString());
        $this->assertSame(
            'ssmiff.laravel_event_sauce.test\test_mocks.user_aggregate_root',
            $message->aggregateRootType()
        );
        $this->assertSame(1, $message->aggregateVersion());
    }

    #[Test]
    public function it_can_retrieve_and_persist_expected_event_to_message_repository(): void
    {
        $messageRepository = new InMemoryMessageRepository();

        /** @var MessageRepositoryFactory $mockMessageRepositoryFactory */
        $mockMessageRepositoryFactory = Mockery::mock(
            MessageRepositoryFactory::class,
            fn(MockInterface $mock) => $mock
                ->expects('buildMessageRepository')
                ->with(
                    'my_message_repository',
                    'my-connection-name',
                    'my_domain_messages'
                )
                ->andReturn($messageRepository)
        );

        $aggregateRootRepository = new class($mockMessageRepositoryFactory) extends AggregateRootRepository {
            protected string $aggregateRootClassName = UserAggregateRoot::class;
            protected ?string $connectionName = 'my-connection-name';
            protected string $tableName = 'my_domain_messages';
            protected ?string $messageRepositoryClassName = 'my_message_repository';

            protected function messageDecorator(): ?MessageDecorator
            {
                return new NopMessageDecorator();
            }
        };

        $userId = UserId::fromString('123');

        $fakeMessage = (new Message(
            new UserRegisteredEvent($userId, 'scott')
        ))
            ->withHeader(Header::AGGREGATE_ROOT_VERSION, 1)
            ->withHeader(Header::AGGREGATE_ROOT_ID, $userId)
            ->withHeader(
                Header::AGGREGATE_ROOT_TYPE,
                'ssmiff.laravel_event_sauce.test\test_mocks.user_aggregate_root'
            );
        $messageRepository->persist($fakeMessage);

        /** @var UserAggregateRoot $userAggregateRoot */
        $userAggregateRoot = $aggregateRootRepository->retrieve($userId);
        $userAggregateRoot->userChangedPassword($userId, 'some-password');
        $aggregateRootRepository->persist($userAggregateRoot);

        $messages = [];
        foreach ($messageRepository->retrieveAll($userId) as $message) {
            $messages[] = $message;
        }

        $this->assertCount(2, $messages);

        $message = $messages[0];
        $payload = $message->payload();

        $this->assertInstanceOf(UserRegisteredEvent::class, $payload);
        $this->assertInstanceOf(UserId::class, $payload->userId);
        $this->assertSame('123', $payload->userId->toString());
        $this->assertSame('scott', $payload->name);
        $this->assertInstanceOf(UserId::class, $message->aggregateRootId());
        $this->assertSame('123', $message->aggregateRootId()->toString());
        $this->assertSame(
            'ssmiff.laravel_event_sauce.test\test_mocks.user_aggregate_root',
            $message->aggregateRootType()
        );
        $this->assertSame(1, $message->aggregateVersion());

        $message = $messages[1];
        $payload = $message->payload();

        $this->assertInstanceOf(UserChangedPasswordEvent::class, $payload);
        $this->assertInstanceOf(UserId::class, $payload->userId);
        $this->assertSame('123', $payload->userId->toString());
        $this->assertSame('some-password', $payload->password);
        $this->assertInstanceOf(UserId::class, $message->aggregateRootId());
        $this->assertSame('123', $message->aggregateRootId()->toString());
        $this->assertSame(
            'ssmiff.laravel_event_sauce.test\test_mocks.user_aggregate_root',
            $message->aggregateRootType()
        );
        $this->assertSame(2, $message->aggregateVersion());
    }

    #[Test]
    public function it_dispatches_messages(): void
    {
        $messageRepository = new InMemoryMessageRepository();

        /** @var MessageRepositoryFactory $mockMessageRepositoryFactory */
        $mockMessageRepositoryFactory = Mockery::mock(
            MessageRepositoryFactory::class,
            fn(MockInterface $mock) => $mock
                ->expects('buildMessageRepository')
                ->with(
                    'my_message_repository',
                    'my-connection-name',
                    'my_domain_messages'
                )
                ->andReturn($messageRepository)
        );

        $dispatcher = new CollectingMessageDispatcher();

        $aggregateRootRepository = new class(
            $mockMessageRepositoryFactory,
            $dispatcher
        ) extends AggregateRootRepository {
            public function __construct(
                MessageRepositoryFactory $messageRepositoryFactory,
                private readonly MessageDispatcher $dispatcher
            ) {
                parent::__construct($messageRepositoryFactory);
            }

            protected string $aggregateRootClassName = UserAggregateRoot::class;
            protected ?string $connectionName = 'my-connection-name';
            protected string $tableName = 'my_domain_messages';
            protected ?string $messageRepositoryClassName = 'my_message_repository';

            protected function messageDecorator(): ?MessageDecorator
            {
                return new NopMessageDecorator();
            }

            protected function messageDispatcher(): ?MessageDispatcher
            {
                return $this->dispatcher;
            }
        };

        $userId = UserId::fromString('123');
        $userAggregateRoot = UserAggregateRoot::registerUser($userId, 'scott');
        $aggregateRootRepository->persist($userAggregateRoot);

        $messages = [];
        foreach ($messageRepository->retrieveAll($userId) as $message) {
            $messages[] = $message;
        }

        $this->assertCount(1, $messages);

        $dispatchedMessages = $dispatcher->collectedMessages();

        $this->assertSame($messages, $dispatchedMessages);
    }
}
