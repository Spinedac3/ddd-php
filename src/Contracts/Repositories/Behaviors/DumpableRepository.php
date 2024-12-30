<?php

namespace Spineda\DddFoundation\Contracts\Repositories\Behaviors;

use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Contracts for repositories that can be dumped
 *
 * @package Spineda\DddFoundation
 */
interface DumpableRepository
{
    /**
     * Dump to a file destination
     *
     * @param   File  $file File destination
     *
     * @throws  DirectoryNotFoundException
     */
    public function dumpToFile(File $file): void;
}
