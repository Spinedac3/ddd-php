<?php

namespace Spineda\DddFoundation\Tests;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Mockery\MockInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Mockery;
use ReflectionClass;
use ReflectionException;
use stdClass;

/**
 * Base class for all Library tests
 *
 * @package Spineda\DddFoundation\Tests
 */
abstract class AbstractTest extends TestCase
{
    use ArraySubsetAsserts;

    /**
     * Mocks the specified class.
     * Wrapper method for Mockery::mock().
     *
     * @param string $class - Class to be Mocked.
     *
     * @return MockInterface
     */
    protected function mock(string $class): MockInterface
    {
        return Mockery::mock($class);
    }

    /**
     * Mocks the specified class, disabling its constructor
     *
     * @param string $class
     *
     * @return  MockObject
     */
    protected function mockWithoutConstructor(string $class): MockObject
    {
        return $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Calls a protected method of a function, using reflection
     *
     * @param string $class
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     *
     * @return  mixed
     * @throws ReflectionException
     */
    protected function callProtectedMethod(string $class, object $object, string $methodName, array $parameters): mixed
    {
        $reflection = new ReflectionClass($class);
        $method = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Sets up a property field (private or protected) of an object of a certain class, using Reflection
     *
     * @param object $object
     * @param ReflectionClass $reflection
     * @param string $propertyName
     * @param mixed $propertyValue
     *
     * @return  void
     * @throws ReflectionException
     */
    protected function setUpEntityProperty(
        object $object,
        ReflectionClass $reflection,
        string $propertyName,
        mixed $propertyValue
    ): void {
        $requiredProperty = $reflection->getProperty($propertyName);
        $requiredProperty->setValue($object, $propertyValue);
    }

    /**
     * Gets a protected property of an object
     *
     * @param object $object Object
     * @param string $property Property name
     *
     * @return  mixed
     * @throws  ReflectionException
     */
    protected function getProtectedProperty(object $object, string $property): mixed
    {
        $reflection = new ReflectionClass($object);
        $theProperty = $reflection->getProperty($property);

        return $theProperty->getValue($object);
    }

    /**
     * Setup methods required to mock an iterator
     *
     * @param   MockObject  $iteratorMock  The mock to attach the iterator methods to
     * @param   array       $items         The mock data we're going to use with the iterator
     *
     * @return  MockObject  The iterator mock
     */
    public function mockIterator(MockObject $iteratorMock, array $items): MockObject
    {
        $iteratorData = new stdClass();
        $iteratorData->array = $items;
        $iteratorData->position = 0;

        $iteratorMock->expects($this->any())
            ->method('rewind')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position = 0;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('current')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->array[$iteratorData->position];
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('key')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return $iteratorData->position;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('next')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        $iteratorData->position++;
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('valid')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return isset($iteratorData->array[$iteratorData->position]);
                    }
                )
            );

        $iteratorMock->expects($this->any())
            ->method('count')
            ->will(
                $this->returnCallback(
                    function () use ($iteratorData) {
                        return sizeof($iteratorData->array);
                    }
                )
            );

        return $iteratorMock;
    }

    /**
     * Closes Mockery, if needed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
