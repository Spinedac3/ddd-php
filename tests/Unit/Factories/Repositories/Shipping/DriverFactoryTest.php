<?php

namespace Spineda\DddFoundation\Tests\Unit\Factories\Repositories\Shipping;

use Spineda\DddFoundation\Contracts\Repositories\Shipping\DriverRepository;
use Spineda\DddFoundation\Factories\Repositories\Shipping\DriverFactory;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;

/**
 * Tests for the factory of driver repositories
 *
 * @package Spineda\DddFoundation\Tests
 */
class DriverFactoryTest extends AbstractUnitTest
{
    /**
     * Tests that the factory hands out the same repository every time.
     */
    public function testGetReturnsTheSameRepository(): void
    {
        // Performs the test.
        $first = DriverFactory::get();
        $second = DriverFactory::get();

        // Performs assertions.
        static::assertInstanceOf(DriverRepository::class, $first);
        static::assertSame($first, $second, 'La fábrica debió haber retornado el mismo repositorio.');
    }
}
