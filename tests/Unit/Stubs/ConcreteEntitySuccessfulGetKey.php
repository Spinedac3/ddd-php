<?php

namespace Spineda\DddFoundation\Tests\Unit\Stubs;

use Spineda\DddFoundation\Entities\AbstractEntity;

/**
 * Stub entity to test an Abstract Entity class
 *
 * @package Spineda\DddFoundation\Tests
 */
class ConcreteEntitySuccessfulGetKey extends AbstractEntity
{
    /**
     * @var array
     */
    protected array $keyFields = [ 'field1', 'field2' ];

    /**
     * @var mixed
     */
    protected mixed $field1;

    /**
     * @var mixed
     */
    protected mixed $field2;
}
