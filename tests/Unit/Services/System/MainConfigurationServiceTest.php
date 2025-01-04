<?php

namespace Spineda\DddFoundation\Tests\Unit\Services\System;

use PHPUnit\Framework\MockObject\MockObject;
use Spineda\DddFoundation\Contracts\Repositories\System\MainConfigurationRepository;
use Spineda\DddFoundation\Entities\Database\Eloquent\Connection;
use Spineda\DddFoundation\Exceptions\System\MainConfiguration\MainConfigurationRepositoryMissing;
use Spineda\DddFoundation\Services\System\MainConfigurationService;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;

/**
 * Tests for the abstract/global MainConfigurationService class
 *
 * @package Spineda\DddFoundation\Tests
 */
class MainConfigurationServiceTest extends AbstractUnitTest
{
    /**
     * @var  ?MainConfigurationRepository $originalRepository
     */
    private ?MainConfigurationRepository $originalRepository = null;

    /**
     * {@inheritDoc}
     * @see  TestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();

        try {
            // Saves the real repository and empties it from the abstract service class
            $this->originalRepository = MainConfigurationService::getRepository();
            MainConfigurationService::setRepository(null);
        } catch (MainConfigurationRepositoryMissing $exception) {
            // No action
        }
    }

    /**
     * {@inheritDoc}
     * @see  TestCase::tearDown()
     */
    public function tearDown(): void
    {
        parent::tearDown();

        if (null !== $this->originalRepository) {
            // Restores any real repository found before starting the tests, if any
            MainConfigurationService::setRepository($this->originalRepository);
        }
    }

    /**
     * Tests that the method fails if no configuration is present
     *
     * @throws  MainConfigurationRepositoryMissing
     */
    public function testGetRepositoryFailsMissingConfiguration(): void
    {
        static::expectException(MainConfigurationRepositoryMissing::class);
        MainConfigurationService::getRepository();
    }

    /**
     * Tests that the method fails if no configuration is present
     *
     * @throws  MainConfigurationRepositoryMissing
     */
    public function testGetTmpFolderFailsMissingConfiguration(): void
    {
        static::expectException(MainConfigurationRepositoryMissing::class);
        MainConfigurationService::getTmpFolder();
    }


    /**
     * Tests that each of the repository properties are accessible
     *
     * @throws  MainConfigurationRepositoryMissing
     */
    public function testGetConfigurationPropertiesSuccessfully(): void
    {
        /** @var MainConfigurationRepository|MockObject $repository */
        $repository = $this->mockWithoutConstructor(MainConfigurationRepository::class);
        $repository->method('getTmpFolder')->willReturn('dummy');

        MainConfigurationService::setRepository($repository);

        static::assertInstanceOf(MainConfigurationRepository::class, MainConfigurationService::getRepository());
        static::assertInstanceOf(Connection::class, MainConfigurationService::getConnection('dummy'));
        static::assertIsString(MainConfigurationService::getTmpFolder());
    }
}
