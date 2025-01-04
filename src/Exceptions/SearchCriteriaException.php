<?php

namespace Spineda\DddFoundation\Exceptions;

/**
 * Search criteria exception
 *
 * @package Spineda\DddFoundation
 */
class SearchCriteriaException extends GenericException
{
    /**
     * @var int
     */
    protected $code = 406;

    /**
     * @var string
     */
    protected $message = "Los criterios de búsqueda son insuficientes";
}
