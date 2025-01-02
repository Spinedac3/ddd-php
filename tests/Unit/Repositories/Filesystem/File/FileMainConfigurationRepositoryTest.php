<?php

namespace Spineda\DddFoundation\Tests\Unit\Repositories\Filesystem\File;

use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\InvalidConfigurationFileException;
use Spineda\DddFoundation\Repositories\Filesystem\File\FileMainConfigurationRepository;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\File;

/**
 * Tests for FileMainConfigurationRepository class
 *
 * @package Spineda\DddFoundation\Tests
 */
class FileMainConfigurationRepositoryTest extends AbstractUnitTest
{
    /**
     * @var FileMainConfigurationRepository
     */
    protected FileMainConfigurationRepository $repository;

    /**
     * Tests loading an invalid YAML file
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidYAMLFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalid.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no dbs
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbsFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodbs.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no db - connection name
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbConnectionNameFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodb-connectionname.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no db - username
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbUsernameFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodb-username.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no db - password
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbPasswordFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodb-password.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no db - port
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbPortFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodb-port.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no db - database name
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbDatabaseFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodb-database.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no db - host
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbHostFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodb-host.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no db - driver
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoDbDriverFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-nodb-driver.yaml')
        );
    }

    /**
     * Tests loading an invalid configuration file format - no tmp folder
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidFormatNoTmpFolderFileMainConfigurationRepository(): void
    {
        static::expectException(InvalidConfigurationFileException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalidformat-notmpfolder.yaml')
        );
    }

    /**
     * Tests feeding the configuration an invalid tmp folder
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testConstructInvalidTmpFolderFileMainConfigurationRepository(): void
    {
        static::expectException(DirectoryNotFoundException::class);
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/invalid-nonexisting-tmp.yaml')
        );
    }

    /**
     * Tests loading and returning each property of a valid main configuration file
     *
     * @throws  FileNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  DirectoryNotFoundException
     */
    public function testValidFileMainConfigurationRepository(): void
    {
        $this->repository = new FileMainConfigurationRepository(
            new File(__DIR__ . '/Stubs/MainConfigurationRepository/validconfiguration.yaml')
        );

        $connection = $this->repository->getConnection('dummy');

        static::assertInstanceOf(FileMainConfigurationRepository::class, $this->repository);
        static::assertEquals('dummy', $connection->getConnectionName());
        static::assertEquals('username', $connection->getUserName());
        static::assertEquals('password', $connection->getPassword());
        static::assertEquals('database', $connection->getDBName());
        static::assertEquals('host', $connection->getHost());
        static::assertEquals('driver', $connection->getDBDriver());
        static::assertEquals('port', $connection->getPort());
        static::assertEquals('./tests/tmp', $this->repository->getTmpFolder());
        static::assertEquals('America/Guatemala', $this->repository->getTimeZone());
    }
}
