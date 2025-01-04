<?php

namespace Spineda\DddFoundation\Exceptions\Filesystem;

use Spineda\DddFoundation\Exceptions\GenericException;

/**
 * Exception to be raised when a certain date is not valid
 *
 * @package Spineda\DddFoundation
 */
class InvalidDateFormatException extends GenericException
{
    protected $message = 'Formato de fecha invalido';
}
