<?php

namespace Spineda\DddFoundation\Aggregates;

use Spineda\DddFoundation\Contracts\IsAggregate;
use Spineda\DddFoundation\ValueObjects\Collections\AggregateCollection;
use JsonSerializable;

/**
 * Aggregate with the needed data for listing an aggregate collection
 *
 * @package Spineda\DddFoundation
 */
class ListingAggregateCollection implements IsAggregate
{
    /**
     * @var  AggregateCollection
     */
    protected AggregateCollection $records;

    /**
     * Populates the aggregate with the data
     *
     * @param   AggregateCollection  $records  Collection of the composed rows
     */
    public function __construct(AggregateCollection $records)
    {
        $this->records = $records;
    }

    /**
     * Records getter
     *
     * @return  AggregateCollection
     */
    public function getRecords(): AggregateCollection
    {
        return $this->records;
    }

    /**
     * {@inheritDoc}
     * @see  JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return [
            'collection' => $this->records->jsonSerialize(),
        ];
    }
}
