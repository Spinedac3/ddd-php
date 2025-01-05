<?php

namespace Spineda\DddFoundation\Repositories\Filesystem\Directory;

use Carbon\Carbon;
use Spineda\DddFoundation\Contracts\Repositories\Filesystem\FilesRepository as Contract;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\ValueObjects\Collections\Filesystem\FileCollection;
use Spineda\DddFoundation\ValueObjects\File;
use Exception;

/**
 * Files repository in a certain directory
 *
 * @package Spineda\DddFoundation
 */
class DirectoryFilesRepository implements Contract
{
    /**
     * @var string
     */
    protected string $directory;

    /**
     * Constructor with a certain directory
     *
     * @param   string  $directory
     *
     * @throws  DirectoryNotFoundException
     */
    public function __construct(string $directory)
    {
        if (!file_exists($directory)) {
            throw new DirectoryNotFoundException($directory);
        }

        $this->directory = $directory;
    }

    /**
     * Validates if the file exists
     *
     * @param   string  $file
     *
     * @throws  FileNotFoundException
     */
    private function validateFileExists(string $file): void
    {
        // The file does not exist
        if (!file_exists($file)) {
            throw new FileNotFoundException($file);
        }
    }

    /**
     * Validates if the file should be skipped
     *
     * @param string $file
     *
     * @return bool
     */
    private function shouldSkipFile(string $file): bool
    {
        // validate if the file is empty or is a directory file
        return empty($file) || in_array(basename($file), ['.', '..'], true);
    }

    /**
     * Converts an array of files into an array of File (ValueObject)
     *
     * @param   array  $files
     *
     * @return  FileCollection
     * @throws  FileNotFoundException
     * @throws  Exception
     */
    protected function enrichFiles(array $files): FileCollection
    {
        $fileArray = new FileCollection();

        // Empty directory
        if (empty($files)) {
            return $fileArray;
        }

        foreach ($files as $file) {
            // Skips empty lines and directory files
            if ($this->shouldSkipFile($file)) {
                continue;
            }

            // The file does not exist
            $this->validateFileExists($file);

            $fileArray->add(new File($file, new Carbon(filemtime($file))));
        }

        return $fileArray;
    }

    /**
     * {@inheritDoc}
     *
     * @param   string   $name
     *
     * @return  File
     * @throws  FileNotFoundException
     * @throws  Exception
     */
    public function get(string $name): File
    {
        $fileName = $this->directory . '/' . $name;

        if (!file_exists($fileName)) {
            throw new FileNotFoundException($name);
        }

        return new File($fileName, new Carbon(filemtime($fileName)));
    }

    /**
     * {@inheritDoc}
     *
     * @return  FileCollection
     * @throws  FileNotFoundException
     */
    public function listAll(): FileCollection
    {
        $files = glob($this->directory . '/*');
        return $this->enrichFiles($files);
    }

    /**
     * {@inheritDoc}
     *
     * @param   Carbon  $timestamp
     * @param   string  $extension  File extension (optional)
     *
     * @return  FileCollection
     * @throws  FileNotFoundException
     */
    public function listModifiedAfter(Carbon $timestamp, string $extension = ''): FileCollection
    {
        // Optional extension in the find command
        $name = empty($extension)
            ? ''
            : ' -name *.' . $extension;

        // Uses the Linux find command to get the files
        $files = explode(
            chr(10),
            shell_exec(
                'find ' . $this->directory . '/. -mmin -' . $timestamp->diffInMinutes(new Carbon()) . $name
            )
        );

        return $this->enrichFiles($files);
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
        // The target directory does not exist
        if (!file_exists($file->getDirectory())) {
            throw new DirectoryNotFoundException($file->getDirectory());
        }

        // Exports with ls
        shell_exec('ls -la ' . $this->directory . ' > ' . $file->getFullPath());
    }
}
