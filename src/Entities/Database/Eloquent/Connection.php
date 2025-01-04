<?php

namespace Spineda\DddFoundation\Entities\Database\Eloquent;

use Spineda\DddFoundation\Entities\AbstractEntity;

/**
 * Connection Entity
 *
 * @package Spineda\DddFoundation
 */
class Connection extends AbstractEntity
{
    /**
     * @var array
     */
    protected array $keyFields = [ 'name' ];

    /**
     * @var array
     */
    protected array $required = ['name', 'username', 'password', 'host', 'database', 'driver', 'port'];

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var string
     */
    protected string $username;

    /**
     * @var string
     */
    protected string $password;

    /**
     * @var string
     */
    protected string $host;

    /**
     * @var string
     */
    protected string $database;

    /**
     * @var string
     */
    protected string $driver;

    /**
     * @var string
     */
    protected string $port;

    /**
     * DB Connection name getter
     *
     * @return string
     */
    public function getConnectionName(): string
    {
        return $this->name;
    }

    /**
     * DB User name getter
     *
     * @return string
     */
    public function getUserName(): string
    {
        return $this->username;
    }

    /**
     * DB Password getter
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * DB Host getter
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * DB Name getter
     *
     * @return string
     */
    public function getDBName(): string
    {
        return $this->database;
    }

    /**
     * DB Driver getter
     *
     * @return string
     */
    public function getDBDriver(): string
    {
        return $this->driver;
    }

    /**
     * DB Port getter
     *
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }
}
