<?php

namespace Spineda\DddFoundation\Contracts\ValueObjects\Collections;

/**
 * Store Contract.
 *
 * This Contract will uniform any List/Collection that will have a Store-like behavior.
 *
 * @package Spineda\DddFoundation
 */
interface Store
{
    /**
     * Retrieves all existing values from the Store
     *
     * @return  array
     */
    public function getValues(): array;

    /**
     * Retrieves a value from the Store
     *
     * If the value is not found, the supplied $default value will be returned.
     *
     * @param  string        $name     Name of the Value to be retrieved.
     * @param  string|null   $default  Default returned value, of not found.
     *
     * @return string|null
     */
    public function get(string $name, string $default = null): ?string;

    /**
     * Returns true of the value exists in the Store
     * false otherwise.
     *
     * @param   string  $name   Name of the Value to be searched.
     *
     * @return  bool
     */
    public function has(string $name): bool;

    /**
     * Sets a value in the store
     *
     * @param   string  $name   Name of the Value to be set.
     * @param   string  $value  Value to be set.
     *
     * @return bool
     */
    public function set(string $name, string $value): bool;

    /**
     * Adds a no existing value in the Store.
     *
     * If the value was set, will return true. Otherwise, returns false.
     *
     * @param   string  $name   Name of the Value to be added.
     * @param   string  $value  Value to be added.
     *
     * @return bool
     */
    public function add(string $name, string $value): bool;

    /**
     * Edits an existing value in the Store
     *
     * If the value was set, will return true. Otherwise, returns false
     *
     * @param   string  $name   Name of the Value to be edited.
     * @param   string  $value  Value to be edited.
     *
     * @return  bool
     */
    public function edit(string $name, string $value): bool;

    /**
     * Removes a given value from the store
     *
     * @param   string  $name  Name of the value to be removed from the store.
     *
     * @return bool
     */
    public function delete(string $name): bool;
}
