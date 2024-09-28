<?php

declare(strict_types=1);

namespace Ssmiff\EventSauce\Laravel\Concerns;

trait IgnoresMissingMethods
{
    public function __call($name, $arguments)
    {
    }
}
