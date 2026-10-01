<?php

namespace Spineda\DddFoundation\Traits\Entities;

/**
 * Trait for the common properties of the entities
 *
 * @package Spineda\DddFoundation
 */
trait CommonEntity
{
    /**
     * @var  int
     */
    protected int $id;

    /**
     * Id getter
     *
     * @return  int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
