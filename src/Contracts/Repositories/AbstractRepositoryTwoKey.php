<?php

namespace Spineda\DddFoundation\Contracts\Repositories;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Exceptions\NotFoundException;

/**
 * Abstract contract for repositories with two-key entities
 *
 * @package Spineda\DddFoundation
 */
interface AbstractRepositoryTwoKey extends AbstractRepository
{
    /**
     * Gets an entity from this repository using its key (variable number of arguments)
     *
     * @param   mixed  $key1  Key 1 argument
     * @param   mixed  $key2  Key 2 argument
     *
     * @return  AbstractEntity
     * @throws  NotFoundException
     */
    public function get(mixed $key1, mixed $key2): AbstractEntity;
}
