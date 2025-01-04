<?php

namespace Spineda\DddFoundation\Services\System;

use Spineda\DddFoundation\Contracts\IsService;
use Spineda\DddFoundation\Contracts\Repositories\System\MainConfigurationRepository;
use Spineda\DddFoundation\Entities\Database\Eloquent\Connection;
use Spineda\DddFoundation\Exceptions\System\MainConfiguration\MainConfigurationRepositoryMissing;

/**
 * Global service for the main configuration.
 * It works using static methods for global access through the application
 *
 * @package Spineda\DddFoundation
 */
abstract class MainConfigurationService implements IsService
{
    /**
     * @var ?MainConfigurationRepository
     */
    protected static ?MainConfigurationRepository $repository = null;

    /**
     * Sets the repository
     *
     * @param  ?MainConfigurationRepository  $repository
     *
     * @return void
     */
    public static function setRepository(?MainConfigurationRepository $repository): void
    {
        static::$repository = $repository;
    }

    /**
     * Gets the repository
     *
     * @return MainConfigurationRepository
     * @throws MainConfigurationRepositoryMissing
     */
    public static function getRepository(): MainConfigurationRepository
    {
        if (null === static::$repository) {
            throw new MainConfigurationRepositoryMissing();
        }

        return static::$repository;
    }

    /**
     * Connection Entity getter by connection name identifier
     *
     * @param  string    $connectionName    Connection name identifier
     *
     * @return Connection
     */
    public static function getConnection(string $connectionName): Connection
    {
        return static::$repository->getConnection($connectionName);
    }

    /**
     * Temp folder getter
     *
     * @return string
     * @throws MainConfigurationRepositoryMissing
     */
    public static function getTmpFolder(): string
    {
        if (null === static::$repository) {
            throw new MainConfigurationRepositoryMissing();
        }

        return static::$repository->getTmpFolder();
    }
}
