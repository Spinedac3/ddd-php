<?php

namespace Spineda\DddFoundation\Entities\Shipping;

use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Traits\Entities\AuditableEntity;
use Spineda\DddFoundation\Traits\Entities\CommonEntity;
use Spineda\DddFoundation\Traits\Entities\SoftDeletableEntity;

/**
 * Shipping Driver entity
 *
 * @package Spineda\DddFoundation
 */
class Driver extends AbstractEntity
{
    use CommonEntity;
    use AuditableEntity;
    use SoftDeletableEntity;

    /**
     * @var  array
     */
    protected array $keyFields = ['id'];

    /**
     * @var  array
     */
    protected array $required = ['id', 'code'];

    /**
     * @var  string
     */
    protected string $code;

    /**
     * @var  string|null
     */
    protected ?string $first_name = null;

    /**
     * @var  string|null
     */
    protected ?string $last_name = null;

    /**
     * Code getter
     *
     * @return  string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * First name getter
     *
     * @return  string|null
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * Last name getter
     *
     * @return  string|null
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }
}
