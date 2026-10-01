<?php

namespace Spineda\DddFoundation\Tests\Unit\Services\Shipping;

use Mockery\MockInterface;
use Spineda\DddFoundation\Exceptions\Shipping\Driver\DriverNotFoundException;
use Spineda\DddFoundation\Persistencies\Eloquent\Shipping\Driver as DriverModel;
use Spineda\DddFoundation\Repositories\Database\ORM\Shipping\ORMDriverRepository;
use Spineda\DddFoundation\Services\Shipping\DriverService;
use Spineda\DddFoundation\Tests\Unit\Services\AbstractServiceUnitTest;

/**
 * Class for testing the drivers service
 *
 * @package Spineda\DddFoundation\Tests
 */
class DriverServiceTest extends AbstractServiceUnitTest
{
    /**
     * Returns the driver the repository hydrates from the row the model finds
     *
     * @throws  DriverNotFoundException
     */
    public function testGetReturnsTheDriverOfTheRepository(): void
    {
        // Mocks the model only: the repository and the entity are the real ones.
        /** @var DriverModel|MockInterface $record */
        $record = $this->mock(DriverModel::class);
        $record->shouldReceive('getAttributes')
            ->andReturn(['id' => 7, 'code' => 'D-3107']);

        /** @var DriverModel|MockInterface $model */
        $model = $this->mock(DriverModel::class);
        $model->shouldReceive('newQuery->find')
            ->with(7)
            ->andReturn($record);

        $this->service = new DriverService(new ORMDriverRepository($model));

        // Performs the test.
        $driver = $this->service->get(7);

        // Performs assertions.
        static::assertSame('D-3107', $driver->getCode(), 'El código del conductor no coincide.');
    }
}
