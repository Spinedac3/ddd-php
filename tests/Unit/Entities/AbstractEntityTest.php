<?php

namespace Spineda\DddFoundation\Tests\Unit\Entities;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\Tests\Unit\Stubs\ConcreteEntitySuccessfulGetKey;
use Spineda\DddFoundation\Tests\Unit\Stubs\ConcreteEntityUnderflowValidation;
use Spineda\DddFoundation\Tests\Unit\Stubs\ConcreteEntityWithoutNonEntityProperties;
use UnderflowException;
use stdClass;

/**
 * Tests for an abstract entity
 *
 * @package Spineda\DddFoundation\Tests
 */
class AbstractEntityTest extends AbstractUnitTest
{
    /**
     * @var  AbstractEntity
     */
    protected AbstractEntity $entity;

    /**
     * Tests a non-passing validation of an entity
     *
     * @return  void
     */
    public function testUnderflowValidation(): void
    {
        static::expectException(UnderflowException::class);
        $this->entity = new ConcreteEntityUnderflowValidation([
            'field1' => 1,
            'field3' => 3
        ]);
    }

    /**
     * Tests a successful getKey
     *
     * @return  void
     */
    public function testSuccessfulGetKey(): void
    {
        $this->entity = new ConcreteEntitySuccessfulGetKey([
            'field1' => 1,
            'field2' => 3
        ]);

        static::assertEquals('1_3', $this->entity->getKey());
    }

    /**
     * Tests JSON serialization
     */
    public function testSuccessfulJSONSerialization(): void
    {
        $this->entity = new ConcreteEntitySuccessfulGetKey([
            'field1' => 1,
            'field2' => 3
        ]);

        $entityObject = new stdClass();
        $entityObject->field1 = 1;
        $entityObject->field2 = 3;

        static::assertEquals($entityObject, $this->entity->jsonSerialize());
    }

    /**
     * Tests JSON serialization
     */
    public function testSuccessfulJSONSerializationWithoutNotEntityProperties(): void
    {
        $this->entity = new ConcreteEntityWithoutNonEntityProperties([
            'field1' => 1,
            'field2' => 3
        ]);

        $entityObject = new stdClass();
        $entityObject->field1 = 1;
        $entityObject->field2 = 3;
        $entityObject->key = 1;
        $entityObject->nonEntityProperties = [];
        $entityObject->keyFields = ['field1'];
        $entityObject->required = ['key'];

        static::assertEquals($entityObject, $this->entity->jsonSerialize());
    }
}
