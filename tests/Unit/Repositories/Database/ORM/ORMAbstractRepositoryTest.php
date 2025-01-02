<?php

namespace Spineda\DddFoundation\Tests\Unit\Repositories\Database\ORM;

use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Spineda\DddFoundation\Persistencies\Eloquent\AbstractModel;
use Spineda\DddFoundation\Repositories\Database\ORM\ORMAbstractRepository;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;
use Spineda\DddFoundation\Exceptions\SearchCriteriaException;
use ReflectionException;
use ReflectionClass;
use Spineda\DddFoundation\ValueObjects\Data\JoinInfo;

/**
 * Tests for the ORMAbstractRepository class
 *
 * @package Spineda\DddFoundation\Tests
 */
class ORMAbstractRepositoryTest extends AbstractUnitTest
{
    /**
     * @var  ORMAbstractRepository
     */
    protected ORMAbstractRepository $repository;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        parent::setUp();

        $model = $this->getMockForAbstractClass(
            AbstractModel::class
        );

        $this->repository = $this->getMockForAbstractClass(
            ORMAbstractRepository::class,
            [
                $model
            ]
        );
    }

    /**
     * translateOrder test: asc = false / desc = true
     *
     * @throws  ReflectionException
     */
    public function testTranslateOrder(): void
    {
        static::assertEquals(
            'asc',
            $this->callProtectedMethod(
                ORMAbstractRepository::class,
                $this->repository,
                'translateOrder',
                [ false ]
            )
        );
        static::assertEquals(
            'desc',
            $this->callProtectedMethod(
                ORMAbstractRepository::class,
                $this->repository,
                'translateOrder',
                [ true ]
            )
        );
    }

    /**
     * Sets up a Builder for tests
     *
     * @return Builder|MockObject
     */
    protected function setUpBuilder(): Builder | MockObject
    {
        // Sets up mocks
        /** @var Model|MockObject $model */
        $model = $this->mockWithoutConstructor(Model::class);
        $model->method('getTable')
            ->willReturn('table');

        /** @var Builder|MockObject $builder */
        $builder = $this->mockWithoutConstructor(Builder::class);
        $builder->method('getModel')
            ->willReturn($model);

        return $builder;
    }

    /**
     * Tests with no filters
     *
     * @throws ReflectionException
     */
    public function testProcessFiltersToQueryWithNoFilters(): void
    {
        $builder = $this->setUpBuilder();
        $builder->expects($this->never())
            ->method('getModel');
        $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'processFiltersToQuery',
            [
                $builder, null
            ]
        );
    }

    /**
     * Tests that the right filters are added to the query
     * @throws ReflectionException|SearchCriteriaException
     */
    public function testProcessFiltersToQueryWithFilters(): void
    {
        $builder = $this->setUpBuilder();
        $filterArray = [
            'f1' => 1,
            'f2' => 2
        ];
        $filters = new FiltersInfo($filterArray, '');
        $builder->expects($this->exactly(2))
            ->method('Where');
        $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'processFiltersToQuery',
            [
                $builder,
                $filters
            ]
        );
    }

    /**
     * Tests that the right query operators
     *
     * @throws ReflectionException
     */
    public function testConstructQueryWithSearchFieldsDirect(): void
    {
        $reflection = new ReflectionClass(ORMAbstractRepository::class);
        $this->setUpEntityProperty(
            $this->repository,
            $reflection,
            'searchFieldsDirect',
            ['f1', 'f2', 'f3']
        );

        //construct the query operators
        $unifiedQuery = $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'unifySearchQueries',
            [false]
        );

        //test assert operators by Fields
        foreach ($unifiedQuery as $query) {
            static::assertEquals('=', $query->operator);
        }
    }

    /**
     * Tests that the right query operators
     *
     * @throws ReflectionException
     */
    public function testConstructQueryWithSearchFieldsLike(): void
    {
        $reflection = new ReflectionClass(ORMAbstractRepository::class);
        $this->setUpEntityProperty(
            $this->repository,
            $reflection,
            'searchFieldsLike',
            ['f1', 'f2', 'f3']
        );

        //construct the query operators
        $unifiedQuery = $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'unifySearchQueries',
            [false]
        );

        //test assert operators by Fields
        foreach ($unifiedQuery as $query) {
            static::assertEquals('LIKE', $query->operator);
        }
    }

    /**
     * Tests that the right query operators
     *
     * @throws ReflectionException
     */
    public function testConstructQueryWithSearchDirectAndLike(): void
    {
        $reflection = new ReflectionClass(ORMAbstractRepository::class);
        $this->setUpEntityProperty(
            $this->repository,
            $reflection,
            'searchFieldsDirect',
            ['f1', 'f2', 'f3']
        );
        $this->setUpEntityProperty(
            $this->repository,
            $reflection,
            'searchFieldsLike',
            ['f4', 'f5', 'f6']
        );

        //array of operators
        $operators = [];

        //construct the query operators
        $unifiedQuery = $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'unifySearchQueries',
            [false]
        );

        //populate array with query construct
        foreach ($unifiedQuery as $query) {
            $operators[] = $query->operator;
        }

        //count array values
        $counts = array_count_values($operators);

        //test construct query
        static::assertEquals(3, $counts['=']);
        static::assertEquals(3, $counts['LIKE']);
    }

    /**
     * testValidateArgumentsArray
     *
     * @throws  ReflectionException
     */
    public function testValidateArgumentInteger(): void
    {
        $argument1 = -1;

        $arguments = array('argument1' => $argument1);

        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'validateArgumentsArray',
            [$arguments]
        );
    }

    /**
     * testValidateArgumentsArray
     *
     * @throws  ReflectionException
     */
    public function testValidateArgumentString(): void
    {
        $argument2 = '';

        $arguments = array('argument2' => $argument2);

        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'validateArgumentsArray',
            [$arguments]
        );
    }

    /**
     * Tests with filters and join info
     *
     * @throws ReflectionException
     * @throws SearchCriteriaException
     */
    public function testProcessFiltersToQueryWithFiltersAndJoinInfo(): void
    {
        $builder = $this->setUpBuilder();
        $filters = new FiltersInfo(['f1' => 1, 'f2' => 2], 'table2');
        $joinInfo = new JoinInfo(
            'table1',
            'joinFieldTable1',
            'table2',
            'joinFieldTable2',
            new FiltersInfo(['f3' => 3], 'table1')
        );
        $builder->expects($this->exactly(1))
            ->method('getModel')
            ->willReturn($this->mockWithoutConstructor(Model::class));
        $this->callProtectedMethod(
            ORMAbstractRepository::class,
            $this->repository,
            'processFiltersToQuery',
            [
                $builder, $filters, $joinInfo
            ]
        );
    }
}
