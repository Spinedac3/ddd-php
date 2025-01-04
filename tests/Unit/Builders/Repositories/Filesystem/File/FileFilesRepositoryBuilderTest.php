<?php

namespace Builders\Repositories\Filesystem\File;

use Spineda\DddFoundation\Builders\Repositories\Filesystem\File\FileFilesRepositoryBuilder;
use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Tester of file repository builder using file implementation
 *
 * @package Spineda\DddFoundation\Tests
 */
class FileFilesRepositoryBuilderTest extends AbstractUnitTest
{
    /**
     * @var string
     */
    protected string $producedClassName = FilesRepository::class;

    /**
     * Fails building a repository because of a non-existing file being referenced
     *
     * @throws  FileNotFoundException
     */
    public function testBuildFailDirectoryNotFound(): void
    {
        static::expectException(FileNotFoundException::class);
        FileFilesRepositoryBuilder::build(new File(__DIR__ . '/Stubs/non-existing-file'));
    }

    /**
     * Tests that new objects can be built
     *
     * @return void
     * @throws FileNotFoundException
     */
    public function testNewProducedObjectCanBeBuilt(): void
    {
        // Retrieves two new base objects.
        $repo1 = FileFilesRepositoryBuilder::build(new File(__DIR__ . '/Stubs/stub.txt'));
        $repo2 = FileFilesRepositoryBuilder::build(new File(__DIR__ . '/Stubs/stub.txt'));

        // Asserts that the correct contract class was retrieved.
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