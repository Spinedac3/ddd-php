<?php

namespace Spineda\DddFoundation\Repositories\Filesystem\File;

use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Repositories\AbstractRepository;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * File repository - abstract
 *
 * @package Spineda\DddFoundation
 */
abstract class AbstractFileRepository extends AbstractRepository
{
    /**
     * @var File
     */
    protected File $file;

    /**
     * Constructor using a File value object
     *
     * @param   File  $file
     *
     * @throws  FileNotFoundException
     */
    public function __construct(File $file)
    {
        // Non-existing file
        if (!$file->exists()) {
            throw new FileNotFoundException($file->getFullPath());
        }

        $this->file = $file;
    }
}
