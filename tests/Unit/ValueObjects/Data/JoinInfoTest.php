<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Data;

use Spineda\DddFoundation\Exceptions\SearchCriteriaException;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;
use Spineda\DddFoundation\ValueObjects\Data\JoinInfo;

/**
 * Join Info Unit testing.
 *
 * @package Spineda\DddFoundation\Tests
 */
class JoinInfoTest extends AbstractUnitTest
{
    /**
     * Test that the object will load correctly, and will return the expected values.
     *
     * @return void
     * @throws SearchCriteriaException
     */
    public function testLoadsSuccessfully(): void
    {
        // Actions dummy data
        $joinInfo = new JoinInfo(
            'table1',
            'table2',
            'table1.id',
            'table2.id',
            new FiltersInfo([], '')
        );

        // Performs assertions.
        static::assertEquals(
            'table1',
            $joinInfo->getTable1(),
            'Los valores no coinciden.'
        );
        static::assertEquals(
            'table2',
            $joinInfo->getTable2(),
            'Los valores no coinciden.'
        );
        static::assertEquals(
            'table1.id',
            $joinInfo->getJoinFieldTable1(),
            'Los valores no coinciden.'
        );
        static::assertEquals(
            'table2.id',
            $joinInfo->getJoinFieldTable2(),
            'Los valores no coinciden.'
        );
        static::assertInstanceOf(
            FiltersInfo::class,
            $joinInfo->getFiltersFirstTable(),
            'Los valores no coinciden.'
        );
    }

    /**
     * Tests that can be successfully serialized into json
     *
     * @return void
     * @throws SearchCriteriaException
     */
    public function testAggregateCanBeSerializedIntoJson(): void
    {
        // Actions dummy data
        $joinInfo = new JoinInfo(
            'table1',
            'table2',
            'table1.id',
            'table2.id',
            new FiltersInfo([], '')
        );

        $json = $joinInfo->jsonSerialize();
        $text = json_encode($json);

        // Performs assertions.
        static::assertIsArray(
            $json,
            'Debió haber retornado un arreglo.'
        );
        static::assertIsString(
            $text,
            'Debió haberse codificado hacia una cadena.'
        );
        static::assertIsArray(
            json_decode($text, true),
            'Debió haberse serializado de vuelta a un arreglo.'
        );
        static::assertArrayHasKey(
            'table1',
            $json,
            'El arreglo debería tener una objeto table1.'
        );
        static::assertArrayHasKey(
            'table2',
            $json,
            'El arreglo debería tener una objeto table2.'
        );
        static::assertArrayHasKey(
            'joinFieldTable1',
            $json,
            'El arreglo debería tener una objeto joinFieldTable1.'
        );
        static::assertArrayHasKey(
            'joinFieldTable2',
            $json,
            'El arreglo debería tener una objeto joinFieldTable2.'
        );
        static::assertArrayHasKey(
            'filtersFirstTable',
            $json,
            'El arreglo debería tener una objeto filtersFirstTable.'
        );
    }
}
