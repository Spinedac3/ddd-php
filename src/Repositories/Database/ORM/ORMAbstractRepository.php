<?php

namespace Spineda\DddFoundation\Repositories\Database\ORM;

use Illuminate\Database\Query\Builder;
use Spineda\DddFoundation\Persistencies\Eloquent\AbstractModel;
use Spineda\DddFoundation\ValueObjects\Data\FiltersInfo;
use Spineda\DddFoundation\ValueObjects\Data\JoinInfo;
use InvalidArgumentException;
use stdClass;

/**
 * Abstract class for repositories from the ORM
 *
 * @package Spineda\DddFoundation
 */
class ORMAbstractRepository
{
    /**
     * @var AbstractModel
     */
    protected AbstractModel $model;

    /**
     * Fields to be searched directly
     * @var  array
     */
    protected array $searchFieldsDirect = [];

    /**
     * Fields to be searched via like
     * @var  array
     */
    protected array $searchFieldsLike = [];

    /**
     * Repository constructor
     *
     * @param AbstractModel $model Associated model to the repository
     */
    public function __construct(AbstractModel $model)
    {
        $this->model = $model;
    }

    /**
     * Translates a boolean based ordering into a string based one.
     *
     * @param   bool  $reverse  true if the order is reversed.
     *
     * @return string
     */
    protected function translateOrder(bool $reverse): string
    {
        return $reverse ? 'desc' : 'asc';
    }

    /**
     * Construct search criterion in base array filters
     *
     * @return  array
     */
    protected function unifySearchQueries(): array
    {
        $queries = [];

        if (!empty($this->searchFieldsLike)) {
            foreach ($this->searchFieldsLike as $searchField) {
                $query = new stdClass();
                $query->field = $searchField;
                $query->operator = 'LIKE';
                $query->pre = '%';
                $query->post = '%';

                $queries[] = $query;
            }
        }

        if (!empty($this->searchFieldsDirect)) {
            foreach ($this->searchFieldsDirect as $searchField) {
                $query = new stdClass();
                $query->field = $searchField;
                $query->operator = '=';
                $query->pre = '';
                $query->post = '';


                $queries[] = $query;
            }
        }

        return $queries;
    }

    /**
     * Process Direct and Like queries
     *
     * @param Builder $query
     * @param FiltersInfo $filters
     * @param string $table
     *
     * @return Builder
     */
    public function processDirectAndLikeQueries(
        Builder $query,
        FiltersInfo $filters,
        string $table
    ): Builder {
        // Define Search queries
        $search = $filters->getSearch();
        $queries = $this->unifySearchQueries();

        // No search criteria or no fields to query
        if (empty($search) || empty($queries)) {
            return $query;
        }

        // Direct and like queries
        $query->where(function ($query) use ($search, $table, $queries) {
            foreach ($queries as $index => $criterion) {
                if (0 === $index) {
                    $query->where(
                        $table . '.' . $criterion->field,
                        $criterion->operator,
                        $criterion->pre . $search . $criterion->post
                    );
                    continue;
                }

                $query->orWhere(
                    $table . '.' . $criterion->field,
                    $criterion->operator,
                    $criterion->pre . $search . $criterion->post
                );
            }
        });

        return $query;
    }

    /**
     * Processes the Filters if any, in an Eloquent Builder object, and then returns it.
     *
     * @param Builder $query Builder object to use for processing.
     * @param FiltersInfo $filters If not null the filters to apply.
     * @param JoinInfo|null $joinInfo If not null the join information to apply.
     * @return Builder
     */
    protected function processFiltersToQuery(
        Builder $query,
        FiltersInfo $filters,
        ?JoinInfo $joinInfo = null
    ): Builder {
        // Get table name
        $table = $query->getModel()->getTable();

        // Get any filters supplied
        $filterValues = $filters->getFilters()->getValues();

        // Fills in any filters that might have been supplied.
        foreach ($filterValues as $column => $value) {
            if ($value !== null) {
                $query->where($table . '.' . $column, $value);
            }
        }

        // Process Join Information
        if ($joinInfo != null) {
            $filtersSecondTableValues = $joinInfo->getFiltersFirstTable()->getFilters()->getValues();
            $query->join(
                $joinInfo->getTable1(),
                $joinInfo->getTable1() . '.' . $joinInfo->getJoinFieldTable1(),
                '=',
                $joinInfo->getTable2() . '.' . $joinInfo->getJoinFieldTable2()
            );

            foreach ($filtersSecondTableValues as $column => $value) {
                if ($value !== null) {
                    $query->where($joinInfo->getTable1() . '.' . $column, $value);
                }
            }
        }

        // Process Search Criteria
        $this->processDirectAndLikeQueries($query, $filters, $table);

        return $query;
    }

    /**
     * Validate array of arguments
     *
     * @param array $arguments
     *
     * @return  void
     */
    protected function validateArgumentsArray(array $arguments): void
    {
        foreach ($arguments as $key => $argument) {
            if (is_numeric($argument) && $argument < 0) {
                throw new InvalidArgumentException("El parametro $key no es valido", 406);
            }


            if (is_string($argument) && empty($argument)) {
                throw new InvalidArgumentException("El parametro $key no es valido", 406);
            }
        }
    }
}
