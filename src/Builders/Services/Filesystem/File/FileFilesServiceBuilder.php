<?php

namespace Spineda\DddFoundation\Builders\Services\Filesystem\File;

use Spineda\DddFoundation\Builders\Repositories\Filesystem\File\FileFilesRepositoryBuilder;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Services\Filesystem\FilesService;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Builder of a files service using a specific file implementation
 *
 * @package Spineda\DddFoundation
 */
abstract class FileFilesServiceBuilder
{
    /**
     * Builds a file service using a certain file
     *
     * @param   File  $file
     *
     * @return  FilesService
     * @throws  FileNotFoundException
     */
    public static function build(File $file): FilesService
    {
        return new FilesService(
            FileFilesRepositoryBuilder::build($file)
        );
    }
}