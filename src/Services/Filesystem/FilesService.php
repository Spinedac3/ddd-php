<?php

namespace Spineda\DddFoundation\Services\Filesystem;

use Spineda\DddFoundation\Contracts\IsService;
use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Domain service for colors
 *
 * @package Spineda\DddFoundation
 */
class FilesService implements IsService
{
    /**
     * @var  FilesRepository
     */
    protected FilesRepository $files;

    /**
     * Constructor of Files service
     *
     * @param   FilesRepository  $files  Files repository implementation
     */
    public function __construct(FilesRepository $files)
    {
        $this->files = $files;
    }

    /**
     * Dump the attached files repository to the specified file
     *
     * @param   File  $file
     *
     * @throws  DirectoryNotFoundException
     */
    public function dumpToFile(File $file): void
    {
        $this->files->dumpToFile($file);
    }
}