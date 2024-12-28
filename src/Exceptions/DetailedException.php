<?php

namespace Spineda\DddFoundation\Exceptions;

use Exception;

/**
 * Detailed exception class (requires a specific error string)
 *
 * @package Spineda\DddFoundation
 */
class DetailedException extends GenericException
{
    /**
     * @var  string
     */
    protected $message = 'Error de library: %s';

    /**
     * Construct a detailed exception with nested exception
     *
     * @param   string|null     $message   Exception message
     * @param   int|null        $code      Exception code
     * @param   Exception|null  $previous  Previous exception - for nesting
     */
    public function __construct(?string $message = null, ?int $code = null, ?Exception $previous = null)
    {

        $message = sprintf($this->message, $message);

        parent::__construct($message, $code, $previous);
    }
}
