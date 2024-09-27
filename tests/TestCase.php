<?php

declare(strict_types=1);

namespace Ssmiff\LaravelEventSauce\Test;

use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Mockery::close();
    }

    public function passed(): void
    {
        $this->assertTrue(true);
    }
}
