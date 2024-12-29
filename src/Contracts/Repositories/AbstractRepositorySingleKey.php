<?php

namespace Spineda\DddFoundation\Contracts\Repositories;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Exceptions\NotFoundException;

/**
 * Abstract contract for repositories of single-key entities
 *
 * @package Spineda\DddFoundation
 */
interface AbstractRepositorySingleKey extends AbstractRepository
{
    /**
     * Returns an entity from the repository, handled by its id
     *
     * @param   mixed  $key  Keu Entity to find
     *
     * @return  AbstractEntity
     * @throws  NotFoundException
     */
    public function get(mixed $key): AbstractEntity;
}
