<?php

namespace Spineda\DddFoundation\Tests\Unit\Stubs;

use Spineda\DddFoundation\Entities\AbstractEntity;

/**
 * Stub entity to test an Abstract Entity class
 *
 * @package Spineda\DddFoundation\Tests
 */
class ConcreteEntityWithoutKeys extends AbstractEntity
{
    /**
     * @var mixed
     */
    protected mixed $field1;

    /**
     * @var mixed
     */
    protected mixed $field2;
}
