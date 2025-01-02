<?php

namespace Spineda\DddFoundation\Tests\Unit\Repositories\Filesystem\Directory;

use Carbon\Carbon;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Repositories\Filesystem\Directory\DirectoryFilesRepository;
use Spineda\DddFoundation\ValueObjects\Collections\FileSystem\FileCollection;
use Spineda\DddFoundation\ValueObjects\File;
use ReflectionException;

/**
 * Class for testing filesystem files repositories
 *
 * @package Spineda\DddFoundation\Tests
 */
class DirectoryFilesRepositoryTest extends AbstractUnitTest
{
    /**
     * @var DirectoryFilesRepository
     */
    protected DirectoryFilesRepository $repository;

    /**
     * Unsuccessful initialization of repository because of non-existing directory
     *
     * @throws  DirectoryNotFoundException
     */
    public function testConstructNonExistingDirectory(): void
    {
        static::expectException(DirectoryNotFoundException::class);
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/NonExistingDir');
    }

    /**
     * Unsuccessful get file test
     *
     * @throws  DirectoryNotFoundException
     * @throws  FileNotFoundException
     */
    public function testGetUnsuccessful(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs');

        static::expectException(FileNotFoundException::class);
        $this->repository->get('non-existing-file.txt');
    }

    /**
     * Successful file getting test
     *
     * @throws  DirectoryNotFoundException
     * @throws  FileNotFoundException
     */
    public function testGetSuccessful(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Files');

        $file = $this->repository->get('file1.txt');
        static::assertInstanceOf(File::class, $file);
    }

    /**
     * Listing files in an empty directory
     *
     * @throws  DirectoryNotFoundException
     * @throws  FileNotFoundException
     */
    public function testListEmptyDirectory(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Empty');

        $files = $this->repository->listAll();

        static::assertInstanceOf(FileCollection::class, $files);
        static::assertEquals(0, $files->count());
    }

    /**
     * Successful list all directory files
     *
     * @throws  DirectoryNotFoundException
     * @throws  FileNotFoundException
     */
    public function testListAllSuccessful(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Files');

        $files = $this->repository->listAll();

        static::assertInstanceOf(FileCollection::class, $files);
        static::assertEquals(2, $files->count());
    }

    /**
     * Successful list of files modified after a certain timestamp
     *
     * @throws  DirectoryNotFoundException
     * @throws  FileNotFoundException
     */
    public function testListModifiedAfterSuccessful(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Files');

        $files = $this->repository->listModifiedAfter(new Carbon('2024-01-01 22:00:00'));

        static::assertInstanceOf(FileCollection::class, $files);
        static::assertEquals(2, $files->count());

        $this->assertNotNull($files->current()->getModifiedDateTime());
    }

    /**
     * Successful list of files modified after a certain timestamp, using a certain extension
     *
     * @throws  DirectoryNotFoundException
     * @throws  FileNotFoundException
     */
    public function testListModifiedAfterExtensionSuccessful(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Files');

        $files = $this->repository->listModifiedAfter(new Carbon('2024-01-01 22:00:00'), 'txt');

        static::assertInstanceOf(FileCollection::class, $files);
        static::assertEquals(2, $files->count());

        static::assertNotNull($files->current()->getModifiedDateTime());
    }

    /**
     * Enrich files providing non-existing files
     *
     * @throws  DirectoryNotFoundException
     * @throws  ReflectionException
     */
    public function testEnrichNonExistingFile(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Files');

        static::expectException(FileNotFoundException::class);
        $files = [ __DIR__ . '/Stubs/Files/non-existing-file.txt' ];
        $this->callProtectedMethod(DirectoryFilesRepository::class, $this->repository, 'enrichFiles', [ $files ]);
    }

    /**
     * Tests dumping a file without an existing target directory
     *
     * @throws  DirectoryNotFoundException
     */
    public function testDumpFileTargetDirectoryNotFound(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Files');

        static::expectException(DirectoryNotFoundException::class);
        $this->repository->dumpToFile(new File(__DIR__ . '/Stubs/NonExistingDirectory/dump.txt'));
    }

    /**
     * Tests successfully dumping a file to a target directory
     *
     * @throws DirectoryNotFoundException
     */
    public function testDumpFileSuccessfully(): void
    {
        $this->repository = new DirectoryFilesRepository(__DIR__ . '/Stubs/Files');
        $target = __DIR__ . '/Stubs/Target/dump.txt';

        // Deletes the target if it already exists
        if (file_exists($target)) {
            unlink($target);
        }

        $this->repository->dumpToFile(new File($target));
        static::assertFileExists($target);
    }
}
