<?php

namespace Spineda\DddFoundation\Tests\Unit\Repositories\Filesystem\File;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Repositories\Filesystem\File\FileFilesRepository;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Collections\Filesystem\FileCollection;
use Spineda\DddFoundation\ValueObjects\File;
use ReflectionException;
use Exception;

/**
 * Class for testing a files repository inside a file
 *
 * @package Spineda\DddFoundation\Tests
 */
class FileFilesRepositoryTest extends AbstractUnitTest
{
    /**
     * @var FileFilesRepository
     */
    protected FileFilesRepository $repository;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     *
     * @throws FileNotFoundException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FileFilesRepository(new File(__DIR__ . '/Stubs/FilesRepository/stub.txt'));
    }

    /**
     * Tests the permission pattern
     *
     * @throws  ReflectionException
     */
    public function testPatternPermission(): void
    {
        $permissionPattern = '/^' . $this->callProtectedMethod(
                FileFilesRepository::class,
                $this->repository,
                'getPatternPermission',
                []
            ) . '$/';
        static::assertEquals(0, preg_match($permissionPattern, ''));
        static::assertEquals(0, preg_match($permissionPattern, ' '));
        static::assertEquals(0, preg_match($permissionPattern, 'rrr'));
        static::assertEquals(0, preg_match($permissionPattern, 'www'));
        static::assertEquals(0, preg_match($permissionPattern, 'xxx'));
        static::assertEquals(1, preg_match($permissionPattern, '---'));
        static::assertEquals(1, preg_match($permissionPattern, '--x'));
        static::assertEquals(1, preg_match($permissionPattern, '-wx'));
        static::assertEquals(1, preg_match($permissionPattern, 'rwx'));
        static::assertEquals(1, preg_match($permissionPattern, '-w-'));
        static::assertEquals(1, preg_match($permissionPattern, 'rw-'));
        static::assertEquals(1, preg_match($permissionPattern, 'r--'));
        static::assertEquals(1, preg_match($permissionPattern, 'rw-'));
        static::assertEquals(1, preg_match($permissionPattern, 'r-x'));
    }

    /**
     * Tests the user pattern
     *
     * @throws  ReflectionException
     */
    public function testPatternUser(): void
    {
        $userPattern = '/^' . $this->callProtectedMethod(
                FileFilesRepository::class,
                $this->repository,
                'getPatternUser',
                []
            ) . '$/';
        static::assertEquals(0, preg_match($userPattern, ''));
        static::assertEquals(0, preg_match($userPattern, ' '));
        static::assertEquals(1, preg_match($userPattern, '0'));
        static::assertEquals(1, preg_match($userPattern, 'a'));
        static::assertEquals(1, preg_match($userPattern, '_'));
        static::assertEquals(1, preg_match($userPattern, 'A'));
        static::assertEquals(1, preg_match($userPattern, 'username'));
        static::assertEquals(1, preg_match($userPattern, 'u_sername'));
        static::assertEquals(1, preg_match($userPattern, 'u-sername'));
        static::assertEquals(1, preg_match($userPattern, 'u1sername'));
        static::assertEquals(1, preg_match($userPattern, 'uSername'));
        static::assertEquals(1, preg_match($userPattern, '1009'));
    }

    /**
     * Tests the space pattern
     *
     * @throws  ReflectionException
     */
    public function testPatternSpace(): void
    {
        $spacePattern = '/^' . $this->callProtectedMethod(
                FileFilesRepository::class,
                $this->repository,
                'getPatternSpace',
                []
            ) . '$/';
        static::assertEquals(0, preg_match($spacePattern, ''));
        static::assertEquals(0, preg_match($spacePattern, '0'));
        static::assertEquals(0, preg_match($spacePattern, 'a'));
        static::assertEquals(0, preg_match($spacePattern, 'A'));
        static::assertEquals(0, preg_match($spacePattern, '_'));
        static::assertEquals(0, preg_match($spacePattern, '-'));
        static::assertEquals(1, preg_match($spacePattern, ' '));
        static::assertEquals(1, preg_match($spacePattern, '  '));
        static::assertEquals(1, preg_match($spacePattern, '   '));
    }

    /**
     * Tests the time pattern
     *
     * @throws  ReflectionException
     */
    public function testPatternTime(): void
    {
        $timePattern = '/^' . $this->callProtectedMethod(
                FileFilesRepository::class,
                $this->repository,
                'getPatternTime',
                []
            ) . '$/';
        static::assertEquals(0, preg_match($timePattern, ''));
        static::assertEquals(0, preg_match($timePattern, '0'));
        static::assertEquals(0, preg_match($timePattern, ':'));
        static::assertEquals(0, preg_match($timePattern, 'a:aa'));
        static::assertEquals(0, preg_match($timePattern, 'A:AA'));
        static::assertEquals(1, preg_match($timePattern, '0:00'));
        static::assertEquals(1, preg_match($timePattern, '1:00'));
        static::assertEquals(1, preg_match($timePattern, '9:00'));
        static::assertEquals(1, preg_match($timePattern, '00:00'));
        static::assertEquals(1, preg_match($timePattern, '01:00'));
        static::assertEquals(1, preg_match($timePattern, '10:00'));
        static::assertEquals(1, preg_match($timePattern, '20:00'));
        static::assertEquals(1, preg_match($timePattern, '23:00'));
        static::assertEquals(0, preg_match($timePattern, '24:00'));
        static::assertEquals(1, preg_match($timePattern, '00:01'));
        static::assertEquals(1, preg_match($timePattern, '00:59'));
        static::assertEquals(0, preg_match($timePattern, '00:60'));
        static::assertEquals(0, preg_match($timePattern, '00:99'));
    }

    /**
     * Tests the month pattern
     *
     * @throws  ReflectionException
     */
    public function testPatternMonth(): void
    {
        $timePattern = '/^' . $this->callProtectedMethod(
                FileFilesRepository::class,
                $this->repository,
                'getPatternMonth',
                []
            ) . '$/';
        static::assertEquals(0, preg_match($timePattern, ''));
        static::assertEquals(0, preg_match($timePattern, '0'));
        static::assertEquals(0, preg_match($timePattern, 'JAN'));
        static::assertEquals(0, preg_match($timePattern, 'jan'));
        static::assertEquals(1, preg_match($timePattern, 'Jan'));
        static::assertEquals(1, preg_match($timePattern, 'Feb'));
        static::assertEquals(1, preg_match($timePattern, 'Mar'));
        static::assertEquals(1, preg_match($timePattern, 'Apr'));
        static::assertEquals(1, preg_match($timePattern, 'May'));
        static::assertEquals(1, preg_match($timePattern, 'Jun'));
        static::assertEquals(1, preg_match($timePattern, 'Jul'));
        static::assertEquals(1, preg_match($timePattern, 'Aug'));
        static::assertEquals(1, preg_match($timePattern, 'Sep'));
        static::assertEquals(1, preg_match($timePattern, 'Oct'));
        static::assertEquals(1, preg_match($timePattern, 'Nov'));
        static::assertEquals(1, preg_match($timePattern, 'Dec'));
    }

    /**
     * Test parsing a file with 2 different formats: time and year
     *
     * @throws  ReflectionException
     * @throws  Exception
     */
    public function testParseFileFormats(): void
    {
        $line1 = 'drwxr-xr-x 1 root root   672 Jan  1 00:01 file';
        $line2 = 'drwxr-xr-x 1 root root   672 Jan  1  2018 file';

        /** @var File $file1 */
        $file1 = $this->callProtectedMethod(
            FileFilesRepository::class,
            $this->repository,
            'parseLine',
            [ $line1 ]
        );
        /** @var File $file2 */
        $file2 = $this->callProtectedMethod(
            FileFilesRepository::class,
            $this->repository,
            'parseLine',
            [ $line2 ]
        );

        static::assertEquals(Carbon::create(date('Y'), 1, 1, 0, 1), $file1->getModifiedDateTime());
        static::assertEquals(Carbon::create(2018), $file2->getModifiedDateTime());
    }

    /**
     * Test getting a full list of files
     *
     * @throws  Exception
     */
    public function testListAllSuccessful(): void
    {
        $files = $this->repository->listAll();
        static::assertInstanceOf(FileCollection::class, $files);
        static::assertEquals(6, $files->count());
    }

    /**
     * Test getting files modified after a certain date - and optionally with some extension
     *
     * @throws Exception
     */
    public function testListModifiedAfterSuccessful(): void
    {
        $files = $this->repository->listModifiedAfter(Carbon::create(date('Y') - 1, 11));
        static::assertInstanceOf(FileCollection::class, $files);
        static::assertEquals(4, $files->count());

        $files = $this->repository->listModifiedAfter(Carbon::create(date('Y') - 1, 11), 'xml');
        static::assertInstanceOf(FileCollection::class, $files);
        static::assertEquals(1, $files->count());
    }

    /**
     * Tests not finding a certain file in a repository
     *
     * @throws FileNotFoundException
     */
    public function testGetFileUnsuccessful(): void
    {
        static::expectException(FileNotFoundException::class);
        $this->repository->get('non-existing-file.txt');
    }

    /**
     * Tests dumping a file without an existing target directory
     *
     * @throws  DirectoryNotFoundException
     */
    public function testDumpFileTargetDirectoryNotFound(): void
    {
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
        $target = __DIR__ . '/Stubs/FilesRepository/Target/dump.txt';

        // Deletes the target if it already exists
        if (file_exists($target)) {
            unlink($target);
        }

        $this->repository->dumpToFile(new File($target));
        static::assertFileExists($target);
    }
}
