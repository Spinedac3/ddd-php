<?php

namespace Spineda\DddFoundation\Connections;

use Illuminate\Database\Capsule\Manager;
use Spineda\DddFoundation\Contracts\Connections\IsConnection;

/**
 * SQL Server Connection Configuration class
 *
 * @package Spineda\DddFoundation
 */
class SQLServerConnection implements IsConnection
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
