<?php

namespace Spineda\DddFoundation\Contracts\Repositories\Behaviors;

use Spineda\DddFoundation\Entities\AbstractEntity;

/**
 * Repository that admits record creation
 *
 * @package Spineda\DddFoundation
 */
interface CreatableRepository
{
    /**
     * Record creation for a given entity
     *
     * @param   AbstractEntity  $entity  Entity to be created as a record in the repository
     *
     * @return  int
     */
    public function create(AbstractEntity $entity): int;
}
