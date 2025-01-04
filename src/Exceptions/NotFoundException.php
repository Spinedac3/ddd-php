<?php

namespace Spineda\DddFoundation\Exceptions;

/**
 * Exception to be raised when something is not found (client/user error)
 *
 * @package Spineda\DddFoundation
 */
class NotFoundException extends GenericException
{
    /**
     * @var  string
     */
    protected $message = 'No existe el elemento que está buscando';

    /**
     * @var  int
     */
    protected $code = 404;
}
