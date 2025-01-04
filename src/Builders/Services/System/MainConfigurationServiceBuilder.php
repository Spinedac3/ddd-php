<?php

namespace Spineda\DddFoundation\Builders\Services\System;

use Spineda\DddFoundation\Builders\Repositories\Filesystem\File\FileMainConfigurationRepositoryBuilder;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\InvalidConfigurationFileException;
use Spineda\DddFoundation\Exceptions\System\MainConfiguration\MainConfigurationRepositoryMissing;
use Spineda\DddFoundation\Services\System\MainConfigurationService;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * MainConfigurationService builder class
 *
 * @package Spineda\DddFoundation
 */
abstract class MainConfigurationServiceBuilder
{
    /**
     * Builds the main configuration service and initializes the DB capsule manager
     *
     * @param string $file Full file path
     *
     * @return bool
     * @throws DirectoryNotFoundException
     * @throws FileNotFoundException
     * @throws InvalidConfigurationFileException
     * @throws MainConfigurationRepositoryMissing
     */
    public static function buildFromFile(string $file): bool
    {
        // Reads the configuration file
        $configuration = FileMainConfigurationRepositoryBuilder::build(new File($file));

        // Set Repository
        MainConfigurationService::setRepository($configuration);

        // Configuration TimeZone
        date_default_timezone_set(MainConfigurationService::getRepository()->getTimeZone());

        return true;
    }
}
