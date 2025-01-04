<?php

namespace Spineda\DddFoundation\Tests\Unit\Entities;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;

/**
 * Abstract class for testing entities
 *
 * @package Spineda\DddFoundation\Tests
 */
abstract class AbstractEntityUnitTest extends AbstractUnitTest
{
    /**
     * Dummy record for testing
     * @var array
     */
    protected array $dummy = [];

    /**
     * @var string
     */
    protected string $entityClass;

    /**
     * Tests the Entity's structure can be created successfully.
     *
     * @return void
     */
    public function testEntityHasExpectedStructure(): void
    {
        // Loads Entity object.
        $entity = new $this->entityClass($this->dummy);

        // Checks if the Entity implements state management by extending AbstractEntityS
        $this->assertInstanceOf(
            AbstractEntity::class,
            $entity,
            'La entidad creada debería extender a la clase AbstractEntity.'
        );
    }

    /**
     * Loads an entity without a certain field
     *
     * @param   string  $field
     *
     * @return void
     */
    protected function tryToLoadWithoutField(string $field): void
    {
        $dummy = $this->dummy;

        // Removes required field.
        unset($dummy[$field]);

        // Performs test.
        new $this->entityClass($dummy);
    }
}
