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
}
