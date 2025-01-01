<?php

namespace Builders\Services\Filesystem\File;

use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\Builders\Services\Filesystem\File\FileFilesServiceBuilder;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Services\Filesystem\FilesService;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Tests for the builder class of the files service - specific file implementation
 *
 * @package Spineda\DddFoundation\Tests
 */
class FileFilesServiceBuilderTest extends AbstractUnitTest
{
    /**
     * @var string
     */
    protected string $producedClassName = FilesService::class;

    /**
     * Tests a failed build due to a non-existing file
     *
     * @throws  FileNotFoundException
     */
    public function testBuildFailNonExistingDirectory(): void
    {
        $this->expectException(FileNotFoundException::class);
        FileFilesServiceBuilder::build(new File(__DIR__ . '/Stubs/non-existing-file.txt'));
    }

    /**
     * Tests that new objects can be built
     *
     * @throws FileNotFoundException
     */
    public function testNewProducedObjectCanBeBuilt(): void
    {
        // Retrieves two new base objects.
        $repo1 = FileFilesServiceBuilder::build(new File(__DIR__ . '/Stubs/stub.txt'));
        $repo2 = FileFilesServiceBuilder::build(new File(__DIR__ . '/Stubs/stub.txt'));

        // Asserts that the correct class was retrieved.
        static::assertInstanceOf(
            $this->producedClassName,
            $repo1,
            'Debió haber regresado una instancia de ' . $this->producedClassName
        );
        static::assertInstanceOf(
            $this->producedClassName,
            $repo2,
            'Debió haber regresado una instancia de ' . $this->producedClassName
        );
        static::assertNotSame(
            $repo1,
            $repo2,
            'Las instancias de ' . basename($this->producedClassName) . ' deben ser distintas'
        );
    }
}