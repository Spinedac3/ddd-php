<?php

namespace Spineda\DddFoundation\Traits\Entities;

use Carbon\Carbon;

/**
 * Trait for the soft deletion properties of the entities
 *
 * @package Spineda\DddFoundation
 */
trait SoftDeletableEntity
{
    /**
     * @var  string|null
     */
    protected ?string $deleted_at = null;

    /**
     * @var  int|null
     */
    protected ?int $deleted_by = null;

    /**
     * Deleted at getter
     *
     * @return  Carbon|null
     */
    public function getDeletedAt(): ?Carbon
    {
        // Not deleted, early return
        if (null === $this->deleted_at) {
            return null;
        }

        return Carbon::parse($this->deleted_at);
    }

    /**
     * Deleted by getter
     *
     * @return  int|null
     */
    public function getDeletedBy(): ?int
    {
        return $this->deleted_by;
    }
}
