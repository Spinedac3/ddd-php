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
     * @return IsFactory
     */
    public static function get(): IsFactory;

    /**
     * @return IsFactory
     */
    public static function create(): IsFactory;
}
