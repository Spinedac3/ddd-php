<?php

namespace Spineda\DddFoundation\ValueObjects\Collections;

use Spineda\DddFoundation\Entities\AbstractEntity;
use JsonSerializable;
use OverflowException;
use Countable;
use Iterator;

/**
 * Collection of entities
 *
 * @package Spineda\DddFoundation
 */
class EntityCollection extends Collection implements Countable, Iterator, JsonSerializable
{
    /**
     * Adds a new Entity to the Collection.
     * This method supports chaining.
     *
     * @param AbstractEntity $entity Entity to add to the collection.
     *
     * @return self
     * @throws OverflowException If you're trying to add a repeated Entity to the Collection.
     */
    public function add(AbstractEntity $entity): self
    {
        // Validates if the given Entity already exists in the Collection.
        if (array_key_exists($entity->getKey(), $this->collection)) {
            throw new OverflowException('La entidad que se intenta agregar ya existe en la colección.');
        }

        // Adds an Entity to the collection.
        $this->collection[$entity->getKey()] = $entity;

        // Returns this instance.
        return $this;
    }

    /**
     * Add a new Entity to the Collection from an external Collection skipping repeated
     *
     * @param AbstractEntity $entity Entity to add to the collection.
     *
     * @return  self
     */
    public function strictMerge(AbstractEntity $entity): self
    {
        // Validates if the given Entity already exists in the Collection.
        if (array_key_exists($entity->getKey(), $this->collection)) {
            return $this;
        }

        // Adds an Entity to the collection.
        $this->collection[$entity->getKey()] = $entity;

        // Returns this instance.
        return $this;
    }

    /**
     * Merge one or more entity collection using strict merge
     *
     * @param EntityCollection  $newEntityCollection
     * @return self
     */
    public function strictMergeEntityCollection(EntityCollection $newEntityCollection): self
    {
        /** @var AbstractEntity $entity */
        foreach ($newEntityCollection as $entity) {
            // Validates if the given Entity already exists in the Collection.
            if (array_key_exists($entity->getKey(), $this->collection)) {
                return $this;
            }

            // Adds an Entity to the collection.
            $this->collection[$entity->getKey()] = $entity;
        }

        // Returns this instance.
        return $this;
    }
}
