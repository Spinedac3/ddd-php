<?php

namespace Spineda\DddFoundation\Exceptions;

/**
 * Conflict exception class
 *
 * @package Spineda\DddFoundation
 */
class ConflictException extends GenericException
{
    /**
     * @var int
     */
    protected $code = 409;

    /**
     * @var string
     */
    protected $message = 'La acción falló por un conflicto con la solicitud: %s';
}
