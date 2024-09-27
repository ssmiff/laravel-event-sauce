<?php

declare(strict_types=1);

return [
    /*
     * This connection name will be used to storage messages. When
     * set to null the default connection will be used.
     */
    'database_connection' => null,

    /*
     * This class will be used to store messages.
     *
     * You may change this to any class that implements
     * \EventSauce\EventSourcing\MessageRepository
     */
    'message_repository' => \EventSauce\MessageRepository\IlluminateMessageRepository\IlluminateMessageRepository::class,
];
