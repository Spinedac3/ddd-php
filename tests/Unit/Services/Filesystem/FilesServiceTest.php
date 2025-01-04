<?php /** @noinspection PhpVoidFunctionResultUsedInspection */

namespace Spineda\DddFoundation\Tests\Unit\Services\Filesystem;

use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Services\Filesystem\FilesService;
use Spineda\DddFoundation\Tests\Unit\Services\AbstractServiceUnitTest;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Class for testing a files service
 *
 * @package Spineda\DddFoundation\Tests
 */
class FilesServiceTest extends AbstractServiceUnitTest
{
    /**
     * Returns a directory not found exception when the repository does that
     *
     * @throws  DirectoryNotFoundException
     */
    public function testDumpToFileDirectoryNotFoundException(): void
    {
        // Initializes the service using a mock of the repository
        $filesRepository = $this->getMockForAbstractClass(FilesRepository::class);
        $filesRepository->method('dumpToFile')
            ->willThrowException(new DirectoryNotFoundException('Stub'));

        $this->service = new FilesService($filesRepository);

        static::expectException(DirectoryNotFoundException::class);
        $this->service->dumpToFile(new File('stub.txt'));
    }

    /**
     * Dumps a certain repository to a file, successfully
     *
     * @throws  DirectoryNotFoundException
     */
    public function testDumpToFileSuccessfully(): void
    {
        // Initializes the service using a mock of the repository
        $filesRepository = $this->getMockForAbstractClass(FilesRepository::class);

        $this->service = new FilesService($filesRepository);

        // No return. No action tested because this is just a facade
        static::assertNull($this->service->dumpToFile(new File('stub.txt')));
    }
}
