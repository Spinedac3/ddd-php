<?php

namespace Spineda\DddFoundation\ValueObjects\Collections\Filesystem;

use Carbon\Carbon;
use Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsValueObject;
use Spineda\DddFoundation\ValueObjects\Collections\ValueObjectCollection;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Collection of files in a filesystem
 *
 * @package Spineda\DddFoundation
 */
class FileCollection extends ValueObjectCollection
{
    /**
     * {@inheritDoc}
     *
     * @see ValueObjectCollection::add()
     * @return ValueObjectCollection
     */
    public function add(IsValueObject $valueObject): ValueObjectCollection
    {
        return parent::add($valueObject);
    }

    /**
     * Gets the max modified date of the files in the collection
     *
     * @return Carbon|null
     */
    public function getMaxModifiedDateTime(): ?Carbon
    {
        // No files, returns null
        if (!count($this->collection)) {
            return null;
        }

        /** @var Carbon $maxFileDate */
        $maxFileDate = null;

        /** @var File $file */
        foreach ($this->collection as $file) {
            if (null === $maxFileDate || $file->getModifiedDateTime()->greaterThan($maxFileDate)) {
                $maxFileDate = $file->getModifiedDateTime();
            }
        }

        return $maxFileDate;
    }
}
