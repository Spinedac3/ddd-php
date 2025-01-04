<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Collections;

use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Collections\Store;
use Spineda\DddFoundation\Contracts\ValueObjects\Collections\Store as Contract;
use InvalidArgumentException;
use JsonSerializable;

/**
 * General Store's Unit testing.
 *
 * @package Spineda\DddFoundation\Tests
 */
class StoreTest extends AbstractUnitTest
{
    /**
     * @var  array  $dummy  Dummy data array.
     */
    private array $dummy = [];

    /**
     * Sets up the environment for the tests.
     *
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    public function setUp(): void
    {
        // Calls parent method.
        parent::setUp();

        // Initializes Dummy data array.
        $this->dummy = [];
        for ($i = 0; $i < 10; $i++) {
            $this->dummy['Key' . $i] = 'Value' . $i;
        }
    }

    /**
     * Tests Store structure implementation.
     *
     * @return void
     */
    public function testStoreStructure(): void
    {
        // Performs test.
        $store = new Store(null);

        // Performs assertions.
        static::assertInstanceOf(Contract::class, $store);
        static::assertInstanceOf(JsonSerializable::class, $store);
    }

    /**
     * Asserts Store cannot be created if supplied with an invalid $context.
     *
     * @return void
     */
    public function testStoreConstructInvalidContext(): void
    {
        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        new Store('');
    }

    /**
     * Asserts Store can be created if supplied with a null $context.
     *
     * @return void
     */
    public function testStoreConstructNullContext(): void
    {
        // Performs test.
        $store = new Store(null);

        // Performs assertions.
        $this->assertIsArray(
            $store->getValues(),
            'Los valores debieron haber retornado como arreglo.'
        );
        $this->assertIsArray(
            $store->jsonSerialize(),
            'Los valores debieron haber retornado como arreglo.'
        );
        $this->assertCount(
            0,
            $store->getValues(),
            'El Store no debió haber tenido ningún valor.'
        );
    }

    /**
     * Asserts Store breaks if supplied with an empty $name in method.
     *
     * @return void
     */
    public function testGetBreaksWithInvalidName(): void
    {
        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        $store = new Store(null);
        $store->get('');
    }

    /**
     * Asserts Store breaks if supplied with an empty $name in method.
     *
     * @return void
     */
    public function testSetBreaksWithInvalidName(): void
    {
        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        $store = new Store(null);
        $store->set('', '');
    }

    /**
     * Asserts Store breaks if supplied with an empty $name in method.
     *
     * @return void
     */
    public function testHasBreaksWithInvalidName(): void
    {
        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        $store = new Store(null);
        $store->has('');
    }

    /**
     * Asserts Store breaks if supplied with an empty $name in method.
     *
     * @return void
     */
    public function testAddBreaksWithInvalidName(): void
    {
        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        $store = new Store(null);
        $store->add('', '');
    }

    /**
     * Asserts Store breaks if supplied with an empty $name in method.
     *
     * @return void
     */
    public function testEditBreaksWithInvalidName(): void
    {
        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        $store = new Store(null);
        $store->edit('', '');
    }

    /**
     * Asserts Store breaks if supplied with an empty $name in method.
     *
     * @return void
     */
    public function testDeleteBreaksWithInvalidName(): void
    {
        // Creates Expectation.
        static::expectException(InvalidArgumentException::class);

        // Performs test.
        $store = new Store(null);
        $store->delete('');
    }

    /**
     * Tests Store can hold the values that were added to it.
     *
     * @return void
     */
    public function testStoreCanHoldValuesSuccessfully(): void
    {
        // Performs test.
        $store = new Store(null);
        static::assertInstanceOf(
            Store::class,
            $store,
            'El objeto debió haber sido una instancia de Store.'
        );

        /*
         * Inserts all records into the store.
         *
         * Asserts the Store is holding the same number of elements as there were inserted.
         *
         * Retrieves all Store's elements and compares them.
         */
        foreach ($this->dummy as $key => $value) {
            $store->add($key, $value);
        }

        $values = $store->getValues();
        static::assertCount(
            count($this->dummy),
            $values,
            'El número de elementos del Store no coincide.'
        );

        // Validates each and every single value.
        foreach ($this->dummy as $key => $value) {
            static::assertEquals(
                $values[strtolower($key)],
                $value,
                'Los valores retornados no coinciden.'
            );
        }

        // Validates get() returns default's NULL value if not found.
        $getValue = $store->get('SomeDummyName');
        static::assertNull($getValue, 'Debió haber retornado null.');
    }

    /**
     * Tests that Store's values can be edited.
     *
     * @return void
     */
    public function testStoreCanEditValues(): void
    {
        // Performs test.
        $store = new Store(null);
        foreach ($this->dummy as $key => $value) {
            $store->add($key, $value);
        }

        // Edits 2 values in the Store, one using edit, another one using set.
        $res1 = $store->edit('Key3', 'NewValue3');
        $res2 = $store->edit('Key4', 'NewValue4');

        // Retrieves the 2 new values form the store.
        static::assertTrue($res1, 'El resultado de la operación debió haber sido true.');
        static::assertTrue($res2, 'El resultado de la operación debió haber sido true.');
        static::assertEquals(
            'NewValue3',
            $store->get('Key3'),
            'El valor retornado no coincide.'
        );
        static::assertEquals(
            'NewValue4',
            $store->get('Key4'),
            'El valor retornado no coincide.'
        );
    }

    /**
     * Tests that Store's values can be set.
     *
     * @return void
     */
    public function testStoreCanSetValues(): void
    {
        // Performs test.
        $store = new Store(null);
        foreach ($this->dummy as $key => $value) {
            $store->add($key, $value);
        }

        // Sets 2 values in the Store, one using edit, another one using set.
        $res1 = $store->set('Key3', 'NewValue3');
        $res2 = $store->set('Key4', 'NewValue4');

        // Retrieves the 2 new values form the store.
        static::assertTrue($res1, 'El resultado de la operación debió haber sido true.');
        static::assertTrue($res2, 'El resultado de la operación debió haber sido true.');
        static::assertEquals(
            'NewValue3',
            $store->get('Key3'),
            'El valor retornado no coincide.'
        );
        static::assertEquals(
            'NewValue4',
            $store->get('Key4'),
            'El valor retornado no coincide.'
        );
    }

    /**
     * Test that elements can be removed from the Store.
     *
     * @return void
     */
    public function testValuesCanBeDeleted(): void
    {
        // Performs test.
        $store = new Store(null);
        foreach ($this->dummy as $key => $value) {
            $store->add($key, $value);
        }

        // Retrieves current values held by the Store.
        $oldValues = $store->getValues();

        // Remove 2 elements from the Store.
        $res1 = $store->delete('Key3');
        $res2 = $store->delete('Key4');

        // Performs assertions.
        static::assertTrue($res1, 'El resultado de la operación debió haber sido true.');
        static::assertTrue($res2, 'El resultado de la operación debió haber sido true.');

        // Retrieves new set of values held in the Store.
        $newValues = $store->getValues();

        // Performs assertions for the counts.
        static::assertCount(
            (count($oldValues) - 2),
            $newValues,
            'El nuevo elemento no coincide.'
        );
    }

    /**
     * Asserts Edit and Add methods do not succeed if concurrency rules break.
     *
     * @return void
     */
    public function testAddAndEditMethodsDoNotSuccessIfExistenceRuleBreaks(): void
    {
        // Performs test.
        $store = new Store('edit');
        foreach ($this->dummy as $key => $value) {
            $store->add($key, $value);
        }

        // Asserts non-existing values cannot be edited.
        $res1 = $store->edit('Key1000', 'Dummy');
        static::assertFalse($res1, 'El resultado de la operación debió haber sido false.');

        // Asserts values cannot be added twice.
        $res2 = $store->add('Key1', 'Dummy');
        static::assertFalse($res2, 'El resultado de la operación debió haber sido false.');
    }

    /**
     * Asserts Delete method does not succeed if concurrency rules break.
     *
     * @return void
     */
    public function testDeleteMethodDoesNotSucceedIfExistenceRuleBreaks(): void
    {
        // Performs test.
        $store = new Store('delete');
        foreach ($this->dummy as $key => $value) {
            $store->add($key, $value);
        }

        // Asserts nonexistent values cannot be deleted.
        $res1 = $store->delete('Key1000');
        static::assertFalse($res1, 'Operation result should have been FALSE.');
    }
}