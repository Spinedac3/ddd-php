<?php

namespace Spineda\DddFoundation\Builders\Repositories\Filesystem\File;

use Spineda\DddFoundation\Contracts\Repositories\System\MainConfigurationRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\InvalidConfigurationFileException;
use Spineda\DddFoundation\Exceptions\System\MainConfiguration\MainConfigurationRepositoryMissing;
use Spineda\DddFoundation\Repositories\Filesystem\File\FileMainConfigurationRepository;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Builder of the main configuration file repository
 *
 * @package Spineda\DddFoundation
 */
abstract class FileMainConfigurationRepositoryBuilder
{
    /**
     * @var  ?MainConfigurationRepository
     */
    private static ?MainConfigurationRepository $repository = null;

    /**
     * Builder of main configuration using a YAML file
     *
     * @param   File  $file     File configuration

     * @return  MainConfigurationRepository
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public static function build(File $file): MainConfigurationRepository
    {
        static::$repository = new FileMainConfigurationRepository($file);

        return static::$repository;
    }

    /**
     * Returns the current repository, if any
     *
     * @return MainConfigurationRepository
     * @throws MainConfigurationRepositoryMissing
     */
    public static function get(): MainConfigurationRepository
    {
        if (null === static::$repository) {
            throw new MainConfigurationRepositoryMissing();
        }

        return static::$repository;
    }
}
