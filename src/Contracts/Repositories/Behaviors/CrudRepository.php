<?php

namespace Spineda\DddFoundation\Contracts\Repositories\Behaviors;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Persistencies\Eloquent\AbstractModel;

/**
 * Contract for the CRUD implementation used by the repositories that need it
 *
 * @package Spineda\DddFoundation
 */
interface CrudRepository
{
    /**
     * Stores a new entity in the system, using the associated model
     *
     * @param   AbstractEntity|array  $entity  Entity
     * @param   AbstractModel         $model   Model
     *
     * @return  int
     */
    public function create(AbstractEntity | array $entity, AbstractModel $model): int;

    /**
     * Updates an existing entity in the system
     *
     * @param   AbstractModel         $oldEntity  Existing record
     * @param   AbstractEntity|array  $newEntity  Entity with the new data
     *
     * @return  AbstractModel
     */
    public function update(AbstractModel $oldEntity, AbstractEntity | array $newEntity): AbstractModel;

    /**
     * Deletes an existing entity in the system
     *
     * @param   AbstractModel  $entity  Record to be deleted
     *
     * @return  bool
     */
    public function delete(AbstractModel $entity): bool;

    /**
     * Stores a new entity in the system when the entity has no incrementing key
     *
     * @param   AbstractEntity|array  $entity  Entity
     * @param   AbstractModel         $model   Model
     *
     * @return  bool
     */
    public function createNotIncrementing(AbstractEntity | array $entity, AbstractModel $model): bool;
}
