<?php

namespace Spineda\DddFoundation\Builders\Services\Filesystem\Directory;

use Spineda\DddFoundation\Builders\Repositories\Filesystem\Directory\DirectoryFilesRepositoryBuilder;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Services\Filesystem\FilesService;

/**
 * Builder of Files Service
 *
 * @package Spineda\DddFoundation
 */
abstract class DirectoryFilesServiceBuilder
{
    /**
     * Builds a files service, using a certain directory to build a specific directory implementation
     *
     * @param   string  $directory
     *
     * @return  FilesService
     * @throws  DirectoryNotFoundException
     */
    public static function build(string $directory): FilesService
    {
        return new FilesService(
            DirectoryFilesRepositoryBuilder::build($directory)
        );
    }
}
