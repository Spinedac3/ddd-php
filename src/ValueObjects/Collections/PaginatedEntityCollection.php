<?php

namespace Spineda\DddFoundation\ValueObjects\Collections;

use Spineda\DddFoundation\ValueObjects\Data\SortInfo;
use JsonSerializable;

/**
 * Paginated aggregate Collection information object.
 *
 * This class is used when a Collection of records is paginated, and we'll need
 * to associated with the Collection all the pagination's information.
 *
 * The initialCollection of records being paginated will still be accessible through
 * the getCollection() method, but <b>any changes in this Collection will not be
 * reflected in the Pagination information counters.
 *
 * If no pagination information is required, please use regular Collection object.
 *
 * @package   @package Spineda\DddFoundation
 * @see       EntityCollection
 */
class PaginatedEntityCollection implements JsonSerializable
{
    /**
     * @var  EntityCollection|null  $collection
     * Collection of records being Paginated.
     */
    protected ?EntityCollection $collection = null;

    /**
     * @var  SortInfo|null  $sortInfo
     * Sorting information instance.
     */
    protected ?SortInfo $sortInfo = null;

    /**
     * @var   int  $total
     * Total number of records available.
     */
    protected int $total = 0;

    /**
     * @var   int|null  $firstId
     * Initial record being displayed. NULL if empty Collection.
     */
    protected ?int $firstId = null;

    /**
     * @var   int|null  $lastId
     * Last record being displayed. NULL if empty Collection.
     */
    protected ?int $lastId = null;

    /**
     * @var   int  $currentPage
     * Current Page for the Paginated records.
     */
    protected int $currentPage = 0;

    /**
     * @var   int  $lastPage
     * Last Page available for Pagination.
     */
    protected int $lastPage = 0;

    /**
     * @var   int  $perPage
     * Number of records being displayed per Page.
     */
    protected int $perPage = 0;

    /**
     * Initializes the Paginated Collection instance.
     *
     * @param   mixed             $collection   Collection of records being Paginated.
     * @param   SortInfo          $sortInfo     Sorting information instance.
     * @param   int               $total        Total number of records available.
     * @param   int               $currentPage  Current Page for the Paginated records.
     * @param   int               $lastPage     Last Page available for Pagination.
     * @param   int               $perPage      Number of records being displayed per Page.
     * @param   int|null          $from         Initial record's ID being displayed. NULL if empty Collection.
     * @param   int|null          $to           Last record's ID being displayed. NULL if empty Collection.
     *
     * @return void
     */
    public function __construct(
        EntityCollection $collection,
        SortInfo $sortInfo,
        int $total,
        int $currentPage,
        int $lastPage,
        int $perPage,
        int $from = null,
        int $to = null
    ) {
        // Sets the actual Collection of records being Paginated.
        $this->collection = $collection;
        $this->sortInfo   = $sortInfo;

        // Sets the counters.
        $this->total   = $total;
        $this->perPage = $perPage;

        // Sets the Page information.
        $this->currentPage = $currentPage;
        $this->lastPage    = $lastPage;
        $this->firstId     = $from;
        $this->lastId      = $to;
    }

    /**
     * Collection of items being paginated.
     *
     * Pagination counters will be calculated at start, and will remain immutable
     * until the object is destroyed.
     *
     * Please note that adding/removing elements to or from the Collection will
     * not reflect on the Pagination counters being updated.
     *
     * @return EntityCollection
     */
    public function getCollection(): EntityCollection
    {
        return $this->collection;
    }

    /**
     * Retrieves the sorting information for the Pagination.
     *
     * @return SortInfo
     */
    public function getSortInfo(): SortInfo
    {
        return $this->sortInfo;
    }

    /**
     * Number of the first record being displayed in the Paginated results.
     *
     * Please note that the returned integer is not the record's ID. It represents
     * the record's global displaying order if the records weren't being paginated.
     *
     * @return int
     */
    public function getFromRecord(): int
    {
        return (($this->currentPage - 1) * $this->perPage);
    }

    /**
     * Number of the last record being displayed in the Paginated results.
     *
     * Please note that the returned integer is not the record's ID. It represents
     * the record's global displaying order if the records weren't being paginated.
     *
     * @return int
     */
    public function getToRecord(): int
    {
        if ($this->collection->count() < $this->perPage) {
            return ($this->getFromRecord() + $this->collection->count());
        } else {
            return ($this->getFromRecord() + $this->perPage);
        }
    }

    /**
     * Number of the first record's ID being displayed in the Paginated results.
     *
     * If the record Collection is empty, this will return null, as
     * there isn't any first or last records.
     *
     * @return int|null
     */
    public function getFirstId(): ?int
    {
        return $this->firstId;
    }

    /**
     * Number of the last record's ID being displayed in the Paginated results.
     *
     * If the record Collection is empty, this will return null, as
     * there isn't any first or last records.
     *
     * @return int|null
     */
    public function getLastId(): ?int
    {
        return $this->lastId;
    }

    /**
     * Total number of records in source.
     *
     * Returns the total number of records from the source dataset, from which the pagination was produced.
     *
     * @return int
     */
    public function getTotalRecords(): int
    {
        return $this->total;
    }

    /**
     * Total number of available records for Pagination.
     *
     * Returns the total number of records in the selected Page.
     *
     * @return int
     */
    public function getTotal(): int
    {
        return $this->collection->count();
    }

    /**
     * Number of the current Page for the paginated records.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Number of the calculated last Page for the paginated records.
     *
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * Number of records being displayed by Page.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Returns an <code>Array</code> representation of the <i>Paginated Collection</i>'s object.
     *
     * @return array
     */
    public function getToArray(): array
    {
        return [
            'collection'   => $this->collection,
            'sortInfo'     => $this->sortInfo,
            'fromRecord'   => $this->getFromRecord(),
            'toRecord'     => $this->getToRecord(),
            'totalRecords' => $this->getTotalRecords(),
            'currentPage'  => $this->getCurrentPage(),
            'lastPage'     => $this->getLastPage(),
            'perPage'      => $this->getPerPage(),
        ];
    }

    /**
     * Returns an array containing all its item's serialized data.
     *
     * Allows the Collection to be serialized directly by the json_encode function.
     *
     * {@inheritDoc}
     * @link http://www.php.net/manual/en/jsonserializable.jsonserialize.php
     * @see  JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return [
            'collection'   => $this->collection->jsonSerialize(),
            'sortInfo'     => $this->sortInfo->jsonSerialize(),
            'fromRecord'   => $this->getFromRecord(),
            'toRecord'     => $this->getToRecord(),
            'totalRecords' => $this->getTotalRecords(),
            'currentPage'  => $this->getCurrentPage(),
            'lastPage'     => $this->getLastPage(),
            'perPage'      => $this->getPerPage(),
        ];
    }
}
