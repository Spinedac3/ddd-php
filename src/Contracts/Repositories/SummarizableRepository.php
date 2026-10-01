<?php

namespace Spineda\DddFoundation\Contracts\Repositories;

use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;
use Spineda\DddFoundation\ValueObjects\Data\JoinInfo;
use Spineda\DddFoundation\ValueObjects\Data\SummaryInfo;

/**
 * Contract for repositories that calculate a summary over a field
 *
 * @package Spineda\DddFoundation
 */
interface SummarizableRepository
{
    /**
     * Retrieves summary info from the system.
     *
     * @param   string            $sumField     Summary Field
     * @param   FiltersInfo|null  $filtersInfo  Filters Information
     * @param   JoinInfo|null     $joinInfo     Join information condition
     * @param   string            $groupBy      Field Group By
     *
     * @return  SummaryInfo
     */
    public function getSummaryInfo(
        string $sumField,
        ?FiltersInfo $filtersInfo = null,
        ?JoinInfo $joinInfo = null,
        string $groupBy = ''
    ): SummaryInfo;
}
