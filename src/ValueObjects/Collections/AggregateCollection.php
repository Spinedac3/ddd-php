<?php

namespace Spineda\DddFoundation\ValueObjects\Collections;

use Countable;
use Iterator;
use JsonSerializable;
use Spineda\DddFoundation\Contracts\IsAggregate;

/**
 * Collection of aggregate objects
 *
 * @package Spineda\DddFoundation
 */
class AggregateCollection extends Collection implements Countable, Iterator, JsonSerializable
{
    /**
     * Adds a new aggregate object to the Collection.
     * This method supports chaining.
     *
     * @param IsAggregate $aggregate Aggregate object to add to the collection.
     *
     * @return  self
     */
    public function add(IsAggregate $aggregate): self
    {
        // Adds an aggregate object to the collection.
        $this->collection[] = $aggregate;

        // Returns this instance.
        return $this;
    }
}
