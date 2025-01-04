<?php

namespace Spineda\DddFoundation\Repositories\Filesystem\File;

use Carbon\Carbon;
use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository as Contract;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\InvalidDateFormatException;
use Spineda\DddFoundation\ValueObjects\Collections\Filesystem\FileCollection;
use Spineda\DddFoundation\ValueObjects\File;
use Exception;

/**
 * Implements the FileRepository contract inside a provided file
 *
 * @package Spineda\DddFoundation
 */
class FileFilesRepository extends AbstractFileRepository implements Contract
{
    /**
     * Returns a permission pattern
     *
     * @return string
     */
    protected function getPatternPermission(): string
    {
        return '([r-][w-][x-])';
    }

    /**
     * Returns a user pattern
     *
     * @return string
     */
    protected function getPatternUser(): string
    {
        return '([a-z0-9A-Z-_][a-z0-9A-Z-_]*)';
    }

    /**
     * Returns a space pattern
     *
     * @return string
     */
    protected function getPatternSpace(): string
    {
        return '([ ]+)';
    }

    /**
     * Gets a time pattern
     *
     * @return string
     */
    protected function getPatternTime(): string
    {
        return '([0-9]|0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])';
    }

    protected function getPatternMonth(): string
    {
        return '(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)';
    }

    /**
     * Convert month from string to integer
     *
     * @param   string  $month
     *
     * @return  int
     * @throws  InvalidDateFormatException
     */
    protected function getMonthFromStr(string $month): int
    {
        $months = [
            'Jan' => 1,
            'Feb' => 2,
            'Mar' => 3,
            'Apr' => 4,
            'May' => 5,
            'Jun' => 6,
            'Jul' => 7,
            'Aug' => 8,
            'Sep' => 9,
            'Oct' => 10,
            'Nov' => 11,
            'Dec' => 12
        ];

        if (!isset($months[$month])) {
            throw new InvalidDateFormatException();
        }

        return $months[$month];
    }

    /**
     * Gets the pattern to parse an incoming file, using a variable format for year or time
     *
     * @param   bool  $useTimePattern  Use the time pattern - false uses the year pattern
     *
     * @return  string
     */
    protected function getPattern(bool $useTimePattern = true): string
    {
        $permissionPattern = $this->getPatternPermission();
        $userPattern = $this->getPatternUser();
        $spacePattern = $this->getPatternSpace();
        $monthPattern = $this->getPatternMonth();
        $dayPattern = '([0-9]{1,2})';

        $timePattern = $this->getPatternTime();
        $yearPattern = '([0-9]{4})';

        return '([d-])' .
            $permissionPattern . $permissionPattern . $permissionPattern . $spacePattern .
            '[1]' . $spacePattern .
            $userPattern . $spacePattern . $userPattern . $spacePattern .
            '([0-9]+)' . $spacePattern .
            $monthPattern . $spacePattern . $dayPattern . $spacePattern .
            ($useTimePattern ? $timePattern : $yearPattern) . $spacePattern .
            '(.+)';
    }

    /**
     * Gets a parsed file using the incoming variables
     *
     * @param string    $fileName
     * @param int       $month
     * @param int       $day
     * @param int|null  $year
     * @param int|null  $hour
     * @param int|null  $minute
     *
     * @return  File|null
     * @throws  Exception
     */
    protected function getParsedFile(
        string $fileName,
        int $month,
        int $day,
        ?int $year,
        ?int $hour,
        ?int $minute
    ): ?File {
        if ($fileName === '.' || $fileName === '..') {
            return null;
        }

        $year = $year ?? date('Y');
        $hour = $hour ?? 0;
        $minute = $minute ?? 0;

        $dateTime = Carbon::create($year, $month, $day, $hour, $minute);

        if ($dateTime->greaterThan(new Carbon())) {
            $dateTime = $dateTime->year($dateTime->year - 1);
        }

        return new File($fileName, $dateTime);
    }

    /**
     * Reads a line in the file - representing a file
     * @see https://cr.yp.to/ftp/list/binls.html
     *
     * @param   string  $line
     *
     * @return  File|null
     * @throws  Exception
     */
    protected function parseLine(string $line): ?File
    {
        if (preg_match('/^' . $this->getPattern() . '$/', $line, $matches)) {
            return $this->getParsedFile(
                $matches[20],
                $this->getMonthFromStr($matches[13]),
                $matches[15],
                null,
                $matches[17],
                $matches[18]
            );
        }

        if (preg_match('/^' . $this->getPattern(false) . '$/', $line, $matches)) {
            return $this->getParsedFile(
                $matches[19],
                $this->getMonthFromStr($matches[13]),
                $matches[15],
                $matches[17],
                null,
                null
            );
        }

        return null;
    }

    /**
     * {@inheritDoc}
     * @see Contract::get()
     *
     * @param   string  $name  File name
     *
     * @return  File
     * @throws  FileNotFoundException
     * @throws  Exception
     */
    public function get(string $name): File
    {
        $collection = $this->getFiles(
            function (File $file, array $params) {
                if ($file->getBaseName() === $params['name']) {
                    return true;
                }

                return false;
            },
            [ 'name' => $name ]
        );

        if (!$collection->count()) {
            throw new FileNotFoundException($name);
        }

        /** @var File */
        return $collection->rewind();
    }

    /**
     * Get a collection of files given a certain filter function
     *
     * @param   callable  $filterFunction  Function to use for filtering
     * @param   array     $parameters      Parameters in an assoc array
     *
     * @return  FileCollection
     * @throws  Exception
     */
    protected function getFiles(callable $filterFunction, array $parameters): FileCollection
    {
        $collection = new FileCollection();
        $file = fopen($this->file->getFullPath(), 'r');

        // Parses each line until the end of the file
        while (!feof($file)) {
            $line = fgets($file);
            $fileObject = $this->parseLine($line);

            if (null === $fileObject) {
                continue;
            }

            if (!$filterFunction($fileObject, $parameters)) {
                continue;
            }

            $collection->add($fileObject);
        }

        fclose($file);

        return $collection;
    }

    /**
     * {@inheritDoc}
     * @see Contract::listAll()
     *
     * @return  FileCollection
     * @throws  Exception
     */
    public function listAll(): FileCollection
    {
        return $this->getFiles(
            // Function to return every available file in the list
            function () {
                return true;
            },
            []
        );
    }

    /**
     * {@inheritDoc}
     * @see Contract::listModifiedAfter()
     *
     * @param   Carbon   $timestamp
     * @param   string   $extension  File extension (optional)
     *
     * @return  FileCollection
     * @throws  Exception
     */
    public function listModifiedAfter(Carbon $timestamp, string $extension = ''): FileCollection
    {
        return $this->getFiles(
            // Function to return files only if they are modified after the given timestamp
            function (File $file, array $params) {
                if ($file->getModifiedDateTime()->lte($params['timestamp'])) {
                    return false;
                }

                if (empty($params['extension'])) {
                    return true;
                }

                if ($file->getExtension() === $params['extension']) {
                    return true;
                }

                return false;
            },
            [ 'timestamp' => $timestamp, 'extension' => $extension ]
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param   File  $file
     *
     * @throws  DirectoryNotFoundException
     */
    public function dumpToFile(File $file): void
    {
        // Target directory does not exist
        if (!file_exists($file->getDirectory())) {
            throw new DirectoryNotFoundException($file->getDirectory());
        }

        // Implements a file copy
        copy($this->file->getFullPath(), $file->getFullPath());
    }
}
