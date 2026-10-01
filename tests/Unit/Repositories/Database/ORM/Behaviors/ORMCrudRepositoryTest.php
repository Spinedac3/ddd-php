<?php

namespace Spineda\DddFoundation\Tests\Unit\Repositories\Database\ORM\Behaviors;

use Mockery\MockInterface;
use PDOException;
use PHPUnit\Framework\TestCase;
use Spineda\DddFoundation\Contracts\Repositories\Behaviors\CrudRepository;
use Spineda\DddFoundation\Exceptions\Behaviors\Crud\RecordNotCreatedException;
use Spineda\DddFoundation\Exceptions\Behaviors\Crud\RecordNotDeletedException;
use Spineda\DddFoundation\Exceptions\Behaviors\Crud\RecordNotUpdatedException;
use Spineda\DddFoundation\Persistencies\Eloquent\AbstractModel;
use Spineda\DddFoundation\Repositories\Database\ORM\Behaviors\ORMCrudRepository;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\Tests\Unit\Stubs\ConcreteEntitySuccessfulGetKey;

/**
 * Tests for the ORM implementation of the CRUD repository
 *
 * @package Spineda\DddFoundation\Tests
 */
class ORMCrudRepositoryTest extends AbstractUnitTest
{
    /**
     * @var ORMCrudRepository
     */
    protected ORMCrudRepository $repository;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new ORMCrudRepository();
    }

    /**
     * Mocks a model that hands out the given record for insertion
     *
     * @param   AbstractModel|MockInterface  $record  Record handed out by the model
     *
     * @return  AbstractModel|MockInterface
     */
    protected function mockModelWithRecord(AbstractModel | MockInterface $record): AbstractModel | MockInterface
    {
        /** @var AbstractModel|MockInterface $model */
        $model = $this->mock(AbstractModel::class);
        $model->shouldReceive('newInstance')
            ->andReturn($record);

        return $model;
    }

    /**
     * Tests that the repository honors the CRUD contract.
     */
    public function testImplementsTheContract(): void
    {
        static::assertInstanceOf(CrudRepository::class, $this->repository);
    }

    /**
     * Tests creating a record, returning its incrementing key.
     *
     * @throws  RecordNotCreatedException
     */
    public function testCreateSuccessfully(): void
    {
        // Mocks the record to be inserted.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class)->makePartial();
        $record->shouldReceive('save')->once()->andReturn(true);
        $record->shouldReceive('getIncrementing')->andReturn(true);
        $record->shouldReceive('getKey')->andReturn(42);

        // Performs the test.
        $entity = new ConcreteEntitySuccessfulGetKey(['field1' => 'a', 'field2' => 'b']);
        $id = $this->repository->create($entity, $this->mockModelWithRecord($record));

        // Performs assertions.
        static::assertSame(42, $id, 'El identificador creado debió haber sido 42.');
        static::assertSame('a', $record->field1, 'El campo field1 no se copió al registro.');
        static::assertSame('b', $record->field2, 'El campo field2 no se copió al registro.');
    }

    /**
     * Tests that a record that cannot be saved raises the creation exception.
     *
     * @throws  RecordNotCreatedException
     */
    public function testCreateNotSaved(): void
    {
        // Mocks the record to be inserted.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class)->makePartial();
        $record->shouldReceive('save')->andReturn(false);

        // Creates expectation.
        static::expectException(RecordNotCreatedException::class);

        // Performs test.
        $this->repository->create(['field1' => 'a'], $this->mockModelWithRecord($record));
    }

    /**
     * Tests that a database error while creating raises the creation exception.
     *
     * @throws  RecordNotCreatedException
     */
    public function testCreateDatabaseError(): void
    {
        // Mocks the record to be inserted.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class)->makePartial();
        $record->shouldReceive('save')->andThrow(new PDOException('Stub'));

        // Creates expectation.
        static::expectException(RecordNotCreatedException::class);

        // Performs test.
        $this->repository->create(['field1' => 'a'], $this->mockModelWithRecord($record));
    }

    /**
     * Tests creating a record without an incrementing key.
     *
     * @throws  RecordNotCreatedException
     */
    public function testCreateNotIncrementingSuccessfully(): void
    {
        // Mocks the record to be inserted.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class)->makePartial();
        $record->shouldReceive('save')->once()->andReturn(true);

        // Performs the test.
        $created = $this->repository->createNotIncrementing(['field1' => 'a'], $this->mockModelWithRecord($record));

        // Performs assertions.
        static::assertTrue($created, 'El registro debió haberse creado.');
    }

    /**
     * Tests updating a record with the data of the new entity.
     *
     * @throws  RecordNotUpdatedException
     */
    public function testUpdateSuccessfully(): void
    {
        // Mocks the existing record.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class)->makePartial();
        $record->shouldReceive('save')->once()->andReturn(true);

        // Performs the test.
        $entity = new ConcreteEntitySuccessfulGetKey(['field1' => 'new', 'field2' => 'b']);
        $updated = $this->repository->update($record, $entity);

        // Performs assertions.
        static::assertSame($record, $updated, 'El registro actualizado no coincide.');
        static::assertSame('new', $record->field1, 'El campo field1 no se actualizó.');
    }

    /**
     * Tests that a record that cannot be saved raises the update exception.
     *
     * @throws  RecordNotUpdatedException
     */
    public function testUpdateNotSaved(): void
    {
        // Mocks the existing record.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class)->makePartial();
        $record->shouldReceive('save')->andReturn(false);

        // Creates expectation.
        static::expectException(RecordNotUpdatedException::class);

        // Performs test.
        $this->repository->update($record, ['field1' => 'new']);
    }

    /**
     * Tests deleting a record.
     *
     * @throws  RecordNotDeletedException
     */
    public function testDeleteSuccessfully(): void
    {
        // Mocks the existing record.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class);
        $record->shouldReceive('delete')->once()->andReturn(true);

        // Performs assertions.
        static::assertTrue($this->repository->delete($record), 'El registro debió haberse eliminado.');
    }

    /**
     * Tests that a record that cannot be deleted raises the deletion exception.
     *
     * @throws  RecordNotDeletedException
     */
    public function testDeleteNotDeleted(): void
    {
        // Mocks the existing record.
        /** @var AbstractModel|MockInterface $record */
        $record = $this->mock(AbstractModel::class);
        $record->shouldReceive('delete')->andReturn(false);

        // Creates expectation.
        static::expectException(RecordNotDeletedException::class);

        // Performs test.
        $this->repository->delete($record);
    }
}
