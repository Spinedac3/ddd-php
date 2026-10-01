<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Collections;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spineda\DddFoundation\Contracts\IsAggregate;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Collections\AggregateCollection;
use stdClass;
use TypeError;

/**
 * Tests for AggregateCollection class
 *
 * @package Spineda\DddFoundation\Tests
 */
class AggregateCollectionTest extends AbstractUnitTest
{
    /**
     * @var AggregateCollection
     */
    protected AggregateCollection $collection;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new AggregateCollection();
    }

    /**
     * Creates a stub aggregate with the given serialized data
     *
     * @param   array  $serialized  Data returned by the JSON serialization of the stub
     *
     * @return  IsAggregate
     */
    protected function createStubAggregate(array $serialized): IsAggregate
    {
        /** @var IsAggregate|MockObject $aggregate */
        $aggregate = $this->mockWithoutConstructor(IsAggregate::class);
        $aggregate->method('jsonSerialize')
            ->willReturn($serialized);

        return $aggregate;
    }

    /**
     * Tests adding something that is not an aggregate to the collection
     */
    public function testAddingNonAggregate(): void
    {
        // Creates expectation.
        static::expectException(TypeError::class);

        // Performs test.
        /** @noinspection PhpParamsInspection */
        $this->collection->add(new stdClass());
    }

    /**
     * Tests adding aggregates to the collection, successfully
     */
    public function testAddingSuccessfully(): void
    {
        // Performs the test.
        $returned = $this->collection
            ->add($this->createStubAggregate(['id' => 1]))
            ->add($this->createStubAggregate(['id' => 2]));

        // Performs assertions.
        static::assertSame(
            $this->collection,
            $returned,
            'El método add debió haber retornado la misma colección.'
        );
        static::assertCount(
            2,
            $this->collection,
            'La colección debió haber tenido 2 agregados.'
        );
    }

    /**
     * Tests the JSON serialization of the aggregates in the collection
     */
    public function testJSONSerialization(): void
    {
        // Performs the test.
        $this->collection
            ->add($this->createStubAggregate(['id' => 1]))
            ->add($this->createStubAggregate(['id' => 2]));

        // Performs assertions.
        static::assertEquals(
            '[{"id":1},{"id":2}]',
            json_encode($this->collection),
            'La serialización de la colección no coincide.'
        );
    }
}
