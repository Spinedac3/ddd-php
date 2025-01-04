<?php

namespace Spineda\DddFoundation\Builders\Repositories\Filesystem\Directory;

use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Repositories\Filesystem\Directory\DirectoryFilesRepository as Repository;

/**
 * Builder of FilesRepository objects using the specific Directory implementation
 *
 * @package Spineda\DddFoundation
 */
abstract class DirectoryFilesRepositoryBuilder
{
    /**
     * Builds the repository using a certain directory
     *
     * @param   string  $directory
     *
     * @return  FilesRepository
     * @throws  DirectoryNotFoundException
     */
    public static function build(string $directory): FilesRepository
    {
        return new Repository($directory);
    }
}
