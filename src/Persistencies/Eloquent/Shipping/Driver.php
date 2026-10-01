<?php

namespace Spineda\DddFoundation\Persistencies\Eloquent\Shipping;

use Spineda\DddFoundation\Persistencies\Eloquent\AbstractModel;

/**
 * Driver model
 *
 * @package Spineda\DddFoundation
 */
class Driver extends AbstractModel
{
    /**
     * @var  string
     */
    protected $table = 'driver';

    /**
     * @var  string
     */
    protected $primaryKey = 'id';

    /**
     * @var  bool
     */
    public $timestamps = true;
}
