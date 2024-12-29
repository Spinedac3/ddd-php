<?php

namespace Spineda\DddFoundation\Persistencies\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Instance of Eloquent Builder to handle multi primary key entities
 *
 * @package Spineda\DddFoundation
 */
class MultiKeyBuilder extends Builder
{
    /**
     * @var AbstractModelMultiKey
     */
    protected $model;

    /**
     * Find a model by its primary key (multiple keys => array is required)
     *
     * @param  array  $multiPK  Array of values representing the primary key to find
     * @param  array  $columns
     *
     * @return AbstractModelMultiKey|static|null
     * @see    Builder::find()
     */
    public function findMK(array $multiPK, array $columns = array('*')): AbstractModelMultiKey | static | null
    {
        if (empty($multiPK)) {
            return null;
        }

        $modelPK = $this->model->getMK();

        foreach ($modelPK as $arrayKey => $primaryKey) {
            $this->query->where($primaryKey, '=', $multiPK[$arrayKey]);
        }

        return $this->first($columns);
    }

    /**
     * Construct a new query builder for the model.
     *
     * @param QueryBuilder  $query  Query builder
     *
     * @return MultiKeyBuilder
     */
    public function newEloquentBuilder(QueryBuilder $query): MultiKeyBuilder
    {
        return new MultiKeyBuilder($query);
    }
}
