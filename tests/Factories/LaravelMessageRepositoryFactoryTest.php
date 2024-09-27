<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test\Factories;

use Closure;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Serialization\ConstructingMessageSerializer;
use EventSauce\IdEncoding\StringIdEncoder;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Ssmiff\LaravelEventSauce\Factories\LaravelMessageRepositoryFactory;
use Ssmiff\LaravelEventSauce\Test\TestCase;

class LaravelMessageRepositoryFactoryTest extends TestCase
{
    #[Test]
    public function it_can_create_message_repository_with_defaults(): void
    {
        $mockConnection = Mockery::mock(Connection::class);

        $mockDatabaseManager = Mockery::mock(
            DatabaseManager::class,
            fn (MockInterface $mock) => $mock
                ->expects('connection')
                ->with('default-database')
                ->andReturn($mockConnection)
        );

        $factory = $this->createFactory(
            function (MockInterface $mock) use ($mockConnection, $mockDatabaseManager) {
                $mock
                    ->expects('get')
                    ->with('config')
                    ->times(3)
                    ->andReturn(
                        new Repository([
                            'eventsauce' => ['message_repository' => 'DummyMessageRepository'],
                            'database' => ['default' => 'default-database'],
                        ])
                    );

                $mock
                    ->expects('get')
                    ->with(DatabaseManager::class)
                    ->andReturn($mockDatabaseManager);

                $mock
                    ->expects('makeWith')
                    ->withArgs(
                        fn (...$args) =>
                            2 == count($args)
                            && 'DummyMessageRepository' == $args[0]
                            && is_array($args[1])
                            && $mockConnection == $args[1]['connection']
                            && $args[1]['tableName'] === 'domain_messages'
                            && $args[1]['serializer'] instanceof ConstructingMessageSerializer
                            && $args[1]['aggregateRootIdEncoder'] instanceof StringIdEncoder
                    )
                    ->andReturn(Mockery::mock(MessageRepository::class));
            }
        );

        $this->assertInstanceOf(MessageRepository::class, $factory->buildMessageRepository());
    }

    #[Test]
    public function it_can_create_message_repository_with_and_db_from_event_sauce_config(): void
    {
        $mockConnection = Mockery::mock(Connection::class);

        $mockDatabaseManager = Mockery::mock(
            DatabaseManager::class,
            fn (MockInterface $mock) => $mock
                ->expects('connection')
                ->with('default-database2')
                ->andReturn($mockConnection)
        );

        $factory = $this->createFactory(
            function (MockInterface $mock) use ($mockConnection, $mockDatabaseManager) {
                $mock
                    ->expects('get')
                    ->with('config')
                    ->times(2)
                    ->andReturn(
                        new Repository([
                            'eventsauce' => [
                                'message_repository' => 'DummyMessageRepository',
                                'database_connection' => 'default-database2'
                            ],
                        ])
                    );

                $mock
                    ->expects('get')
                    ->with(DatabaseManager::class)
                    ->andReturn($mockDatabaseManager);

                $mock
                    ->expects('makeWith')
                    ->withArgs(
                        fn (...$args) =>
                            2 == count($args)
                            && 'DummyMessageRepository' == $args[0]
                            && is_array($args[1])
                            && $mockConnection == $args[1]['connection']
                            && $args[1]['tableName'] === 'domain_messages'
                            && $args[1]['serializer'] instanceof ConstructingMessageSerializer
                            && $args[1]['aggregateRootIdEncoder'] instanceof StringIdEncoder
                    )
                    ->andReturn(Mockery::mock(MessageRepository::class));
            }
        );

        $this->assertInstanceOf(MessageRepository::class, $factory->buildMessageRepository());
    }

    #[Test]
    public function it_can_create_message_repository_parameters(): void
    {
        $mockConnection = Mockery::mock(Connection::class);

        $mockDatabaseManager = Mockery::mock(
            DatabaseManager::class,
            fn (MockInterface $mock) => $mock
                ->expects('connection')
                ->with('dummy-connection')
                ->andReturn($mockConnection)
        );

        $factory = $this->createFactory(
            function (MockInterface $mock) use ($mockConnection, $mockDatabaseManager) {
                $mock
                    ->expects('get')
                    ->with(DatabaseManager::class)
                    ->andReturn($mockDatabaseManager);

                $mock
                    ->expects('makeWith')
                    ->withArgs(
                        fn (...$args) =>
                            2 == count($args)
                            && 'DummyRepositoryByParameter' == $args[0]
                            && is_array($args[1])
                            && $mockConnection == $args[1]['connection']
                            && $args[1]['tableName'] === 'dummy_table_name'
                            && $args[1]['serializer'] instanceof ConstructingMessageSerializer
                            && $args[1]['aggregateRootIdEncoder'] instanceof StringIdEncoder
                    )
                    ->andReturn(Mockery::mock(MessageRepository::class));
            }
        );

        $this->assertInstanceOf(
            MessageRepository::class,
            $factory->buildMessageRepository(
                'DummyRepositoryByParameter',
                'dummy-connection',
                'dummy_table_name',
            )
        );
    }

    private function createFactory(Closure $mockContainer): LaravelMessageRepositoryFactory
    {
        /** @var Container $container */
        $container = Mockery::mock(Container::class, $mockContainer);

        return new LaravelMessageRepositoryFactory($container);
    }
}
