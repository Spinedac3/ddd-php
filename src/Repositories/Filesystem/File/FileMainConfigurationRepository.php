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
     * @var array|string[]
     */
    protected array $requiredKeys = [
        'username',
        'password',
        'database',
        'port',
        'host',
        'driver',
    ];

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
     * Validates the database configuration
     *
     * @return bool
     */
    public function validateDatabaseConfiguration(): bool
    {
        if (!isset($this->configuration['dbs']) || !is_array($this->configuration['dbs'])) {
            return false;
        }

        foreach ($this->configuration['dbs'] as $configuration) {
            foreach ($this->requiredKeys as $key) {
                if (!isset($configuration[$key])) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Validates the provided file with the expected connection name and other required parameters
     *
     * @return  bool
     * @throws  DirectoryNotFoundException
     */
    protected function validateConfiguration(): bool
    {
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

        return $this->validateDatabaseConfiguration();
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
