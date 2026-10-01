<?php

namespace Spineda\DddFoundation\Contracts\Repositories\Behaviors;

use Spineda\DddFoundation\Entities\AbstractEntity;

/**
 * Repository that admits record updates
 *
 * @package Spineda\DddFoundation
 */
interface UpdatableRepository
{
    /**
     * Updates a record for a given entity
     *
     * @param   mixed           $primaryKey  Key of the record to be updated
     * @param   AbstractEntity  $entity      Entity with the new data
     *
     * @return  void
     */
    public function update(mixed $primaryKey, AbstractEntity $entity): void;
}
