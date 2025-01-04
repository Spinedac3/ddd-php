<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Data;

use Spineda\DddFoundation\Contracts\IsString;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Data\SortInfo;
use InvalidArgumentException;
use JsonSerializable;
use Exception;

/**
 * Sort Information Datatype Unit testing.
 *
 * @package Spineda\DddFoundation\Tests
 */
class SortInfoTest extends AbstractUnitTest
{
    /**
     * Tests that an empty element is not allowed as an initial Stack's state.
     *
     * @return void
     */
    public function testEmptyInitialFieldIdNotAllowed(): void
    {
        // Creates expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        new SortInfo('', 'default');
    }

    /**
     * Tests that an empty element is not allowed as an initial Stack's state.
     *
     * @return void
     */
    public function testEmptyInitialDefaultIdNotAllowed(): void
    {
        // Creates expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        new SortInfo('field', '');
    }

    /**
     * Tests that the instance is loaded with the appropriate object structure.
     *
     * @return void
     */
    public function testLoadsStructureSuccessfully(): void
    {
        // Performs test.
        $sort = new SortInfo('Field', 'Default');

        // Performs assertions.
        static::assertInstanceOf(
            JsonSerializable::class,
            $sort,
            'La instancia debió haber sido de una clase JsonSerializable.'
        );
        static::assertInstanceOf(
            IsString::class,
            $sort,
            'La instancia debió haber sido de una clase IsString.'
        );
    }

    /**
     * Tests that instance is loaded with minimum required information.
     *
     * @return void
     */
    public function testLoadsSuccessfullyWithMinimumInformation(): void
    {
        // Performs test.
        $sort = new SortInfo('Field', 'Default');

        // Performs assertions.
        static::assertEquals(
            'field',
            $sort->getField(),
            'Los valores Field no coinciden.'
        );
        static::assertEquals(
            'asc',
            $sort->getOrder(),
            'Los valores Order no coinciden.'
        );
        static::assertTrue(
            $sort->isSorted(),
            'La instancia debió haberse reportado como ordenada.'
        );
        static::assertFalse(
            $sort->isSortedBy('Default'),
            'Debió haberse marcado como no ordenada por el campo provisto.'
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
            'Los valores Field no coinciden.'
        );
        static::assertEquals(
            'desc',
            $sort->getOrder(),
            'Los valores Order no coinciden.'
        );
        static::assertTrue(
            $sort->isSorted(),
            'La instancia debió haberse reportado como ordenada.'
        );
        static::assertTrue(
            $sort->isSortedBy('Field'),
            'Debió haberse marcado como no ordenada por el campo provisto.'
        );
    }

    /**
     * Tests that instance is loaded successfully.
     */
    public function testLoadsSuccessfullySortedInformation(): void
    {
        // Performs test.
        $sort = new SortInfo('Field', 'Field', true);

        // Performs assertions.
        static::assertFalse(
            $sort->isSorted(),
            'La instancia no debió haberse reportado como ordenada.'
        );
    }

    /**
     * Asserts that the pagination info can be serialized and unserialized successfully.
     *
     * @return void
     * @throws Exception
     */
    public function testSerializesSuccessfully(): void
    {
        // Performs test.
        $sort = new SortInfo('Field', 'Field');

        // Tests serialization.
        $data = $sort->__serialize();
        $sort->__unserialize($data);

        // Performs assertions.
        static::assertEquals(
            'field',
            $sort->getField(),
            'Los valores Field no coinciden.'
        );
        static::assertEquals(
            'asc',
            $sort->getOrder(),
            'Los valores Order no coinciden.'
        );
    }

    /**
     * Tests that the sorting information can be converted to a String.
     *
     * @return void
     */
    public function testCanBeConvertedToStringSuccessFully(): void
    {
        // Performs test.
        $sort = new SortInfo('Field', 'Field', false);

        // Performs assertions.
        static::assertEquals(
            "orderby=field&orderdir=desc",
            $sort->__toString(),
            'La conversión String no funcionó.'
        );
    }

    /**
     * Tests that the sorting information can be converted to JSON.
     *
     * @return void
     */
    public function testCanBeConvertedToJsonSuccessfully(): void
    {
        // Performs test.
        $sort = new SortInfo('Field', 'Default', true);
        $json = $sort->jsonSerialize();
        $text = json_encode($json);

        // Performs assertions.
        static::assertIsArray(
            $json,
            'Debió haber sido una instancia de Array.'
        );
        static::assertIsString(
            $text,
            'Debió haberse codificado como string..'
        );
        foreach (['field', 'default', 'order'] as $key) {
            static::assertArrayHasKey(
                $key,
                $json,
                'El arreglo no tiene los campos esperados.'
            );
        }
    }
}
