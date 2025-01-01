<?php

namespace Builders\Services\System;

use Spineda\DddFoundation\Exceptions\System\MainConfiguration\MainConfigurationRepositoryMissing;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\Builders\Services\System\MainConfigurationServiceBuilder;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\InvalidConfigurationFileException;

/**
 * Tests for the main configuration service builder class
 *
 * @package Spineda\DddFoundation\Tests
 */
class MainConfigurationServiceBuilderTest extends AbstractUnitTest
{
    /**
     * Tests building the service from a file
     *
     * @throws  FileNotFoundException
     * @throws  DirectoryNotFoundException
     * @throws  InvalidConfigurationFileException
     * @throws  MainConfigurationRepositoryMissing
     */
    public function testBuildFromFile()
    {
        $this->assertTrue(MainConfigurationServiceBuilder::buildFromFile(getenv('TESTS_CONFIGURATION_FILE')));
    }
}