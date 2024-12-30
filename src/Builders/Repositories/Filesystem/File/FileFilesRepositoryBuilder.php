<?php

namespace Spineda\DddFoundation\Builders\Repositories\Filesystem\File;

use Spineda\DddFoundation\Repositories\Filesystem\File\FileFilesRepository as Repository;
use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Files repository builder using a specific file implementation
 *
 * @package Spineda\DddFoundation
 */
abstract class FileFilesRepositoryBuilder
{
    /**
     * Build the repository using an incoming file
     *
     * @param  File  $file
     *
     * @return FilesRepository
     * @throws FileNotFoundException
     */
    public static function build(File $file): FilesRepository
    {
        return new Repository($file);
    }
}
