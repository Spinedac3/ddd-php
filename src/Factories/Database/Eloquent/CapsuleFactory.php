<?php

namespace Spineda\DddFoundation\Factories\Database\Eloquent;

use Illuminate\Database\Capsule\Manager;

/**
 * Eloquent Capsule Factory
 *
 * @package Spineda\DddFoundation
 */
abstract class CapsuleFactory
{
    /**
     * @var ?Manager
     */
    private static ?Manager $capsule = null;

    public static function getCapsule(): Manager
    {
        // Create Manager capsule when is null
        if (null === static::$capsule) {
            static::createCapsule();
        }

        // Retrieve capsule
        return static::$capsule;
    }

    public static function createCapsule(): Manager
    {
        // Create Manager capsule
        static::$capsule = new Manager();
        static::$capsule->setAsGlobal();

        // Retrieve capsule
        return static::$capsule;
    }
}
