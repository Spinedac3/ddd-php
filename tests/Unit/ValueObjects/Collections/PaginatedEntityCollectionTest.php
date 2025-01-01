<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Collections;

use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use JsonSerializable;
use Mockery\MockInterface;
use Spineda\DddFoundation\ValueObjects\Collections\EntityCollection;
use Spineda\DddFoundation\ValueObjects\Collections\PaginatedEntityCollection;
use Spineda\DddFoundation\ValueObjects\Data\SortInfo;

/**
 * Entity's Paginated Collection Unit testing.
 *
 * @package Spineda\DddFoundation\Tests
 */
class PaginatedEntityCollectionTest extends AbstractUnitTest
{
    /**
     * Tests that a Paginated Collection loads correctly.
     *
     * @return void
     */
    public function testLoadsCorrectly(): void
    {
        // Mocks a collection object.
        /** @var EntityCollection $collection */
        $collection = $this->mock(EntityCollection::class);
        /** @var SortInfo $sortInfo */
        $sortInfo   = $this->mock(SortInfo::class);

        // Performs the test.
        $paginated = new PaginatedEntityCollection(
            $collection,
            $sortInfo,
            10,
            2,
            3,
            9,
            1,
            11
        );

        // Performs assertions.
        static::assertEquals(
            $collection,
            $paginated->getCollection(),
            'Las colecciones retornadas no coinciden.'
        );
        static::assertEquals(
            $sortInfo,
            $paginated->getSortInfo(),
            'La información de ordenamiento no coincide.'
        );
        static::assertEquals(
            1,
            $paginated->getFirstId(),
            'El primer registro debió haber sido 1.'
        );
        static::assertEquals(
            11,
            $paginated->getLastId(),
            'El último registro debió haber sido 11.'
        );
    }

    /**
     * Tests that an empty collection can be loaded into a Paginated Collection.
     *
     * @return void
     */
    public function testLoadsCorrectlyEmptyCollection(): void
    {
        // Mocks a collection object.
        /** @var EntityCollection $collection */
        $collection = $this->mock(EntityCollection::class);
        /** @var SortInfo $sortInfo */
        $sortInfo   = $this->mock(SortInfo::class);

        // Performs the test.
        $paginated = new PaginatedEntityCollection(
            $collection,
            $sortInfo,
            0,
            1,
            1,
            9,
            null,
            null
        );

        // Performs assertions.
        static::assertEquals(
            $collection,
            $paginated->getCollection(),
            'Las colecciones retornadas no coinciden.'
        );
        static::assertNull(
            $paginated->getFirstId(),
            'El primer registro debió haber sido null.'
        );
        static::assertNull(
            $paginated->getLastId(),
            'El último registro debió haber sido null.'
        );
    }

    /**
     * Tests the Paginated Collection can validate page numbers correctly.
     *
     * @return void
     */
    public function testCountsPagesCorrectly(): void
    {
        // Mocks a collection object.
        /** @var EntityCollection|MockInterface $collection */
        $collection = $this->mock(EntityCollection::class);
        /** @var SortInfo $sortInfo */
        $sortInfo   = $this->mock(SortInfo::class);

        // Configures mockery.
        $collection->shouldReceive('count')
            ->once()
            ->andReturn(5);

        // Performs the test.
        $paginated = new PaginatedEntityCollection(
            $collection,
            $sortInfo,
            10,
            2,
            3,
            9,
            1,
            11
        );

        // Performs assertions.
        static::assertEquals(
            10,
            $paginated->getTotalRecords(),
            'El número total de registros debió haber sido 10.'
        );
        static::assertEquals(
            5,
            $paginated->getTotal(),
            'El número total de registros debió haber sido 5.'
        );
        static::assertEquals(
            2,
            $paginated->getCurrentPage(),
            'La página actual debió haber sido la 2.'
        );
        static::assertEquals(
            3,
            $paginated->getLastPage(),
            'La última página debió haber sido la 3.'
        );
        static::assertEquals(
            9,
            $paginated->getPerPage(),
            'Los registros por página debieron haber sido 9.'
        );
    }

    /**
     * Tests the Paginated Collection can process the page record index numbers correctly.
     *
     * @return void
     */
    public function testCountsPageIndexesCorrectly(): void
    {
        // Adds counters.
        $perPage = 10;
        $count   = 5;

        // Mocks a collection object.
        /** @var SortInfo $sortInfo */
        $sortInfo   = $this->mock(SortInfo::class);
        /** @var EntityCollection|MockInterface $collection */
        $collection = $this->mock(EntityCollection::class);
        $collection->shouldReceive('count')
            ->twice()
            ->andReturn($count);

        // Performs the test.
        $paginated = new PaginatedEntityCollection(
            $collection,
            $sortInfo,
            (2 * $perPage),
            2,
            2,
            $perPage,
            11,
            19
        );

        // Performs assertions.
        static::assertEquals(
            $perPage,
            $paginated->getFromRecord(),
            'El primer registro no coincide.'
        );
        static::assertEquals(
            ($perPage + $count),
            $paginated->getToRecord(),
            'El último registro no coincide.'
        );
    }

    /**
     * Tests the Paginated Collection can process the page record index numbers correctly, when
     * the Collection's record count equals the number of records per page.
     */
    public function testCountsPageIndexesCorrectlyWhenEqualsPerPageTotal(): void
    {
        // Adds counters.
        $perPage = 10;

        // Mocks a collection object.
        /** @var SortInfo $sortInfo */
        $sortInfo   = $this->mock(SortInfo::class);
        /** @var EntityCollection|MockInterface $collection */
        $collection = $this->mock(EntityCollection::class);
        $collection->shouldReceive('count')
            ->once()
            ->andReturn($perPage);

        // Performs the test.
        $paginated = new PaginatedEntityCollection(
            $collection,
            $sortInfo,
            (3 * $perPage),
            2,
            3,
            $perPage,
            1,
            11
        );

        // Performs assertions.
        static::assertEquals(
            $perPage,
            $paginated->getFromRecord(),
            'El primer registro no coincide.'
        );
        static::assertEquals(
            ($perPage + $perPage),
            $paginated->getToRecord(),
            'El último registro no coincide.'
        );
    }

    /**
     * Tests the object can be converted into an array.
     *
     * @return void
     */
    public function testCanConvertObjectToArray(): void
    {
        // Mocks a collection object.
        /** @var SortInfo $sortInfo */
        $sortInfo   = $this->mock(SortInfo::class);
        /** @var EntityCollection|MockInterface $collection */
        $collection = $this->mock(EntityCollection::class);
        $collection->shouldReceive('count')
            ->twice()
            ->andReturn(5);

        // Creates the dummy data to be returned by the object.
        $data = [
            'collection'   => $collection,
            'sortInfo'     => $sortInfo,
            'fromRecord'   => 9,
            'toRecord'     => 14,
            'totalRecords' => 14,
            'currentPage'  => 2,
            'lastPage'     => 3,
            'perPage'      => 9,
        ];

        // Performs the test.
        $paginated = new PaginatedEntityCollection(
            $collection,
            $sortInfo,
            $data['totalRecords'],
            $data['currentPage'],
            $data['lastPage'],
            $data['perPage'],
            $data['fromRecord'],
            $data['toRecord']
        );
        $returned = $paginated->getToArray();

        // Performs assertions.
        static::assertIsArray(
            $returned,
            'El tipo debió haber sido array.'
        );

        foreach ($data as $key => $value) {
            static::assertArrayHasKey(
                $key,
                $returned,
                'El arreglo retornado no tiene la llave esperada.'
            );
            static::assertEquals(
                $value,
                $returned[$key],
                'El arreglo retornado no coincide.'
            );
        }
    }

    /**
     * Tests that the <i>PaginatedCollection</i> can be successfully serialized into <i>Json</i>.
     *
     * @return void
     */
    public function testCanSerializeObjectToJson(): void
    {
        // Mocks a collection object.
        /** @var SortInfo|MockInterface $sortInfo */
        $sortInfo = $this->mock(SortInfo::class);
        $sortInfo->shouldReceive('jsonSerialize')
            ->once()
            ->withNoArgs()
            ->andReturn([]);
        /** @var EntityCollection|MockInterface $collection */
        $collection = $this->mock(EntityCollection::class);
        $collection->shouldReceive('count')
            ->twice()
            ->andReturn(5);
        $collection->shouldReceive('jsonSerialize')
            ->once()
            ->withNoArgs()
            ->andReturn([]);

        // Creates the dummy data to be returned by the object.
        $data = [
            'collection'   => $collection,
            'sortInfo'     => $sortInfo,
            'fromRecord'   => 9,
            'toRecord'     => 14,
            'totalRecords' => 14,
            'currentPage'  => 2,
            'lastPage'     => 3,
            'perPage'      => 9,
        ];

        // Performs the test.
        $paginated = new PaginatedEntityCollection(
            $collection,
            $sortInfo,
            $data['totalRecords'],
            $data['currentPage'],
            $data['lastPage'],
            $data['perPage'],
            $data['fromRecord'],
            $data['toRecord']
        );
        $json = $paginated->jsonSerialize();
        $text = json_encode($json);

        // Performs assertions.
        static::assertInstanceOf(
            JsonSerializable::class,
            $paginated,
            'La instancia de la colección paginada debió haber sido de clase JsonSerializable.'
        );
        static::assertIsArray(
            $json,
            'Debió haberse retornado una instancia de array.'
        );
        static::assertIsString(
            $text,
            'Debió haberse codificado a una cadena.'
        );
        static::assertIsArray(
            json_decode($text, true),
            'Debió haberse serializado de vuelta a un array.'
        );

        foreach ($data as $key => $value) {
            static::assertArrayHasKey(
                $key,
                $json,
                'El arreglo retornado no tiene la llave esperada.'
            );
        }
    }
}