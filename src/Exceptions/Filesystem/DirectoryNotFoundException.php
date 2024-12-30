<?php

namespace Spineda\DddFoundation\Exceptions\Filesystem;

use Spineda\DddFoundation\Exceptions\NotFoundException;
use Exception;

/**
 * Directory Not Found Exception Class
 *
 * @package Spineda\DddFoundation
 */
class DirectoryNotFoundException extends NotFoundException
{
    /**
     * @var string
     */
    protected $message = 'El directorio %s no pudo ser encontrado';

    /**
     *
     * DirectoryNotFoundException constructor.
     *
     * @param   string     $directory
     * @param   int|null   $code
     * @param   Exception  $previous
     */
    public function __construct($directory, $code = null, $previous = null)
    {
        parent::__construct(sprintf($this->message, $directory), $code ?? $this->code, $previous);
    }
}
