<?php

namespace Spineda\DddFoundation\ValueObjects;

use Carbon\Carbon;
use Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsValueObject;

/**
 * File Value Object
 *
 * @package Spineda\DddFoundation
 */
class File implements IsValueObject
{
    /**
     * @var  string
     */
    protected string $name;

    /**
     * @var ?Carbon
     */
    protected ?Carbon $modifiedDateTime;

    /**
     * @var string
     */
    protected string $directory;

    /**
     * @var string
     */
    protected string $extension;

    /**
     * @var string
     */
    protected string $baseName;

    /**
     * File constructor using the file name
     *
     * @param   string      $fullFileName
     * @param   Carbon|null $modifiedDateTime
     */
    public function __construct(string $fullFileName, ?Carbon $modifiedDateTime = null)
    {
        // Load File's information.
        $pathInfo               = pathinfo($fullFileName);
        $this->name             = $pathInfo['filename'];
        $this->directory        = $pathInfo['dirname'];
        $this->extension        = $pathInfo['extension'] ?? '';
        $this->baseName         = $pathInfo['basename'];
        $this->modifiedDateTime = $modifiedDateTime;
    }

    /**
     * File name getter
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Directory getter
     *
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Extension getter
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * Base name getter
     *
     * @return string
     */
    public function getBaseName(): string
    {
        return $this->baseName;
    }

    /**
     * Modified datetime getter
     *
     * @return Carbon|null
     */
    public function getModifiedDateTime(): ?Carbon
    {
        return $this->modifiedDateTime;
    }

    /**
     * Get the object attributes
     *
     * @return array
     */
    protected function getAttributes(): array
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritDoc}
     *
     * @return object
     * @see    JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): object
    {
        return (object) $this->getAttributes();
    }

    /**
     * Gets the file full path
     */
    public function getFullPath(): string
    {
        return $this->directory . '/' . $this->baseName;
    }

    /**
     * Check if the provided file actually exists or not
     */
    public function exists(): bool
    {
        return file_exists($this->getFullPath());
    }
}
