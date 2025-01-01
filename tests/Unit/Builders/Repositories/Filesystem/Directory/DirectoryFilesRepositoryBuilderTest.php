<?php

namespace Spineda\DddFoundation\Tests\Unit\Builders\Repositories\Filesystem\Directory;

use Spineda\DddFoundation\Builders\Repositories\Filesystem\Directory\DirectoryFilesRepositoryBuilder;
use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;

/**
 * Tests for the FilesystemFilesRepository builder class
 *
 * @package Spineda\DddFoundation\Tests
 */
class DirectoryFilesRepositoryBuilderTest extends AbstractUnitTest
{
    /**
     * @var string
     */
    protected string $producedClassName = FilesRepository::class;

    /**
     * Fails building a repository because of a non-existing directory being referenced
     *
     * @throws  DirectoryNotFoundException
     */
    public function testBuildFailDirectoryNotFound(): void
    {
        $this->expectException(DirectoryNotFoundException::class);
        DirectoryFilesRepositoryBuilder::build(__DIR__ . '/NonExistingDir');
    }

    /**
     * Tests that new objects can be built
     *
     * @return void
     * @throws DirectoryNotFoundException
     */
    public function testNewProducedObjectCanBeBuilt(): void
    {
        // Retrieves two new base objects.
        $repo1 = DirectoryFilesRepositoryBuilder::build(__DIR__ . '/Stubs');
        $repo2 = DirectoryFilesRepositoryBuilder::build(__DIR__ . '/Stubs');

        // Asserts that the correct contract class was retrieved.
        $this->assertInstanceOf(
            $this->producedClassName,
            $repo1,
            'Debió haber regresado una instancia de ' . $this->producedClassName
        );
        $this->assertInstanceOf(
            $this->producedClassName,
            $repo2,
            'Debió haber regresado una instancia de ' . $this->producedClassName
        );
        $this->assertNotSame(
            $repo1,
            $repo2,
            'Las instancias de ' . basename($this->producedClassName) . ' deben ser distintas'
        );
    }
}
