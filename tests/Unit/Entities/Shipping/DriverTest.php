<?php

namespace Spineda\DddFoundation\Tests\Unit\Entities\Shipping;

use Spineda\DddFoundation\Entities\Shipping\Driver;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use UnderflowException;

/**
 * Tests for the Driver entity
 *
 * @package Spineda\DddFoundation\Tests
 */
class DriverTest extends AbstractUnitTest
{
    /**
     * Tests that the entity is hydrated with the columns of the row.
     */
    public function testHydratesTheColumns(): void
    {
        // Performs the test.
        $driver = new Driver(['id' => 7, 'code' => 'D-3107', 'first_name' => 'Ana']);

        // Performs assertions.
        static::assertSame(7, $driver->getId(), 'El id del conductor debió haber sido 7.');
        static::assertSame('D-3107', $driver->getCode(), 'El código del conductor no coincide.');
        static::assertSame('Ana', $driver->getFirstName(), 'El nombre del conductor no coincide.');
        static::assertNull($driver->getLastName(), 'El apellido del conductor debió haber sido null.');
    }

    /**
     * Tests that a row without its code does not build an entity.
     */
    public function testRequiresTheCode(): void
    {
        // Creates expectation.
        static::expectException(UnderflowException::class);

        // Performs test.
        new Driver(['id' => 7]);
    }
}
