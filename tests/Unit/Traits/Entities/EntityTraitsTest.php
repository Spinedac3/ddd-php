<?php

namespace Spineda\DddFoundation\Tests\Unit\Traits\Entities;

use Carbon\Carbon;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\Tests\Unit\Stubs\ConcreteEntityWithTraits;

/**
 * Tests for the traits used by the entities
 *
 * @package Spineda\DddFoundation\Tests
 */
class EntityTraitsTest extends AbstractUnitTest
{
    /**
     * Tests the getters of the common, activable and auditable traits.
     */
    public function testCommonActivableAndAuditableGetters(): void
    {
        // Performs the test.
        $entity = new ConcreteEntityWithTraits([
            'id'         => 15,
            'active'     => 1,
            'created_at' => '2024-03-10 08:30:00',
            'created_by' => 7,
            'updated_at' => '2024-03-11 09:45:00',
            'updated_by' => 9,
        ]);

        // Performs assertions.
        static::assertSame(15, $entity->getId(), 'El id debió haber sido 15.');
        static::assertSame(1, $entity->getActive(), 'El activo debió haber sido 1.');
        static::assertSame(7, $entity->getCreatedBy(), 'El usuario creador debió haber sido 7.');
        static::assertSame(9, $entity->getUpdatedBy(), 'El usuario que actualizó debió haber sido 9.');
        static::assertInstanceOf(Carbon::class, $entity->getCreatedAt());
        static::assertSame(
            '2024-03-10 08:30:00',
            $entity->getCreatedAt()->format('Y-m-d H:i:s'),
            'La fecha de creación no coincide.'
        );
        static::assertSame(
            '2024-03-11 09:45:00',
            $entity->getUpdatedAt()->format('Y-m-d H:i:s'),
            'La fecha de actualización no coincide.'
        );
    }

    /**
     * Tests that the soft deletable getters return null when the record has not been deleted.
     */
    public function testSoftDeletableGettersWhenNotDeleted(): void
    {
        // Performs the test.
        $entity = new ConcreteEntityWithTraits(['id' => 15]);

        // Performs assertions.
        static::assertNull($entity->getDeletedAt(), 'La fecha de eliminación debió haber sido null.');
        static::assertNull($entity->getDeletedBy(), 'El usuario que eliminó debió haber sido null.');
    }

    /**
     * Tests the soft deletable getters when the record has been deleted.
     */
    public function testSoftDeletableGettersWhenDeleted(): void
    {
        // Performs the test.
        $entity = new ConcreteEntityWithTraits([
            'id'         => 15,
            'deleted_at' => '2024-04-01 10:00:00',
            'deleted_by' => 3,
        ]);

        // Performs assertions.
        static::assertSame(
            '2024-04-01 10:00:00',
            $entity->getDeletedAt()->format('Y-m-d H:i:s'),
            'La fecha de eliminación no coincide.'
        );
        static::assertSame(3, $entity->getDeletedBy(), 'El usuario que eliminó debió haber sido 3.');
    }
}
