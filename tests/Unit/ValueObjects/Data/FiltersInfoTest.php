<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Data;

use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;
use Spineda\DddFoundation\ValueObjects\Data\SortInfo;
use Spineda\DddFoundation\Exceptions\SearchCriteriaException;
use JsonSerializable;

/**
 * Filters Information Datatype Unit testing.
 *
 * @package Spineda\DddFoundation\Tests
 */
class FiltersInfoTest extends AbstractUnitTest
{
    /**
     * Tests that the instance does not load when the search criteria is not enough
     *
     * @return void
     * @throws SearchCriteriaException
     */
    public function testLoadsStructureUnsuccessfully(): void
    {
        static::expectException(SearchCriteriaException::class);

        // Performs test.
        new FiltersInfo(['published' => false], '12');
    }

    /**
     * Tests that the instance is loaded with the appropriate object structure.
     *
     * @return void
     * @throws SearchCriteriaException
     */
    public function testLoadsStructureSuccessfully(): void
    {
        // Performs test.
        $filtersInfo = new FiltersInfo(['published' => false], 'Dummy Text');

        // Performs assertions.
        static::assertInstanceOf(
            JsonSerializable::class,
            $filtersInfo,
            'La instancia retornada debió haber sido de clase JsonSerializable.'
        );
    }

    /**
     * Tests that instance is loaded successfully.
     *
     * @return void
     */
    public function testLoadsSuccessfullyWithDescendingValue(): void
    {
        // Performs test.
        $sort = new SortInfo('Field', 'Field', false);

        // Performs assertions.
        static::assertEquals(
            'field',
            $sort->getField(),
            'Los valores de Field no coinciden.'
        );
        static::assertEquals(
            'desc',
            $sort->getOrder(),
            'Los valores de Order no coinciden.'
        );
        static::assertTrue(
            $sort->isSorted(),
            'La instancia debió haberser marcado como ordenada.'
        );
        static::assertTrue(
            $sort->isSortedBy('Field'),
            'La instancia debió haberse retornado como no ordenada por el campo dado.'
        );
    }

    /**
     * Asserts that the FilterInfo can be serialized and un-serialized successfully.
     *
     * @return void
     * @throws SearchCriteriaException
     */
    public function testSerializesSuccessfully(): void
    {
        // Performs test.
        $filtersInfo = new FiltersInfo(['active' => true], 'Dummy Text');

        // Tests serialization.
        $data = $filtersInfo->__serialize();
        $filtersInfo->__unserialize($data);

        // Performs assertions.
        static::assertEquals(
            "1",
            $filtersInfo->getFilters()->get('active'),
            'Los valores de Field no coinciden.'
        );
        static::assertEquals(
            'Dummy Text',
            $filtersInfo->getSearch(),
            'Los valores de Search no coinciden.'
        );
    }

    /**
     * Tests that the Filters information can be converted to JSON.
     *
     * @return void
     * @throws SearchCriteriaException
     */
    public function testCanBeConvertedToJsonSuccessfully(): void
    {
        // Performs test.
        $filtersInfo = new FiltersInfo(['active' => true], 'Default');
        $json = $filtersInfo->jsonSerialize();
        $text = json_encode($json);

        // Performs assertions.
        static::assertIsArray(
            $json,
            'Debió haber sido una instancia de array.'
        );
        static::assertIsString(
            $text,
            'Debió haberse codificado a una cadena.'
        );
        foreach (['filters', 'search'] as $key) {
            static::assertArrayHasKey(
                $key,
                $json,
                'El arreglo retornado no tiene los campos esperados.'
            );
        }
    }
}
