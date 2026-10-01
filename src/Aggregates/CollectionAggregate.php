<?php

namespace Spineda\DddFoundation\Aggregates;

use Spineda\DddFoundation\Contracts\IsAggregate;
use Spineda\DddFoundation\ValueObjects\Collections\EntityCollection;
use Spineda\DddFoundation\ValueObjects\Collections\ValueObjectCollection;
use JsonSerializable;

/**
 * Aggregate that wraps either an entity collection or a value object collection
 *
 * @package Spineda\DddFoundation
 */
class CollectionAggregate implements IsAggregate
{
    /**
     * @var  EntityCollection|ValueObjectCollection
     */
    protected EntityCollection | ValueObjectCollection $collection;

    /**
     * Populates the aggregate with the data
     *
     * @param   EntityCollection|ValueObjectCollection  $collection  Collection to be wrapped
     */
    public function __construct(EntityCollection | ValueObjectCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Collection getter
     *
     * @return  EntityCollection|ValueObjectCollection
     */
    public function getCollection(): EntityCollection | ValueObjectCollection
    {
        return $this->collection;
    }

    /**
     * {@inheritDoc}
     * @see  JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return [
            'collection' => $this->collection->jsonSerialize(),
        ];
    }
}
