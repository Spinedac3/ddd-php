<?php

namespace Builders\Repositories\Filesystem\File;

use Spineda\DddFoundation\Builders\Repositories\Filesystem\File\FileMainConfigurationRepositoryBuilder;
use Spineda\DddFoundation\Contracts\Repositories\System\MainConfigurationRepository;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\InvalidConfigurationFileException;
use Spineda\DddFoundation\Exceptions\System\MainConfiguration\MainConfigurationRepositoryMissing;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\File;
use ReflectionClass;

/**
 * Tests for FileMainConfigurationRepositoryBuilder class
 *
 * @package Spineda\DddFoundation\Tests
 */
class FileMainConfigurationRepositoryBuilderTest extends AbstractUnitTest
{
    /**
     * @var  File
     */
    protected File $file;

    /**
     * {@inheritDoc}
     *
     * @see AbstractUnitTest::setUp()
     */
    public function setUp(): void
    {
        // Initializes parent class.
        parent::setUp();

        // Resets the internal property.
        $this->resetProperty();

        // Configuration stub file
        $this->file = new File(__DIR__ . '/Stubs/validconfiguration.yaml');
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractUnitTest::tearDown()
     */
    protected function tearDown(): void
    {
        // Resets the internal property.
        $this->resetProperty();

        // Tears down parent class.
        parent::tearDown();
    }

    /**
     * Resets the private property's value.
     *
     * Being a private static property, we'll need to change its visibility
     * before actually changing its value.
     *
     * @return void
     */
    private function resetProperty(): void
    {
        // Reflects Factory object.
        $reflection = new ReflectionClass(FileMainConfigurationRepositoryBuilder::class);
        $property = $reflection->getProperty('repository');
        $property->setValue(null);
    }

    /**
     * Tests that new base objects can be retrieved from Builder.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws InvalidConfigurationFileException
     * @throws DirectoryNotFoundException
     */
    public function testNewProducedObjectCanBeCreated(): void
    {
        // Retrieves two new base objects.
        $repo1 = FileMainConfigurationRepositoryBuilder::build($this->file);
        $repo2 = FileMainConfigurationRepositoryBuilder::build($this->file);

        // Asserts that the correct class was retrieved.
        static::assertInstanceOf(
            MainConfigurationRepository::class,
            $repo1,
            'Debió haber regresado una instancia de ' . MainConfigurationRepository::class
        );
        static::assertInstanceOf(
            MainConfigurationRepository::class,
            $repo2,
            'Debió haber regresado una instancia de ' . MainConfigurationRepository::class
        );
        static::assertNotSame(
            $repo1,
            $repo2,
            'Las instancias de ' . MainConfigurationRepository::class . ' deben ser distintas'
        );
    }

    /**
     * Tests that the same base objects instances can be retrieved from Builder.
     *
     * @return void
     * @throws FileNotFoundException
     * @throws InvalidConfigurationFileException
     * @throws MainConfigurationRepositoryMissing
     * @throws DirectoryNotFoundException
     */
    public function testSameProducedObjectsCanBeCreated(): void
    {
        // Builds the repository
        FileMainConfigurationRepositoryBuilder::build($this->file);

        // Retrieves two new base objects.
        $repo1 = FileMainConfigurationRepositoryBuilder::get();
        $repo2 = FileMainConfigurationRepositoryBuilder::get();

        // Asserts that the correct class was retrieved.
        static::assertInstanceOf(
            MainConfigurationRepository::class,
            $repo1,
            'Debió haber regresado una instancia de ' . MainConfigurationRepository::class
        );
        static::assertInstanceOf(
            MainConfigurationRepository::class,
            $repo2,
            'Debió haber regresado una instancia de ' . MainConfigurationRepository::class
        );
        static::assertSame(
            $repo1,
            $repo2,
            'Debió haber regresado la misma instancia de ' . MainConfigurationRepository::class
        );
    }

    /**
     * Tests that the builder fails getting the configuration when it hasn't been built
     *
     * @throws MainConfigurationRepositoryMissing
     */
    public function testFailGetMissingConfiguration(): void
    {
        static::expectException(MainConfigurationRepositoryMissing::class);
        FileMainConfigurationRepositoryBuilder::get();
    }
}