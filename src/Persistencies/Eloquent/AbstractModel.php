<?php

namespace Spineda\DddFoundation\Persistencies\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Spineda\DddFoundation\Contracts\IsModel;

/**
 * Abstract model class
 *
 * @package Spineda\DddFoundation
 */
abstract class AbstractModel extends Model implements IsModel
{
    /**
     * timestamps for any model
     *
     * @var bool
     */
    public $timestamps = true;
}
