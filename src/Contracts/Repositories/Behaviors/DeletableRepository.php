<?php

namespace Spineda\DddFoundation\Contracts\Repositories\Behaviors;

/**
 * Repository that admits record deletion
 *
 * @package Spineda\DddFoundation
 */
interface DeletableRepository
{
    /**
     * Deletes a record using its key
     *
     * @param   mixed  $primaryKey  Key of the record to be deleted
     *
     * @return  bool
     */
    public function delete(mixed $primaryKey): bool;
}
