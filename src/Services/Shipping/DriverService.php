<?php

namespace Spineda\DddFoundation\Services\Shipping;

use Spineda\DddFoundation\Contracts\IsService;
use Spineda\DddFoundation\Contracts\Repositories\Shipping\DriverRepository;
use Spineda\DddFoundation\Entities\Shipping\Driver;
use Spineda\DddFoundation\Exceptions\Shipping\Driver\DriverNotFoundException;

/**
 * Domain service for drivers
 *
 * @package Spineda\DddFoundation
 */
class DriverService implements IsService
{
    /**
     * @var  DriverRepository
     */
    protected DriverRepository $driverRepository;

    /**
     * Constructor of Driver service
     *
     * @param   DriverRepository  $driverRepository  Driver repository implementation
     */
    public function __construct(DriverRepository $driverRepository)
    {
        $this->driverRepository = $driverRepository;
    }

    /**
     * Gets a driver by its id
     *
     * @param   int  $id  Driver id
     *
     * @return  Driver
     * @throws  DriverNotFoundException
     */
    public function get(int $id): Driver
    {
        return $this->driverRepository->get($id);
    }
}
