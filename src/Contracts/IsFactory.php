<?php

namespace Spineda\DddFoundation\Contracts;

/**
 * Contract for factories
 *
 * @package Spineda\DddFoundation
 */
interface IsFactory
{
    /**
     * @return mixed
     */
    public static function get(): mixed;

    /**
     * @return mixed
     */
    public static function create(): mixed;
}
