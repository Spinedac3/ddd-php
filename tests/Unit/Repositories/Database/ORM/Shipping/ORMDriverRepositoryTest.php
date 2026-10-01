<?php

namespace Spineda\DddFoundation\Tests\Unit\Repositories\Database\ORM\Shipping;

use InvalidArgumentException;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Spineda\DddFoundation\Exceptions\Shipping\Driver\DriverNotFoundException;
use Spineda\DddFoundation\Persistencies\Eloquent\Shipping\Driver as DriverModel;
use Spineda\DddFoundation\Repositories\Database\ORM\Shipping\ORMDriverRepository;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;

/**
 * Tests for the ORM repository of drivers
 *
 * @package Spineda\DddFoundation\Tests
 */
class ORMDriverRepositoryTest extends AbstractUnitTest
{
    /**
     * @var  DriverModel|MockInterface
     */
    protected DriverModel | MockInterface $model;

    /**
     * @var  ORMDriverRepository
     */
    protected ORMDriverRepository $repository;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->model = $this->mock(DriverModel::class);
        $this->repository = new ORMDriverRepository($this->model);
    }

    /**
     * Makes the model find the given record when it is queried by id
     *
     * @param   int               $id      Id the model is queried by
     * @param   DriverModel|null  $record  Record the query returns
     */
    protected function modelFinds(int $id, ?DriverModel $record): void
    {
        $this->model->shouldReceive('newQuery->find')
            ->with($id)
            ->andReturn($record);
    }

    /**
     * Tests that a non-positive id is rejected before querying.
     */
    public function testFindByIdRejectsANonPositiveId(): void
    {
        // Creates expectation.
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionCode(406);

        // Performs test.
        $this->repository->findById(0);
    }

    /**
     * Tests that a missing driver comes back as null.
     */
    public function testFindByIdReturnsNullWhenMissing(): void
    {
        // Mocks the query.
        $this->modelFinds(7, null);

        // Performs assertions.
        static::assertNull($this->repository->findById(7), 'El conductor debió haber sido null.');
    }

    /**
     * Tests that the record found is hydrated into the entity.
     */
    public function testFindByIdHydratesTheDriver(): void
    {
        // Mocks the query.
        /** @var DriverModel|MockInterface $record */
        $record = $this->mock(DriverModel::class);
        $record->shouldReceive('getAttributes')
            ->andReturn(['id' => 7, 'code' => 'D-3107']);
        $this->modelFinds(7, $record);

        // Performs the test.
        $driver = $this->repository->findById(7);

        // Performs assertions.
        static::assertSame('D-3107', $driver->getCode(), 'El código del conductor no coincide.');
    }

    /**
     * Tests that a key that is not a number is rejected as a bad argument.
     */
    public function testGetRejectsANonNumericKey(): void
    {
        // Creates expectation.
        static::expectException(InvalidArgumentException::class);
        static::expectExceptionCode(406);

        // Performs test.
        $this->repository->get('abc');
    }

    /**
     * Tests that getting a missing driver raises the not found exception.
     */
    public function testGetThrowsWhenMissing(): void
    {
        // Mocks the query.
        $this->modelFinds(7, null);

        // Creates expectation.
        static::expectException(DriverNotFoundException::class);
        static::expectExceptionCode(404);

        // Performs test.
        $this->repository->get(7);
    }
}
