<?php

namespace Spineda\DddFoundation\Exceptions\Shipping\Driver;

use Spineda\DddFoundation\Exceptions\NotFoundException;

/**
 * Exception to be raised when a driver is not found
 *
 * @package Spineda\DddFoundation
 */
class DriverNotFoundException extends NotFoundException
{
    /**
     * @var  string
     */
    protected $message = 'El conductor no existe en el sistema';
}
