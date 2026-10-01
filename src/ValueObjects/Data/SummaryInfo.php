<?php

namespace Spineda\DddFoundation\ValueObjects\Data;

use JsonSerializable;

/**
 * Summary Information class.
 *
 * This class holds the result of summarizing a field over a set of records:
 * the summarized value and the number of records it was calculated from.
 *
 * @package Spineda\DddFoundation
 */
class SummaryInfo implements JsonSerializable
{
    /**
     * @var   float  $summary
     * Value of the summary.
     */
    protected float $summary;

    /**
     * @var   int  $count
     * Number of records summarized.
     */
    protected int $count;

    /**
     * Creates a new Summary Information instance.
     *
     * @param   float  $summary  Value of the summary.
     * @param   int    $count    Number of records summarized.
     *
     * @return void
     */
    public function __construct(float $summary, int $count)
    {
        $this->summary = $summary;
        $this->count   = $count;
    }

    /**
     * Retrieves the summary information
     *
     * @return float
     */
    public function getSummary(): float
    {
        return $this->summary;
    }

    /**
     * Retrieves the count information
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * Returns an array containing all its item's serialized data.
     *
     * {@inheritDoc}
     * @link http://www.php.net/manual/en/jsonserializable.jsonserialize.php
     * @see  JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return [
            'summary' => $this->summary,
            'count'   => $this->count,
        ];
    }
}
