<?php

namespace Spineda\DddFoundation\Persistencies\Eloquent;

use Spineda\DddFoundation\ValueObjects\Collections\Collection;

/**
 * Abstract model class for multi-key tables
 *
 * @package Spineda\DddFoundation
 */
abstract class AbstractModelMultiKey extends AbstractModel
{
    /**
     * Primary key is set in a different variable
     *
     * @var array
     */
    protected array $multiPK = array();

    /**
     * Get the multiple key array
     *
     * @return  array
     */
    public function getMK(): array
    {
        return $this->multiPK;
    }

    /**
     * Find a model by its primary key.
     *
     * @param  array  $multiPK  Array of values of the primary key, in the same order as the PK itself
     * @param  array  $columns
     *
     * @return AbstractModelMultiKey|Collection|static
     */
    public static function findMK(
        array $multiPK,
        array $columns = array('*')
    ): AbstractModelMultiKey | Collection | static {
        if (empty($multiPK)) {
            return new Collection();
        }

        $instance = new static();

        /** @var MultiKeyBuilder $query */
        $query = $instance->newModelQuery();

        return $query->findMK($multiPK, $columns);
    }

    /**
     * {@inheritDoc}
     * @see Model::newEloquentBuilder()
     *
     * @return MultiKeyBuilder|static
     */
    public function newEloquentBuilder($query): MultiKeyBuilder|static
    {
        return new MultiKeyBuilder($query);
    }
}
