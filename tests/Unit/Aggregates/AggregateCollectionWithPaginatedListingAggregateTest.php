<?php

namespace Spineda\DddFoundation\Tests\Unit\Aggregates;

use Mockery\MockInterface;
use Spineda\DddFoundation\Aggregates\AggregateCollectionWithPaginatedListingAggregate;
use Spineda\DddFoundation\Contracts\IsAggregate;
use Spineda\DddFoundation\Exceptions\SearchCriteriaException;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Collections\AggregateCollection;
use Spineda\DddFoundation\ValueObjects\Collections\PaginatedEntityCollection;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;

/**
 * Tests for the listing aggregate of an aggregate collection with its pagination
 *
 * @package Spineda\DddFoundation\Tests
 */
class AggregateCollectionWithPaginatedListingAggregateTest extends AbstractUnitTest
{
    /**
     * Tests that the aggregate loads with the collection, the pagination and the filters.
     *
     * @throws  SearchCriteriaException
     */
    public function testLoadsCorrectly(): void
    {
        // Mocks the paginated collection.
        $aggregateCollection = new AggregateCollection();
        /** @var PaginatedEntityCollection|MockInterface $paginated */
        $paginated = $this->mock(PaginatedEntityCollection::class);
        $filters = new FiltersInfo(['status' => 'open'], 'express');

        // Performs the test.
        $aggregate = new AggregateCollectionWithPaginatedListingAggregate($aggregateCollection, $paginated, $filters);

        // Performs assertions.
        static::assertInstanceOf(IsAggregate::class, $aggregate);
        static::assertSame(
            $aggregateCollection,
            $aggregate->getAggregateCollection(),
            'La colección de agregados retornada no coincide.'
        );
        static::assertSame(
            $paginated,
            $aggregate->getPaginated(),
            'La colección paginada retornada no coincide.'
        );
        static::assertSame(
            $filters,
            $aggregate->getFilters(),
            'Los filtros retornados no coinciden.'
        );
    }

    /**
     * Tests that the aggregate creates empty filters when none are supplied.
     *
     * @throws  SearchCriteriaException
     */
    public function testLoadsWithoutPaginationNorFilters(): void
    {
        // Performs the test.
        $aggregate = new AggregateCollectionWithPaginatedListingAggregate(new AggregateCollection());

        // Performs assertions.
        static::assertNull(
            $aggregate->getPaginated(),
            'La colección paginada debió haber sido null.'
        );
        static::assertInstanceOf(
            FiltersInfo::class,
            $aggregate->getFilters(),
            'Los filtros debieron haberse creado vacíos.'
        );
    }

    /**
     * Tests the JSON serialization of the aggregate.
     *
     * @throws  SearchCriteriaException
     */
    public function testJSONSerialization(): void
    {
        // Mocks the paginated collection.
        /** @var PaginatedEntityCollection|MockInterface $paginated */
        $paginated = $this->mock(PaginatedEntityCollection::class);
        $paginated->shouldReceive('jsonSerialize')
            ->andReturn(['totalRecords' => 7]);

        // Performs the test.
        $aggregate = new AggregateCollectionWithPaginatedListingAggregate(new AggregateCollection(), $paginated);
        $serialized = $aggregate->jsonSerialize();

        // Performs assertions.
        static::assertSame(
            [],
            $serialized['collection'],
            'La colección serializada debió haber estado vacía.'
        );
        static::assertSame(
            ['totalRecords' => 7],
            $serialized['paginated'],
            'La paginación serializada no coincide.'
        );
        static::assertArrayHasKey('filters', $serialized);
    }
}
