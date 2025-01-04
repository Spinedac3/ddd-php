<?php

namespace Spineda\DddFoundation\Repositories\Filesystem\File;

use Spineda\DddFoundation\Contracts\Repositories\System\MainConfigurationRepository as Contract;
use Spineda\DddFoundation\Entities\Database\Eloquent\Connection;
use Spineda\DddFoundation\Exceptions\Filesystem\DirectoryNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\FileNotFoundException;
use Spineda\DddFoundation\Exceptions\Filesystem\InvalidConfigurationFileException;
use Spineda\DddFoundation\ValueObjects\File;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Main configuration class when given in a file
 *
 * @package Spineda\DddFoundation
 */
class FileMainConfigurationRepository extends AbstractFileRepository implements Contract
{
    /**
     * @var array $configuration
     */
    protected array $configuration;

    /**
     * FileMainConfigurationRepository constructor.
     *
     * @param  File    $file    File configuration
     *
     * @throws FileNotFoundException
     * @throws InvalidConfigurationFileException
     * @throws DirectoryNotFoundException
     */
    public function __construct(File $file)
    {
        parent::__construct($file);

        // Tries to parse the configuration
        try {
            $this->configuration = Yaml::parse(file_get_contents($this->file->getFullPath()));
        } catch (ParseException $exception) {
            throw new InvalidConfigurationFileException($exception->getMessage());
        }

        if (!$this->validateConfiguration()) {
            throw new InvalidConfigurationFileException(
                'El archivo de configuración no posee todos los datos obligatorios'
            );
        }
    }

    /**
     * Validates the provided file with the expected connection name and other required parameters
     *
     * @return  bool
     * @throws  DirectoryNotFoundException
     */
    protected function validateConfiguration(): bool
    {
        if (!isset($this->configuration['dbs']) || !is_array($this->configuration['dbs'])) {
            return false;
        }

        foreach ($this->configuration['dbs'] as $configuration) {
            if (
                !isset($configuration['username'])
                || !isset($configuration['password'])
                || !isset($configuration['database'])
                || !isset($configuration['port'])
                || !isset($configuration['host'])
                || !isset($configuration['driver'])
            ) {
                return false;
            }
        }

        if (
            !isset($this->configuration['tmpfolder'])
            || !isset($this->configuration['timezone'])
        ) {
            return false;
        }

        $tmpFolder = getcwd() . '/' . $this->configuration['tmpfolder'];

        if (!file_exists($tmpFolder)) {
            throw new DirectoryNotFoundException($tmpFolder);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @return Connection
     */
    public function getConnection(string $connectionName): Connection
    {
        return new Connection($this->configuration['dbs'][$connectionName]);
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getTmpFolder(): string
    {
        return $this->configuration['tmpfolder'];
    }

    /**
     * {@inheritDoc}
     *
     * @return string
     */
    public function getTimeZone(): string
    {
        return $this->configuration['timezone'];
    }
}
