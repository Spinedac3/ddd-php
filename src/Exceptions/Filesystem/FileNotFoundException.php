<?php

namespace Spineda\DddFoundation\Exceptions\Filesystem;

use Spineda\DddFoundation\Exceptions\NotFoundException;
use Exception;

/**
 * Exception to be raised when a file is not found
 *
 * @package Spineda\DddFoundation
 */
class FileNotFoundException extends NotFoundException
{
    /**
     * @var string
     */
    protected $message = 'El archivo %s no pudo ser encontrado';

    /**
     *
     * FileNotFoundException constructor.
     *
     * @param   string     $file
     * @param   int|null   $code
     * @param   Exception  $previous
     */
    public function __construct($file, $code = null, $previous = null)
    {
        parent::__construct(sprintf($this->message, $file), $code ?? $this->code, $previous);
    }
}
