<?php

namespace Spineda\DddFoundation\Contracts\Repositories;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Exceptions\NotFoundException;

/**
 * Abstract contract for repositories with five-key entities
 *
 * @package Spineda\DddFoundation
 */
interface AbstractRepositoryFiveKey extends AbstractRepository
{
    /**
     * Gets an entity from this repository using its key (variable number of arguments)
     *
     * @param   mixed  $key1  Key 1 argument
     * @param   mixed  $key2  Key 2 argument
     * @param   mixed  $key3  Key 3 argument
     * @param   mixed  $key4  Key 4 argument
     * @param   mixed  $key5  Key 5 argument
     *
     * @return  AbstractEntity
     * @throws  NotFoundException
     */
    public function get(mixed $key1, mixed $key2, mixed $key3, mixed $key4, mixed $key5): AbstractEntity;
}
