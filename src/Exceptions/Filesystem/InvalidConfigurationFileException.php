<?php

namespace Spineda\DddFoundation\Exceptions\Filesystem;

use Spineda\DddFoundation\Exceptions\DetailedException;

/**
 * Exception to be raised when a configuration file is invalid
 *
 * @package Spineda\DddFoundation
 */
class InvalidConfigurationFileException extends DetailedException
{
    protected $message = 'El archivo de configuración no es válido: %s';
}
