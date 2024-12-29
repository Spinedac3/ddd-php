<?php

namespace Spineda\DddFoundation\ValueObjects\Collections;

use Spineda\DddFoundation\Contracts\ValueObjects\Collections\Store as Contract;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Store Associative Collection.
 *
 * This class will hold and process all data associated with a Store/Set.
 *
 * @package   @package Spineda\DddFoundation
 * @link      https://en.wikipedia.org/wiki/Set_(abstract_data_type)
 */
class Store implements JsonSerializable, Contract
{
    /**
     * @var   array  $store  Store that will hold the values.
     */
    protected array $store = [];

    /**
     * @var   string|null  $context  Context for the Store's values.
     */
    protected ?string $context = null;


    /**
     * Initializes a new instance of a Store.
     *
     * @param   string|null  $context  Value's context. Can be used as an inner store.
     *
     * @throws InvalidArgumentException  If supplied context is a not a no empty string.
     *
     * @return void
     */
    public function __construct(?string $context = null)
    {
        // Validates provided parameters.
        if (is_string($context) && strlen(trim($context)) === 0) {
            throw new InvalidArgumentException('El contexto dado no puede ser una cadena vacía.');
        }

        // Sets the data context.
        $this->context = $context;
    }

    /**
     * Retrieves all existing Values from the Store.
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->store;
    }

    /**
     * Retrieves a value from the Store.
     *
     * If the value is not found, the supplied $default value will be returned.
     *
     * @param   string       $name     Name of the Value to be retrieved.
     * @param   string|null  $default  Default returned value, of not found.
     *
     * @throws InvalidArgumentException  If supplied name is a not a no empty string.
     *
     * @return string|null
     */
    public function get(string $name, ?string $default = null): ?string
    {
        // Sanitize provided $name/key.
        $name = $this->sanitizeName($name);

        // Validate if the provided key is available in the "store".
        if ($this->has($name)) {
            return $this->store[$this->name($name)];
        }

        return $default;
    }

    /**
     * Returns true of the value exists in the store.
     * false otherwise.
     *
     * @param   string  $name  Name of the Value to be searched.
     *
     * @throws InvalidArgumentException  If supplied name is a not a no empty string.
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        // Sanitize provided $name/key.
        $name = $this->sanitizeName($name);

        return array_key_exists($this->name($name), $this->store);
    }

    /**
     * Sets a value in the Store.
     *
     * @param   string  $name   Name of the Value to be set.
     * @param   string  $value  Value to be set.
     *
     * @throws InvalidArgumentException  If supplied name is a not a no empty string.
     *
     * @return bool
     */
    public function set(string $name, string $value): bool
    {
        // Sanitize provided $name/key.
        $name = $this->sanitizeName($name);

        // Sets the value in the store.
        $this->store[$this->name($name)] = $value;

        return true;
    }

    /**
     * Adds a no existing value in the Store.
     *
     * @param   string  $name   Name of the Value to be added.
     * @param   string  $value  Value to be added.
     *
     * @throws  InvalidArgumentException  If supplied name is a not a no empty string.
     *
     * @return bool
     */
    public function add(string $name, string $value): bool
    {
        // Sanitize provided $name/key.
        $name = $this->sanitizeName($name);

        // Validate if the provided key is available in the "store".
        if ($this->has($name)) {
            return false;
        }

        // Sets the value in the store.
        return $this->set($name, $value);
    }

    /**
     * Edits an existing value in the Store.
     *
     * @param   string  $name   Name of the Value to be edited.
     * @param   string  $value  Value to be edited.
     *
     * @throws  InvalidArgumentException  If supplied name is a not a no empty string.
     *
     * @return bool
     */
    public function edit(string $name, string $value): bool
    {
        // Sanitize provided $name/key.
        $name = $this->sanitizeName($name);

        // Validate if the provided key is available in the "store".
        if (! $this->has($name)) {
            return false;
        }

        // Sets the value in the store.
        return $this->set($name, $value);
    }

    /**
     * Removes a given value from the Store.
     *
     * @param   string  $name  Name of the value to be removed from the store.
     *
     * @throws  InvalidArgumentException  If supplied name is a not a no empty string.
     *
     * @return bool
     */
    public function delete(string $name): bool
    {
        // Sanitize provided $name/key.
        $name = $this->sanitizeName($name);

        // Validate if the provided key is available in the "store".
        if (! $this->has($name)) {
            return false;
        }

        // Removes value from Store.
        unset($this->store[$this->name($name)]);

        return true;
    }

    /**
     * Returns sanitized $name/key.
     *
     * @param   string  $name  Name to be sanitized.
     *
     * @throws  InvalidArgumentException  If supplied name is a not a no empty string.
     *
     * @return string
     */
    private function sanitizeName(string $name): string
    {
        // Validates provided parameters.
        if (strlen(trim($name)) === 0) {
            throw new InvalidArgumentException('El valor provisto no puede ser una cadena vacía.');
        }

        // Returns sanitized $name/key.
        return strtolower(trim($name));
    }

    /**
     * Returns full name, with namespace included if necessary.
     *
     * @param   string  $name  Sanitized name.
     *
     * @return string
     */
    private function name(string $name): string
    {
        // Returns sanitized $name/key.
        if ($this->context === null) {
            return $name;
        } else {
            return ($this->context . '.' . $name);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see    JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): array
    {
        return $this->getValues();
    }
}
