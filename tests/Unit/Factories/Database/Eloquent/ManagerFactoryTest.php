<?php

namespace Spineda\DddFoundation\Tests\Unit\Factories\Database\Eloquent;

use Illuminate\Database\Capsule\Manager;
use Mockery\MockInterface;
use Spineda\DddFoundation\Connections\MySQLConnection;
use Spineda\DddFoundation\Connections\SQLServerConnection;
use Spineda\DddFoundation\Contracts\IsFactory;
use Spineda\DddFoundation\Factories\Database\Eloquent\ManagerFactory;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use ReflectionException;
use ReflectionClass;

/**
 * Test for ManagerFactory class
 *
 * @package Spineda\DddFoundation\Tests
 */
class ManagerFactoryTest extends AbstractUnitTest
{
    /**
     * @var string
     */
    protected string $factoryClassName = ManagerFactory::class;

    /**
     * @var string
     */
    protected string $producedClassName = Manager::class;

    /**
     * @var string
     */
    protected string $producedPropertyName = 'manager';

    /**
     * @var string
     */
    protected string $getMethod = 'get';

    /**
     * @var string
     */
    protected string $createMethod = 'create';

    /**
     * @var MockInterface $mysqlConnection
     */
    protected MockInterface $mysqlConnection;

    /**
     * @var MockInterface $sqlServerConnection
     */
    protected MockInterface $sqlServerConnection;

    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     * @see AbstractUnitTest::setUp()
     */
    public function setUp(): void
    {
        // Initializes parent class.
        parent::setUp();

        // Mock Dependencies
        $this->mysqlConnection = $this->mock(MySQLConnection::class);
        $this->sqlServerConnection = $this->mock(SQLServerConnection::class);

        // Resets the internal property.
        $this->resetProperty();
    }

    /**
     * {@inheritDoc}
     *
     * @throws ReflectionException
     * @see AbstractUnitTest::tearDown()
     */
    protected function tearDown(): void
    {
        // Resets the internal property.
        $this->resetProperty();

        // Tears down parent class.
        parent::tearDown();
    }

    /**
     * Resets the private property's value.
     *
     * Being a private static property, we'll need to change its visibility
     * before actually changing its value.
     *
     * @return void
     * @throws ReflectionException
     */
    private function resetProperty(): void
    {
        // Reflects Factory object.
        $reflection = new ReflectionClass($this->factoryClassName);
        $property = $reflection->getProperty($this->producedPropertyName);
        $property->setValue(null);
    }

    /**
     * Tests returns the same object for a created instance
     *
     * @return void
     */
    public function testReturnTheSameObjectForACreatedMySQLInstance(): void
    {
        /** @var IsFactory|string $propertyName */
        $propertyName = $this->factoryClassName;

        $getMethod = $this->getMethod;
        $createMethod = $this->createMethod;

        // mock preConfigure method
        $this->mysqlConnection->shouldReceive('preConfigure');

        // Retrieves two new base objects.
        $repo1 = $propertyName::$getMethod($this->mysqlConnection, 'dummy', true);
        $repo2 = $propertyName::$createMethod($this->mysqlConnection, 'dummy', false);

        // Asserts that the correct class was retrieved.
        static::assertInstanceOf(
            $this->producedClassName,
            $repo1,
            'Debió haber regresado una instancia de ' . basename($this->producedClassName)
        );
        static::assertInstanceOf(
            $this->producedClassName,
            $repo2,
            'Debió haber regresado una instancia de ' . basename($this->producedClassName)
        );
        static::assertSame(
            $repo1,
            $repo2,
            'Las instancias de ' . basename($this->producedClassName) . ' deben ser iguales'
        );
    }

    /**
     * Tests returns the same object for a created instance
     *
     * @return void
     */
    public function testReturnTheSameObjectForACreatedSQLServerInstance(): void
    {
        /** @var IsFactory|string $propertyName */
        $propertyName = $this->factoryClassName;

        $getMethod = $this->getMethod;
        $createMethod = $this->createMethod;

        // mock preConfigure method
        $this->sqlServerConnection->shouldReceive('preConfigure');

        // Retrieves two new base objects.
        $repo1 = $propertyName::$getMethod($this->sqlServerConnection, 'dummy', true);
        $repo2 = $propertyName::$createMethod($this->sqlServerConnection, 'dummy', false);

        // Asserts that the correct class was retrieved.
        static::assertInstanceOf(
            $this->producedClassName,
            $repo1,
            'Debió haber regresado una instancia de ' . basename($this->producedClassName)
        );
        static::assertInstanceOf(
            $this->producedClassName,
            $repo2,
            'Debió haber regresado una instancia de ' . basename($this->producedClassName)
        );
        static::assertSame(
            $repo1,
            $repo2,
            'Las instancias de ' . basename($this->producedClassName) . ' deben ser iguales'
        );
    }

    /**
     * Tests returns the same object for a created instance
     *
     * @return void
     */
    public function testReturnTheSameObjectForACreatedMultiplesInstance(): void
    {
        /** @var IsFactory|string $propertyName */
        $propertyName = $this->factoryClassName;

        $getMethod = $this->getMethod;

        // mock preConfigure method
        $this->mysqlConnection->shouldReceive('preConfigure');
        $this->sqlServerConnection->shouldReceive("preConfigure");

        // Retrieves two new base objects.
        $repo1 = $propertyName::$getMethod($this->mysqlConnection, 'dummy', true);
        $repo3 = $propertyName::$getMethod($this->sqlServerConnection, 'dummy', false);

        // Asserts that the correct class was retrieved.
        static::assertInstanceOf(
            $this->producedClassName,
            $repo1,
            'Debió haber regresado una instancia de ' . basename($this->producedClassName)
        );
        static::assertInstanceOf(
            $this->producedClassName,
            $repo3,
            'Debió haber regresado una instancia de ' . basename($this->producedClassName)
        );
        static::assertSame(
            $repo1,
            $repo3,
            'Las instancias de ' . basename($this->producedClassName) . ' deben ser iguales'
        );
    }
}
