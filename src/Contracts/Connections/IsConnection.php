<?php

namespace Spineda\DddFoundation\Contracts\Connections;

use Illuminate\Database\Capsule\Manager;

/**
 * Interface for Connections
 *
 * @package Spineda\DddFoundation
 */
interface IsConnection
{
    /**
     * Configure Capsule manager for connect database type
     *
     * @param Manager $capsule Capsule manager
     *
     * @return void
     */
    public function preConfigure(Manager $capsule): void;
}
