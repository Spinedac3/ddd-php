<?php

namespace Spineda\DddFoundation\Factories\Repositories\Shipping;

use Spineda\DddFoundation\Contracts\Repositories\Shipping\DriverRepository;
use Spineda\DddFoundation\Factories\AbstractFactory;
use Spineda\DddFoundation\Persistencies\Eloquent\Shipping\Driver;
use Spineda\DddFoundation\Repositories\Database\ORM\Shipping\ORMDriverRepository as Repository;

/**
 * Factory of Driver repositories
 *
 * @package Spineda\DddFoundation
 */
class DriverFactory extends AbstractFactory
{
    /**
     * @var  DriverRepository|null
     */
    protected static ?DriverRepository $driverRepository = null;

    /**
     * Gets a singleton repository
     *
     * @return  DriverRepository
     */
    public static function get(): DriverRepository
    {
        if (null === static::$driverRepository) {
            static::$driverRepository = static::create();
        }

        return static::$driverRepository;
    }

    /**
     * Creates a new repository
     *
     * @return  DriverRepository
     */
    public static function create(): DriverRepository
    {
        return new Repository(new Driver());
    }
}
