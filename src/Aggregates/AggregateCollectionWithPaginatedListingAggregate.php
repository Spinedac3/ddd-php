<?php

namespace Spineda\DddFoundation\Aggregates;

use Spineda\DddFoundation\Contracts\IsAggregate;
use Spineda\DddFoundation\Exceptions\SearchCriteriaException;
use Spineda\DddFoundation\ValueObjects\Collections\AggregateCollection;
use Spineda\DddFoundation\ValueObjects\Collections\PaginatedEntityCollection;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;
use JsonSerializable;

/**
 * Aggregate with the needed data for listing an aggregate collection with its pagination
 *
 * @package Spineda\DddFoundation
 */
class AggregateCollectionWithPaginatedListingAggregate implements IsAggregate
{
    /**
     * @var  AggregateCollection
     */
    protected AggregateCollection $aggregateCollection;

    /**
     * @var  PaginatedEntityCollection|null
     */
    protected ?PaginatedEntityCollection $paginatedEntityCollection;

    /**
     * @var  FiltersInfo
     */
    protected FiltersInfo $filters;

    /**
     * Populates the aggregate with the data
     *
     * @param   AggregateCollection             $aggregateCollection        Collection of the composed rows
     * @param   PaginatedEntityCollection|null  $paginatedEntityCollection  Pagination the rows were composed from
     * @param   FiltersInfo|null                $filters                    Filters used for the listing
     *
     * @throws  SearchCriteriaException
     */
    public function __construct(
        AggregateCollection $aggregateCollection,
        ?PaginatedEntityCollection $paginatedEntityCollection = null,
        ?FiltersInfo $filters = null
    ) {
        $this->aggregateCollection = $aggregateCollection;
        $this->paginatedEntityCollection = $paginatedEntityCollection;
        $this->filters = $filters ?? new FiltersInfo([], '');
    }

    /**
     * Aggregate collection getter
     *
     * @return  AggregateCollection
     */
    public function getAggregateCollection(): AggregateCollection
    {
        return $this->aggregateCollection;
    }

    /**
     * Paginated collection getter
     *
     * @return  PaginatedEntityCollection|null
     */
    public function getPaginated(): ?PaginatedEntityCollection
    {
        return $this->paginatedEntityCollection;
    }

    /**
     * Filters getter
     *
     * @return  FiltersInfo
     */
    public function getFilters(): FiltersInfo
    {
        return $this->filters;
    }

    /**
     * {@inheritDoc}
     * @see  JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return [
            'collection' => $this->aggregateCollection->jsonSerialize(),
            'paginated'  => $this->paginatedEntityCollection ? $this->paginatedEntityCollection->jsonSerialize() : [],
            'filters'    => $this->filters->jsonSerialize(),
        ];
    }
}
