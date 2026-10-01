<?php

namespace Spineda\DddFoundation\Exceptions\Behaviors\Crud;

use Spineda\DddFoundation\Exceptions\ConflictException;

/**
 * Exception to be raised when a record cannot be updated in the CRUD repository
 *
 * @package Spineda\DddFoundation
 */
class RecordNotUpdatedException extends ConflictException
{
    /**
     * @var  string
     */
    protected $message = 'El registro no pudo ser actualizado: %s';
}
