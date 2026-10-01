<?php

namespace Spineda\DddFoundation\Tests\Unit\Aggregates;

use Mockery\MockInterface;
use Spineda\DddFoundation\Aggregates\CollectionAggregate;
use Spineda\DddFoundation\Aggregates\ListingAggregateCollection;
use Spineda\DddFoundation\Aggregates\PaginatedCollectionAggregate;
use Spineda\DddFoundation\Contracts\IsAggregate;
use Spineda\DddFoundation\Exceptions\SearchCriteriaException;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Collections\AggregateCollection;
use Spineda\DddFoundation\ValueObjects\Collections\EntityCollection;
use Spineda\DddFoundation\ValueObjects\Collections\PaginatedValueObjectCollection;
use Spineda\DddFoundation\ValueObjects\Collections\ValueObjectCollection;

/**
 * Tests for the generic aggregates that wrap a collection
 *
 * @package Spineda\DddFoundation\Tests
 */
class CollectionAggregatesTest extends AbstractUnitTest
{
    /**
     * Tests that a collection aggregate wraps an entity collection.
     */
    public function testCollectionAggregateWithEntityCollection(): void
    {
        // Performs the test.
        $collection = new EntityCollection();
        $aggregate = new CollectionAggregate($collection);

        // Performs assertions.
        static::assertInstanceOf(IsAggregate::class, $aggregate);
        static::assertSame(
            $collection,
            $aggregate->getCollection(),
            'La colección retornada no coincide.'
        );
        static::assertSame(
            ['collection' => []],
            $aggregate->jsonSerialize(),
            'La serialización del agregado no coincide.'
        );
    }

    /**
     * Tests that a collection aggregate wraps a value object collection.
     */
    public function testCollectionAggregateWithValueObjectCollection(): void
    {
        // Performs the test.
        $collection = new ValueObjectCollection();
        $aggregate = new CollectionAggregate($collection);

        // Performs assertions.
        static::assertSame(
            $collection,
            $aggregate->getCollection(),
            'La colección retornada no coincide.'
        );
    }

    /**
     * Tests that a paginated collection aggregate wraps the pagination and creates empty filters.
     *
     * @throws  SearchCriteriaException
     */
    public function testPaginatedCollectionAggregate(): void
    {
        // Mocks the paginated collection.
        /** @var PaginatedValueObjectCollection|MockInterface $paginated */
        $paginated = $this->mock(PaginatedValueObjectCollection::class);
        $paginated->shouldReceive('jsonSerialize')
            ->andReturn(['totalRecords' => 4]);

        // Performs the test.
        $aggregate = new PaginatedCollectionAggregate($paginated);
        $serialized = $aggregate->jsonSerialize();

        // Performs assertions.
        static::assertInstanceOf(IsAggregate::class, $aggregate);
        static::assertSame(
            $paginated,
            $aggregate->getPaginatedCollection(),
            'La colección paginada retornada no coincide.'
        );
        static::assertSame(
            ['totalRecords' => 4],
            $serialized['collection'],
            'La paginación serializada no coincide.'
        );
        static::assertArrayHasKey('filters', $serialized);
    }

    /**
     * Tests that a listing aggregate collection wraps its records.
     */
    public function testListingAggregateCollection(): void
    {
        // Performs the test.
        $records = new AggregateCollection();
        $aggregate = new ListingAggregateCollection($records);

        // Performs assertions.
        static::assertInstanceOf(IsAggregate::class, $aggregate);
        static::assertSame(
            $records,
            $aggregate->getRecords(),
            'Los registros retornados no coinciden.'
        );
        static::assertSame(
            ['collection' => []],
            $aggregate->jsonSerialize(),
            'La serialización del agregado no coincide.'
        );
    }
}
