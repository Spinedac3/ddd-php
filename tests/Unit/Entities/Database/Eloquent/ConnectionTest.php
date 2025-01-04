<?php

namespace Entities\Database\Eloquent;

use Spineda\DddFoundation\Entities\Database\Eloquent\Connection;
use Spineda\DddFoundation\Tests\Unit\Entities\AbstractEntityUnitTest;
use UnderflowException;

/**
 * Connection entity test class
 *
 * @package Spineda\DddFoundation\Tests
 */
class ConnectionTest extends AbstractEntityUnitTest
{
    /**
     * @var array
     */
    protected array $dummy = [
        'name'           => 'testConnection',
        'username'       => 'testUser',
        'password'       => 'testPassword',
        'host'           => 'testHost',
        'database'       => 'testDatabase',
        'driver'         => 'testDriver',
        'port'           => 'testPort',
    ];

    /**
     * @var string
     */
    protected string $entityClass = Connection::class;

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutName(): void
    {
        static::expectException(UnderflowException::class);
        static::tryToLoadWithoutField('name');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutUsername(): void
    {
        static::expectException(UnderflowException::class);
        static::tryToLoadWithoutField('username');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutPassword(): void
    {
        static::expectException(UnderflowException::class);
        static::tryToLoadWithoutField('password');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutHost(): void
    {
        static::expectException(UnderflowException::class);
        static::tryToLoadWithoutField('host');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutDatabase(): void
    {
        static::expectException(UnderflowException::class);
        static::tryToLoadWithoutField('database');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutDriver(): void
    {
        static::expectException(UnderflowException::class);
        static::tryToLoadWithoutField('driver');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutPort(): void
    {
        static::expectException(UnderflowException::class);
        static::tryToLoadWithoutField('port');
    }

    /**
     * Test entity getter properties
     */
    public function testEntityGetProperties()
    {
        //setup entity
        /** @var Connection $entity */
        $entity = new $this->entityClass($this->dummy);

        //test getter methods
        static::assertEquals($this->dummy['name'], $entity->getConnectionName());
        static::assertEquals($this->dummy['username'], $entity->getUsername());
        static::assertEquals($this->dummy['password'], $entity->getPassword());
        static::assertEquals($this->dummy['host'], $entity->getHost());
        static::assertEquals($this->dummy['database'], $entity->getDBName());
        static::assertEquals($this->dummy['driver'], $entity->getDBDriver());
        static::assertEquals($this->dummy['port'], $entity->getPort());
    }
}
