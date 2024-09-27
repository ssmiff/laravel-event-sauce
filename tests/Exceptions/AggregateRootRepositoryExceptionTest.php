<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test\Exceptions;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Ssmiff\LaravelEventSauce\Exceptions\AggregateRootRepositoryException;
use Ssmiff\LaravelEventSauce\Test\TestCase;

class AggregateRootRepositoryExceptionTest extends TestCase
{
    #[Test]
    public function is_instance_of_invalid_argument_exception(): void
    {
        $this->assertInstanceOf(
            InvalidArgumentException::class,
            AggregateRootRepositoryException::aggregateRootClassNameNotFound('test')
        );
    }

    public function aggregate_root_class_name_not_found_returns_expected(): void
    {
        $this->assertSame(
            'aggregateRootClassName not found or not set',
            AggregateRootRepositoryException::aggregateRootClassNameNotFound('test')->getMessage()
        );
    }
}
