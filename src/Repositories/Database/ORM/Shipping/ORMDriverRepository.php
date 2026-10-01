<?php

namespace Spineda\DddFoundation\Repositories\Database\ORM\Shipping;

use InvalidArgumentException;
use Spineda\DddFoundation\Contracts\Repositories\Shipping\DriverRepository as Contract;
use Spineda\DddFoundation\Entities\Shipping\Driver;
use Spineda\DddFoundation\Exceptions\Shipping\Driver\DriverNotFoundException;
use Spineda\DddFoundation\Repositories\Database\ORM\ORMAbstractRepository;

/**
 * Driver repository
 *
 * @package Spineda\DddFoundation
 */
class ORMDriverRepository extends ORMAbstractRepository implements Contract
{
    /**
     * Returns a Driver entity from the repository
     *
     * @param   mixed  $key  Driver id
     *
     * @return  Driver
     * @throws  DriverNotFoundException
     */
    public function get(mixed $key): Driver
    {
        $entity = $this->findById($key);

        if (null === $entity) {
            throw new DriverNotFoundException();
        }

        return $entity;
    }

    /**
     * {@inheritDoc}
     * @see  Contract::findById()
     */
    public function findById(int $id): ?Driver
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('El identificador de conductor no es válido', 406);
        }

        $record = $this->model->newQuery()->find($id);

        // Not found, early return
        if (null === $record) {
            return null;
        }

        return new Driver($record->getAttributes());
    }
}
