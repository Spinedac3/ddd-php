<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Collections;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsValueObject;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Collections\ValueObjectCollection;
use stdClass;
use TypeError;

/**
 * Tests for ValueObjectCollection class
 *
 * @package Spineda\DddFoundation\Tests
 */
class ValueObjectCollectionTest extends AbstractUnitTest
{
    /**
     * @var ValueObjectCollection
     */
    protected ValueObjectCollection $collection;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new ValueObjectCollection();
    }

    /**
     * Creates a stub entity out of an abstract entity
     *
     * @param   array  $fieldValues  Array of fields to be set into the value object, individually by key/value
     *
     * @return  IsValueObject
     */
    protected function createStubValueObject(array $fieldValues): IsValueObject
    {
        // Entity object for JSON serialization
        $valueObjectObject = [];

        // Creates the field values in the stub JSON serializable object
        if (count($fieldValues)) {
            foreach ($fieldValues as $fieldKey => $fieldValue) {
                $valueObjectObject[$fieldKey] = $fieldValue;
            }
        }

        /** @var IsValueObject|MockObject $valueObject */
        $valueObject = $this->mockWithoutConstructor(IsValueObject::class);
        $valueObject->method('jsonSerialize')
            ->willReturn($valueObjectObject);

        // No array of values, return early return
        if (!count($fieldValues)) {
            return $valueObject;
        }

        // Sets up the properties of this stub
        foreach ($fieldValues as $fieldKey => $fieldValue) {
            $valueObject->{$fieldKey} = $fieldValue;
        }

        return $valueObject;
    }

    /**
     * Tests adding something that is not a value object to the collection
     */
    public function testAddTypeError(): void
    {
        $this->expectException(TypeError::class);

        /** @var IsValueObject $fakeValueObject */
        $fakeValueObject = new stdClass();
        $this->collection->add($fakeValueObject);
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
     */
    public function testBrowseSuccessful(): void
    {
        $valueObject1 = $this->createStubValueObject([ 'field1' => 'hola', 'field2' => 2]);
        $valueObject2 = $this->createStubValueObject([ 'field1' => 'hola', 'field2' => 2]);

        $this->collection->add($valueObject1)
            ->add($valueObject2);

        static::assertEquals(2, $this->collection->count());
        static::assertEquals(4, $this->collection->sumValueObject('field2'));
        static::assertEquals(0, $this->collection->sumValueObject('field4'));
        static::assertEquals(0, $this->collection->sumValueObject('field1'));
        static::assertTrue($this->collection->valid());
        static::assertEquals($valueObject1, $this->collection->current());
        static::assertEquals(0, $this->collection->key());
        static::assertEquals($valueObject2, $this->collection->next());
        static::assertEquals($valueObject1, $this->collection->rewind());
    }

    /**
     * Tests a JSON serialization
     */
    public function testSuccessfulJSONSerialization(): void
    {
        $valueObject1 = $this->createStubValueObject([ 'field1' => 1, 'field2' => 2]);
        $valueObject2 = $this->createStubValueObject([ 'field1' => 3, 'field2' => 4]);

        $valueObjectObject1 = [];
        $valueObjectObject1['field1'] = 1;
        $valueObjectObject1['field2'] = 2;
        $valueObjectObject2 = [];
        $valueObjectObject2['field1'] = 3;
        $valueObjectObject2['field2'] = 4;

        $this->collection->add($valueObject1)
            ->add($valueObject2);

        static::assertEquals([$valueObjectObject1, $valueObjectObject2], $this->collection->jsonSerialize());
    }
}