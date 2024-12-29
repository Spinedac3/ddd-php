<?php

namespace Spineda\DddFoundation\Contracts\ValueObjects\Collections;

use JsonSerializable;

/**
 * Contract for collectable objects
 * It extends JSONSerializable to include this dependency
 *
 * @package Spineda\DddFoundation
 */
interface IsCollectable extends JsonSerializable
{
    /**
     * Gets the value of a specific field.
     *
     * @param string $field The field to retrieve.
     *
     * @return mixed
     */
    public function getField(string $field): mixed;
}
