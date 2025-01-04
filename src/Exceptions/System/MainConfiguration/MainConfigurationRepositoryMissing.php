<?php

namespace Spineda\DddFoundation\Exceptions\System\MainConfiguration;

use Spineda\DddFoundation\Exceptions\GenericException;

/**
 * Exception to be raised when the main configuration repository has not been configured
 *
 * @package Spineda\DddFoundation
 */
class MainConfigurationRepositoryMissing extends GenericException
{
    protected $message = 'The main configuration is missing / not initialized';
}
