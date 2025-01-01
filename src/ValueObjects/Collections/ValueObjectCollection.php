<?php

namespace Spineda\DddFoundation\ValueObjects\Collections;

use Countable;
use Iterator;
use JsonSerializable;
use Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsValueObject;

/**
 * Collection of value objects
 *
 * @package Spineda\DddFoundation
 */
class ValueObjectCollection extends Collection implements Countable, Iterator, JsonSerializable
{
    /**
     * Adds a new value object to the Collection.
     * This method supports chaining.
     *
     * @param IsValueObject $valueObject Value object to add to the collection.
     *
     * @return  self
     */
    public function add(IsValueObject $valueObject): self
    {
        // Adds a value object to the collection.
        $this->collection[] = $valueObject;

        // Returns this instance.
        return $this;
    }
}
