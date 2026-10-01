<?php

namespace Spineda\DddFoundation\Contracts\Repositories\Shipping;

use InvalidArgumentException;
use Spineda\DddFoundation\Contracts\Repositories\AbstractRepositorySingleKey;
use Spineda\DddFoundation\Entities\Shipping\Driver;

/**
 * Contract for Driver repositories
 *
 * @package Spineda\DddFoundation
 */
interface DriverRepository extends AbstractRepositorySingleKey
{
    /**
     * Returns a Driver entity finding it by its id
     *
     * @param   int  $id  Driver id
     *
     * @return  Driver|null
     * @throws  InvalidArgumentException
     */
    public function findById(int $id): ?Driver;
}
