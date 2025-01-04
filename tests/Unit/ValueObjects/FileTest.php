<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects;

use Carbon\Carbon;
use Spineda\DddFoundation\ValueObjects\File;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use stdClass;

/**
 * Tests for File class
 *
 * @package Spineda\DddFoundation\Tests
 */
class FileTest extends AbstractUnitTest
{
    /**
     * @var File
     */
    protected File $file;

    /**
     * Test a successful load file and its getters
     */
    public function testLoadFileSuccessful(): void
    {
        $fileName = __DIR__ . '/Stubs/stub.txt';
        $modifiedDateTime = new Carbon('2025-01-01 13:00:00');
        $this->file = new File($fileName, $modifiedDateTime);

        static::assertInstanceOf(File::class, $this->file);
        static::assertEquals('stub', $this->file->getName());
        static::assertEquals(__DIR__ . '/Stubs', $this->file->getDirectory());
        static::assertEquals('stub.txt', $this->file->getBaseName());
        static::assertEquals('txt', $this->file->getExtension());
        static::assertEquals($fileName, $this->file->getFullPath());
        static::assertInstanceOf(Carbon::class, $this->file->getModifiedDateTime());
        static::assertTrue($this->file->exists());
    }

    /**
     * Tests that a file is successfully recognized as non-existing
     */
    public function testCheckNonExistingFileSuccessfully(): void
    {
        $fileName = __DIR__ . '/Stubs/non-existing-file.txt';
        $this->file = new File($fileName);
        static::assertFalse($this->file->exists());
    }

    /**
     * Test a correct JSON serialization
     */
    public function testJsonSerialize(): void
    {
        $fileName = __DIR__ . '/Stubs/stub.txt';
        $modifiedDateTime = new Carbon('2025-01-01 13:00:00');
        $this->file = new File($fileName, $modifiedDateTime);

        $properties = new stdClass();
        $properties->name = 'stub';
        $properties->modifiedDateTime = new Carbon('2025-01-01 13:00:00');
        $properties->directory = __DIR__ . '/Stubs';
        $properties->extension = 'txt';
        $properties->baseName = 'stub.txt';

        static::assertEquals($properties, $this->file->jsonSerialize());
    }
}
