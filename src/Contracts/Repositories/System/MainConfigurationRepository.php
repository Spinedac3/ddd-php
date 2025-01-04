<?php

namespace Spineda\DddFoundation\Contracts\Repositories\System;

use Spineda\DddFoundation\Entities\Database\Eloquent\Connection;

/**
 * Contract for the main configuration
 *
 * @package Proaktiv
 */
interface MainConfigurationRepository
{
    /**
     * Connection Entity getter by connection name identifier
     *
     * @param  string    $connectionName    Connection name identifier
     *
     * @return Connection
     */
    public function getConnection(string $connectionName): Connection;

    /**
     * Temp folder getter
     *
     * @return string
     */
    public function getTmpFolder(): string;

    /**
     * Time zone getter
     *
     * @return string
     */
    public function getTimeZone(): string;
}
