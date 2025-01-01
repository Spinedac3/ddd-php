<?php

namespace Builders\Services\Filesystem\Directory;

use Spineda\DddFoundation\Builders\Services\Filesystem\Directory\DirectoryFilesServiceBuilder;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Services\Filesystem\FilesService;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;


/**
 * Tests for the builder class of the files service - specific directory implementation
 *
 * @package Spineda\DddFoundation\Tests
 */
class DirectoryFilesServiceBuilderTest extends AbstractUnitTest
{
    /**
     * @var string
     */
    protected string $producedClassName = FilesService::class;

    /**
     * Tests a failed build due to a non-existing directory
     *
     * @throws  DirectoryNotFoundException
     */
    public function testBuildFailNonExistingDirectory(): void
    {
        $this->expectException(DirectoryNotFoundException::class);
        DirectoryFilesServiceBuilder::build(__DIR__ . 'NonExistingDirectory');
    }

    /**
     * Tests that new objects can be built
     *
     * @throws DirectoryNotFoundException
     */
    public function testNewProducedObjectCanBeBuilt(): void
    {
        // Retrieves two new base objects.
        $repo1 = DirectoryFilesServiceBuilder::build(__DIR__ . '/Stubs');
        $repo2 = DirectoryFilesServiceBuilder::build(__DIR__ . '/Stubs');

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