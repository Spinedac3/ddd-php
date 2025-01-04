<?php

namespace Spineda\DddFoundation\Tests\Unit\Repositories\Filesystem\File;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Repositories\Filesystem\File\AbstractFileRepository;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Class for testing an abstract file repository
 *
 * @package Spineda\DddFoundation
 */
class AbstractFileRepositoryTest extends AbstractUnitTest
{
    /**
     * @var AbstractFileRepository|MockObject
     */
    protected AbstractFileRepository | MockObject $repository;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     *
     * @throws FileNotFoundException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->mockWithoutConstructor(AbstractFileRepository::class);
        $this->repository->__construct(new File(__DIR__ . '/Stubs/stub.txt'));
    }

    /**
     * Test constructing the repository with a no existing file
     *
     * @throws FileNotFoundException
     */
    public function testsConstructNonExistingFile(): void
    {
        static::expectException(FileNotFoundException::class);
        $this->repository = $this->mockWithoutConstructor(AbstractFileRepository::class);
        $this->repository->__construct(new File(__DIR__ . '/Stubs/non-existing-stub.txt'));
    }
}