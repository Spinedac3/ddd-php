<?php

namespace Spineda\DddFoundation\Tests\Unit\Stubs;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Traits\Entities\ActivableEntity;
use Spineda\DddFoundation\Traits\Entities\AuditableEntity;
use Spineda\DddFoundation\Traits\Entities\CommonEntity;
use Spineda\DddFoundation\Traits\Entities\SoftDeletableEntity;

/**
 * Stub entity to test the traits for entities
 *
 * @package Spineda\DddFoundation\Tests
 */
class ConcreteEntityWithTraits extends AbstractEntity
{
    use CommonEntity;
    use AuditableEntity;
    use SoftDeletableEntity;
    use ActivableEntity;

    /**
     * @var array
     */
    protected array $required = ['id'];
}
