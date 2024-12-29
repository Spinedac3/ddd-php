<?php

namespace Spineda\DddFoundation\ValueObjects\Data;

use JsonSerializable;

/**
 * Join Info class.
 *
 * Class used for building queries on related tables
 *
 * @package Spineda\DddFoundation
 */
class JoinInfo implements JsonSerializable
{
    /**
     * @var string
     * Table 1 the join condition
     */
    protected string $table1;

    /**
     * @var string
     * Table 2 the join condition
     */
    protected string $table2;

    /**
     * @var string
     * Join Field condition table 1
     */
    protected string $joinFieldTable1;

    /**
     * @var string
     * Join Field condition table 2
     */
    protected string $joinFieldTable2;

    /**
     * @var FiltersInfo
     * Filters Apply to join table
     */
    protected FiltersInfo $filtersFirstTable;

    /**
     * Creates a new Sorting Information instance.
     *
     * @param  string      $table1             - Table 1 the join condition
     * @param  string      $table2             - Table 2 the join condition
     * @param  string      $joinFieldTable1    - Join Field condition table 1
     * @param  string      $joinFieldTable2    - Join Field condition table 2
     * @param  FiltersInfo $filtersFirstTable  - Filters Apply to join table
     *
     * @return void
     */
    public function __construct(
        string $table1,
        string $table2,
        string $joinFieldTable1,
        string $joinFieldTable2,
        FiltersInfo $filtersFirstTable
    ) {
        $this->table1 = $table1;
        $this->table2 = $table2;
        $this->joinFieldTable1 = $joinFieldTable1;
        $this->joinFieldTable2 = $joinFieldTable2;
        $this->filtersFirstTable = $filtersFirstTable;
    }

    /**
     * Returns the table1 value.
     *
     * @return string
     */
    public function getTable1(): string
    {
        return $this->table1;
    }

    /**
     * Returns the table2 value.
     *
     * @return string
     */
    public function getTable2(): string
    {
        return $this->table2;
    }

    /**
     * Returns the joinFieldTable2 value.
     *
     * @return string
     */
    public function getJoinFieldTable1(): string
    {
        return $this->joinFieldTable1;
    }

    /**
     * Returns the joinFieldTable2 value.
     *
     * @return string
     */
    public function getJoinFieldTable2(): string
    {
        return $this->joinFieldTable2;
    }

    /**
     * Returns the filtersFirstTable value.
     *
     * @return FiltersInfo
     */
    public function getFiltersFirstTable(): FiltersInfo
    {
        return $this->filtersFirstTable;
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
            'table1'            => $this->table1,
            'table2'            => $this->table2,
            'joinFieldTable1'   => $this->joinFieldTable1,
            'joinFieldTable2'   => $this->joinFieldTable2,
            'filtersFirstTable' => $this->filtersFirstTable
        ];
    }
}
