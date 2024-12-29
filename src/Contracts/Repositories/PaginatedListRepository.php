<?php

namespace Spineda\DddFoundation\Contracts\Repositories;

use Spineda\DddFoundation\ValueObjects\Collections\PaginatedEntityCollection;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;

/**
 * Contract for repositories listing paginated information
 *
 * @package Spineda\DddFoundation
 */
interface PaginatedListRepository
{
    /**
     * Retrieves paginated entity records from the system.
     *
     * Pagination information will be attached to retrieved Collection.
     *
     * @param   string        $orderBy   Field used in the record ordering of the Collection.
     * @param   int           $perPage   Listed items per page.
     * @param   int           $page      Page number to be retrieved.
     * @param   bool          $reversed  If the Field ordering of the Collection should be reversed.
     * @param   ?FiltersInfo  $filters   Filters for the pagination.
     *
     * @return PaginatedEntityCollection
     */
    public function listPaginated(
        string $orderBy,
        int $perPage,
        int $page = 1,
        bool $reversed = false,
        FiltersInfo $filters = null
    ): PaginatedEntityCollection;
}
