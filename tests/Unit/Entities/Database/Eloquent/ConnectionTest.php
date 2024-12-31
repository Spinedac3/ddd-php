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
        $this->expectException(UnderflowException::class);
        $this->tryToLoadWithoutField('name');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutUsername(): void
    {
        $this->expectException(UnderflowException::class);
        $this->tryToLoadWithoutField('username');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutPassword(): void
    {
        $this->expectException(UnderflowException::class);
        $this->tryToLoadWithoutField('password');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutHost(): void
    {
        $this->expectException(UnderflowException::class);
        $this->tryToLoadWithoutField('host');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutDatabase(): void
    {
        $this->expectException(UnderflowException::class);
        $this->tryToLoadWithoutField('database');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutDriver(): void
    {
        $this->expectException(UnderflowException::class);
        $this->tryToLoadWithoutField('driver');
    }

    /**
     * Tries to load the entity without required field
     */
    public function testEntityLoadingBreaksWithoutPort(): void
    {
        $this->expectException(UnderflowException::class);
        $this->tryToLoadWithoutField('port');
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
        $this->assertEquals($this->dummy['name'], $entity->getConnectionName());
        $this->assertEquals($this->dummy['username'], $entity->getUsername());
        $this->assertEquals($this->dummy['password'], $entity->getPassword());
        $this->assertEquals($this->dummy['host'], $entity->getHost());
        $this->assertEquals($this->dummy['database'], $entity->getDBName());
        $this->assertEquals($this->dummy['driver'], $entity->getDBDriver());
        $this->assertEquals($this->dummy['port'], $entity->getPort());
    }
}
