<?php

namespace Spineda\DddFoundation\Connections;

use Illuminate\Database\Capsule\Manager;

/**
 * SQL Server Connection Configuration class
 *
 * @package Spineda\DddFoundation
 */
class SQLServerConnection
{
    /**
     * Configure Capsule manager for connect database type
     *
     * @param Manager $manager Capsule Manager
     *
     * @return void
     */
    public function preConfigure(Manager $manager): void
    {
    }
}
