<?php

namespace Spineda\DddFoundation\Traits\Entities;

use Carbon\Carbon;

/**
 * Trait for the auditable properties of the entities
 *
 * @package Spineda\DddFoundation
 */
trait AuditableEntity
{
    /**
     * @var  string
     */
    protected string $created_at;

    /**
     * @var  int
     */
    protected int $created_by;

    /**
     * @var  string
     */
    protected string $updated_at;

    /**
     * @var  int
     */
    protected int $updated_by;

    /**
     * Created at getter
     *
     * @return  Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return Carbon::parse($this->created_at);
    }

    /**
     * Created by getter
     *
     * @return  int
     */
    public function getCreatedBy(): int
    {
        return $this->created_by;
    }

    /**
     * Updated at getter
     *
     * @return  Carbon
     */
    public function getUpdatedAt(): Carbon
    {
        return Carbon::parse($this->updated_at);
    }

    /**
     * Updated by getter
     *
     * @return  int
     */
    public function getUpdatedBy(): int
    {
        return $this->updated_by;
    }
}
