<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Data;

use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Data\SummaryInfo;
use JsonSerializable;

/**
 * Summary Information Datatype Unit testing.
 *
 * @package Spineda\DddFoundation\Tests
 */
class SummaryInfoTest extends AbstractUnitTest
{
    /**
     * Tests that the instance is loaded with the summary and the count.
     *
     * @return void
     */
    public function testLoadsCorrectly(): void
    {
        // Performs the test.
        $summaryInfo = new SummaryInfo(1250.75, 3);

        // Performs assertions.
        static::assertInstanceOf(JsonSerializable::class, $summaryInfo);
        static::assertSame(
            1250.75,
            $summaryInfo->getSummary(),
            'El resumen debió haber sido 1250.75.'
        );
        static::assertSame(
            3,
            $summaryInfo->getCount(),
            'El conteo debió haber sido 3.'
        );
    }

    /**
     * Tests the JSON serialization of the summary information.
     *
     * @return void
     */
    public function testJSONSerialization(): void
    {
        // Performs the test.
        $summaryInfo = new SummaryInfo(10.5, 2);

        // Performs assertions.
        static::assertEquals(
            '{"summary":10.5,"count":2}',
            json_encode($summaryInfo),
            'La serialización del resumen no coincide.'
        );
    }
}
