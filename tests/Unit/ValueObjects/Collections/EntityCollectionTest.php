<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Collections;

use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spineda\DddFoundation\Tests\Unit\Stubs\ConcreteEntitySuccessfulGetKey;
use Spineda\DddFoundation\ValueObjects\Collections\EntityCollection;
use Spineda\DddFoundation\Entities\AbstractEntity;
use ReflectionClass;
use ReflectionException;
use OverflowException;
use stdClass;
use TypeError;

/**
 * Tests for EntityCollection class
 *
 * @package Spineda\DddFoundation\Tests
 */
class EntityCollectionTest extends AbstractUnitTest
{
    /**
     * @var EntityCollection
     */
    protected EntityCollection $collection;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new EntityCollection();
    }

    /**
     * Creates a stub entity out of an abstract entity
     *
     * @param   array   $requiredFields
     * @param   array   $keyFields
     * @param   array   $fieldValues
     * @param   string  $keyValue
     *
     * @return  AbstractEntity
     * @throws  ReflectionException
     */
    protected function createStubEntity(
        array $requiredFields,
        array $keyFields,
        array $fieldValues,
        string $keyValue
    ): AbstractEntity {
        $reflection = new ReflectionClass(AbstractEntity::class);

        // Entity object for JSON serialization
        $entityObject = new stdClass();
        foreach ($fieldValues as $fieldKey => $fieldValue) {
            $entityObject->$fieldKey = $fieldValue;
        }

        /** @var AbstractEntity|MockObject $entity */
        $entity = $this->mockWithoutConstructor(AbstractEntity::class);
        $entity->method('getKey')
            ->willReturn($keyValue);
        $entity->method('jsonSerialize')
            ->willReturn($entityObject);
        $this->setUpEntityProperty($entity, $reflection, 'required', $requiredFields);
        $this->setUpEntityProperty($entity, $reflection, 'keyFields', $keyFields);
        $entity->__construct($fieldValues);
        return $entity;
    }

    /**
     * Tests adding the same element to the collection twice
     *
     * @throws  ReflectionException
     */
    public function testAddOverflowException(): void
    {
        static::expectException(OverflowException::class);
        $entity = $this->createStubEntity([ 'field1' ], [ 'field1' ], [ 'field1' => 1 ], '1');

        // Inserts the entity twice
        $this->collection
            ->add($entity)
            ->add($entity);
    }

    /**
     * Tests adding something that is not an entity to the collection
     */
    public function testAddTypeError(): void
    {
        static::expectException(TypeError::class);

        /** @var AbstractEntity $fakeEntity */
        $fakeEntity = new stdClass();
        $this->collection->add($fakeEntity);
    }

    /**
     * Test the scenario where the collection is empty and an entity is tried being extracted
     */
    public function testCurrentRewindNull(): void
    {
        static::assertNull($this->collection->current());
        static::assertNull($this->collection->key());
        static::assertFalse($this->collection->valid());
    }

    /**
     * Succeeds browsing through a collection: current / next / rewind / count / key / valid
     * It also tests adding more than one element to the collection
     *
     * @throws ReflectionException
     */
    public function testBrowseSuccessful(): void
    {
        $entity1 = $this->createStubEntity([ 'field1' ], [ 'field1' ], [ 'field1' => 1 ], '1');
        $entity2 = $this->createStubEntity([ 'field1' ], [ 'field1' ], [ 'field1' => 2 ], '2');

        $this->collection->add($entity1)
            ->add($entity2);

        static::assertEquals(2, $this->collection->count());
        static::assertTrue($this->collection->valid());
        static::assertEquals($entity1, $this->collection->current());
        static::assertEquals('1', $this->collection->key());
        static::assertEquals($entity2, $this->collection->next());
        static::assertEquals($entity1, $this->collection->rewind());
        static::assertInstanceOf(AbstractEntity::class, $this->collection->findByKey('1'));
        static::assertNull($this->collection->findByKey('5'));
    }

    /**
     * Tests a JSON serialization
     *
     * @throws ReflectionException
     */
    public function testSuccessfulJSONSerialization(): void
    {
        $entity1 = $this->createStubEntity([ 'field1' ], [ 'field1' ], [ 'field1' => 1 ], '1');
        $entity2 = $this->createStubEntity([ 'field1' ], [ 'field1' ], [ 'field1' => 2 ], '2');

        $entityObject1 = new stdClass();
        $entityObject1->field1 = 1;
        $entityObject2 = new stdClass();
        $entityObject2->field1 = 2;

        $this->collection->add($entity1)
            ->add($entity2);

        static::assertEquals([$entityObject1, $entityObject2], $this->collection->jsonSerialize());
    }

    /**
     * Tests the sum of a field in the entities
     */
    public function testSumFieldEntity(): void
    {
        $entity1 = new ConcreteEntitySuccessfulGetKey([
            'field1' => 1,
            'field2' => 3
        ]);

        $entity2 = new ConcreteEntitySuccessfulGetKey([
            'field1' => 2,
            'field2' => 3
        ]);

        $this->collection->add($entity1)
            ->add($entity2);

        static::assertEquals(6, $this->collection->sumFieldEntity('field2'));
    }
}
