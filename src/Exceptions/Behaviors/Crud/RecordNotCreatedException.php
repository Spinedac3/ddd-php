<?php

namespace Spineda\DddFoundation\Exceptions\Behaviors\Crud;

use Spineda\DddFoundation\Exceptions\ConflictException;

/**
 * Exception to be raised when a record cannot be created in the CRUD repository
 *
 * @package Spineda\DddFoundation
 */
class RecordNotCreatedException extends ConflictException
{
    /**
     * @var  string
     */
    protected $message = 'El registro no pudo ser creado: %s';
}
