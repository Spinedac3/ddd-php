<?php

namespace Spineda\DddFoundation\Exceptions\Behaviors\Crud;

use Spineda\DddFoundation\Exceptions\ConflictException;

/**
 * Exception to be raised when a record cannot be deleted in the CRUD repository
 *
 * @package Spineda\DddFoundation
 */
class RecordNotDeletedException extends ConflictException
{
    /**
     * @var  string
     */
    protected $message = 'El registro no pudo ser eliminado: %s';
}
