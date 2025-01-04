<?php

namespace Spineda\DddFoundation\Exceptions;

use Exception;

/**
 * Base class for exceptions
 *
 * @package Spineda\DddFoundation
 */
class GenericException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Error general';

    /**
     * @var int
     */
    protected $code = 500;

    /**
     * Exception constructor
     *
     * @param   string|null $message   Exception message
     * @param   int|null    $code      Exception code
     * @param   Exception|null $previous  Previous exception - for nesting
     */
    public function __construct(?string $message = null, ?int $code = null, ?Exception $previous = null)
    {
        if (null === $message) {
            $message = $this->message;
        }

        if (null === $code) {
            $code = $this->code;
        }

        parent::__construct($message, $code, $previous);
    }
}
