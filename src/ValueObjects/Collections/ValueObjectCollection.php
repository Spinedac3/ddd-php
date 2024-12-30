<?php

namespace Spineda\DddFoundation\ValueObjects\Collections;

use Countable;
use Iterator;
use JsonSerializable;
use Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsValueObject;
use UnexpectedValueException;

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
        // Validates it's a value object trying to be added
        if (!is_a($valueObject, IsValueObject::class)) {
            throw new UnexpectedValueException('El objeto que se intenta agregar no es un objeto de valor.');
        }

        // Adds a value object to the collection.
        $this->collection[] = $valueObject;

        // Returns this instance.
        return $this;
    }
}
