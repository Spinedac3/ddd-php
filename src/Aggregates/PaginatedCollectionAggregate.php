<?php

namespace Spineda\DddFoundation\Aggregates;

use Spineda\DddFoundation\Contracts\IsAggregate;
use Spineda\DddFoundation\Exceptions\SearchCriteriaException;
use Spineda\DddFoundation\ValueObjects\Collections\PaginatedEntityCollection;
use Spineda\DddFoundation\ValueObjects\Collections\PaginatedValueObjectCollection;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;
use JsonSerializable;

/**
 * Aggregate that wraps either a paginated entity collection or a paginated value object collection
 *
 * @package Spineda\DddFoundation
 */
class PaginatedCollectionAggregate implements IsAggregate
{
    /**
     * @var  PaginatedEntityCollection|PaginatedValueObjectCollection
     */
    protected PaginatedEntityCollection | PaginatedValueObjectCollection $paginated;

    /**
     * @var  FiltersInfo
     */
    protected FiltersInfo $filters;

    /**
     * Populates the aggregate with the data
     *
     * @param   PaginatedEntityCollection|PaginatedValueObjectCollection  $paginated  Pagination to be wrapped
     * @param   FiltersInfo|null                                          $filters    Filters used for the listing
     *
     * @throws  SearchCriteriaException
     */
    public function __construct(
        PaginatedEntityCollection | PaginatedValueObjectCollection $paginated,
        ?FiltersInfo $filters = null
    ) {
        $this->paginated = $paginated;
        $this->filters = $filters ?? new FiltersInfo([], '');
    }

    /**
     * Paginated collection getter
     *
     * @return  PaginatedEntityCollection|PaginatedValueObjectCollection
     */
    public function getPaginatedCollection(): PaginatedEntityCollection | PaginatedValueObjectCollection
    {
        return $this->paginated;
    }

    /**
     * {@inheritDoc}
     * @see  JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return [
            'collection' => $this->paginated->jsonSerialize(),
            'filters'    => $this->filters->jsonSerialize(),
        ];
    }
}
