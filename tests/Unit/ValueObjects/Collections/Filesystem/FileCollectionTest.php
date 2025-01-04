<?php

namespace Spineda\DddFoundation\Tests\Unit\ValueObjects\Collections\Filesystem;

use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Spineda\DddFoundation\Tests\Unit\AbstractUnitTest;
use Spineda\DddFoundation\ValueObjects\Collections\Filesystem\FileCollection;
use Spineda\DddFoundation\ValueObjects\File;
use TypeError;
use stdClass;
use Exception;

/**
 * Tests for FileCollection class
 *
 * @package Spineda\DddFoundation\Tests
 */
class FileCollectionTest extends AbstractUnitTest
{
    /**
     * @var FileCollection
     */
    protected FileCollection $collection;

    /**
     * {@inheritDoc}
     * @see TestCase::setUp()
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new FileCollection();
    }

    /**
     * Creates a file stub (File ValueObject)
     *
     * @param   ?Carbon  $modifiedDateTime
     *
     * @return  File
     * @throws  Exception
     */
    protected function createFileStub(?Carbon $modifiedDateTime = null): File
    {
        /** @var File|MockObject $file */
        $file = $this->mockWithoutConstructor(File::class);

        // Mocks the modified date time of the file
        $file->method('getModifiedDateTime')
            ->willReturn($modifiedDateTime ?? new Carbon());

        return $file;
    }

    /**
     * Tests adding something that is not an File to the collection
     */
    public function testAddTypeError(): void
    {
        $this->expectException(TypeError::class);

        /** @var File $fakeEntity */
        $fakeEntity = new stdClass();
        $this->collection->add($fakeEntity);
    }

    /**
     * Tests successfully adding a File value object
     *
     * @throws  Exception
     */
    public function testAddSuccessful(): void
    {
        $file = $this->createFileStub();
        $this->collection->add($file);
        static::assertEquals(1, $this->collection->count());
    }

    /**
     * Tests the max modified date of a collection when it's null - no files
     */
    public function testMaxModifiedDateTimeNull(): void
    {
        static::assertNull($this->collection->getMaxModifiedDateTime());
    }

    /**
     * Test a successful max modified datetime comparison between two files
     *
     * @throws Exception
     */
    public function testMaxModifiedDateTimeSuccessful(): void
    {
        $file1 = $this->createFileStub(new Carbon('2019-11-27 08:00:00'));
        $file2 = $this->createFileStub(new Carbon('2019-11-26 08:00:00'));

        $this->collection->add($file1)
            ->add($file2);

        static::assertEquals(new Carbon('2019-11-27 08:00:00'), $this->collection->getMaxModifiedDateTime());
    }
}
