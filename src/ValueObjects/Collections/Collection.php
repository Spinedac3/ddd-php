<?php

namespace Spineda\DddFoundation\ValueObjects\Collections;

use Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsCollectable;
use Spineda\DddFoundation\Entities\AbstractEntity;
use JsonSerializable;
use ReturnTypeWillChange;
use Countable;
use Iterator;

/**
 * Generic collection. It requires a IsCollectable / JSONSerializable class to be collected
 *
 * @package Spineda\DddFoundation
 */
class Collection implements Countable, Iterator, JsonSerializable
{
    /**
     * Collection of elements
     * @var array
     */
    protected array $collection = [];

    /**
     * Returns the current element
     *
     * @return IsCollectable|null
     */
    public function current(): ?IsCollectable
    {
        /**
         * If retrieving the current element fails, we'll still try to rewind the
         * pointer and return the first element.
         */
        if (($current = current($this->collection)) === false) {
            return $this->rewind();
        }

        return $current;
    }

    /**
     * Returns element by key
     *
     * @param mixed $key  The key of the element
     * @return IsCollectable|null
     */
    public function findByKey(mixed $key): ?IsCollectable
    {
        return $this->collection[$key] ?? null;
    }

    /**
     * @param mixed $key
     * @return void
     */
    public function removeByKey(mixed $key): void
    {
        unset($this->collection[$key]);
    }

    /**
     * Returns the last element
     *
     * @return IsCollectable|null
     */
    public function end(): ?IsCollectable
    {
        /**
         * If retrieving the last element fails, we'll still try to rewind the
         * pointer and return the last element.
         */
        if (($last = end($this->collection)) === false) {
            return $this->rewind();
        }

        return $last;
    }

    /**
     * Move forward to next element
     *
     * @return IsCollectable|null
     */
    #[ReturnTypeWillChange]
    public function next(): ?IsCollectable
    {
        $next = next($this->collection);

        return ($next === false ? null : $next);
    }

    /**
     * Return the key of the current element
     *
     * @return string|null
     */
    public function key(): ?string
    {
        // First, we'll collect the key.
        $key = key($this->collection);

        // No key
        if ($key === null) {
            return null;
        }

        // Then, we'll uniform the returned value.
        return ((string) $key);
    }

    /**
     * Checks if current position of the collection is valid
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     */
    public function valid(): bool
    {
        $key = $this->key();
        return ($key !== null);
    }

    /**
     * Rewinds to the first element
     *
     * @return IsCollectable|null
     */
    #[ReturnTypeWillChange]
    public function rewind(): ?IsCollectable
    {
        // Rewinds the cursor and returns the first Element.
        if (($element = reset($this->collection)) !== false) {
            return $element;
        }

        return null;
    }

    /**
     * Counts the number of entities in this collection
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }

    /**
     * Return field of the collection
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getField(string $field): mixed
    {
        return $this->{$field} ?? null;
    }

    /**
     * Sums the value by field in this collection
     *
     * @param string $field
     *
     * @return float
     */
    public function sumFieldEntity(string $field): float
    {
        // if the collection is empty, return 0.0
        if (empty($this->collection)) {
            return 0.0;
        }

        // Reduce the collection to a single value
        return array_reduce(
            $this->collection,
            function (float $carry, AbstractEntity $element) use ($field): float {
                // Adds the value of the field to the carry
                $value = $element->getField($field);
                if (is_numeric($value) && $value >= 0) {
                    $carry += (float) $value;
                }
                return $carry;
            },
            0.0
        );
    }

    /**
     * Sums the value by field in Value Object Collection
     *
     * @param string $field
     *
     * @return float
     */
    public function sumValueObject(string $field): float
    {
        // Declares sum variable and serialize collection
        $sum = 0;
        $serializedCollection =  $this->jsonSerialize();

        // Sum value element by field
        foreach ($serializedCollection as $element) {
            if (!isset($element[$field])) {
                return 0;
            }

            if ($element[$field] < 0 || !is_numeric($element[$field])) {
                continue;
            }

            $sum += floatVal($element[$field]);
        }

        return $sum;
    }

    /**
     * Returns a sub array with fields values
     *
     * @param string $field
     *
     * @return ?array
     */
    public function getSubArrayByField(string $field): ?array
    {
        $subArray = [];

        $serializedCollection =  $this->jsonSerialize();

        foreach ($serializedCollection as $element) {
            if (!isset($element->$field)) {
                return null;
            }

            $subArray[] = $element->$field;
        }

        return $subArray;
    }

    /**
     * Returns a sub array with fields values
     *
     * @param string $field
     *
     * @return ?array
     */
    public function getSubArrayByFieldWithIdIndex(string $field): ?array
    {
        $subArray = [];

        $serializedCollection =  $this->jsonSerialize();

        foreach ($serializedCollection as $element) {
            if (!isset($element->$field)) {
                return null;
            }

            $subArray[$element->id] = $element->$field;
        }

        return $subArray;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     * @see    JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        // Declares and fills a return array.
        $array = [];

        /** @var IsCollectable $entity */
        foreach ($this->collection as $entity) {
            $array[] = $entity->jsonSerialize();
        }

        return $array;
    }
}
