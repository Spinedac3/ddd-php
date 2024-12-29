<?php

namespace Spineda\DddFoundation\Connections;

use Illuminate\Database\Capsule\Manager;
use Spineda\DddFoundation\Contracts\Connections\IsConnection;

/**
 * MySQL Connection configuration class
 *
 * @package Spineda\DddFoundation
 */
class MySQLConnection implements IsConnection
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
