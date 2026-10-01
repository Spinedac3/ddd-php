<?php

namespace Spineda\DddFoundation\Traits\Entities;

/**
 * Trait for the active property of the entities
 *
 * @package Spineda\DddFoundation
 */
trait ActivableEntity
{
    /**
     * @var  int
     */
    protected int $active;

    /**
     * Active getter
     *
     * @return  int
     */
    public function getActive(): int
    {
        return $this->active;
    }
}
