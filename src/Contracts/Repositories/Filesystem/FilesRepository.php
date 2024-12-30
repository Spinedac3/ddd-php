<?php

namespace Spineda\DddFoundation\Contracts\Repositories\Filesystem;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Contracts\Repositories\AbstractRepository;
use Spineda\DddFoundation\Contracts\Repositories\Behaviors\DumpableRepository;
use Spineda\DddFoundation\ValueObjects\Collections\Filesystem\FileCollection;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Contract for files repositories
 *
 * @package Spineda\DddFoundation
 */
interface FilesRepository extends AbstractRepository, DumpableRepository
{
    /**
     * Gets a file from its repository
     *
     * @param   string  $name  File name
     *
     * @return  File
     * @throws  FileNotFoundException
     */
    public function get(string $name): File;

    /**
     * List all the files in a repository
     *
     * @return  FileCollection
     */
    public function listAll(): FileCollection;

    /**
     * List files modified after a certain datetime
     *
     * @param   Carbon  $timestamp
     * @param   string  $extension  File extension (optional)
     *
     * @return  FileCollection
     */
    public function listModifiedAfter(Carbon $timestamp, string $extension = ''): FileCollection;
}
