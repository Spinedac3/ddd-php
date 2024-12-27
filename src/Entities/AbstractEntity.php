<?php

namespace Spineda\DddFoundation\Entities;

use Spineda\DddFoundation\Contracts\ValueObjects\Collections\IsCollectable;
use JsonSerializable;
use UnderflowException;

/**
 * Base class for entities
 *
 * @package Spineda\DddFoundation
 */
abstract class AbstractEntity implements IsCollectable
{
    /**
     * Entity key
     *
     * @var string
     */
    protected string $key;

    /**
     * Array of fields representing the key of this entity
     *
     * @var array
     */
    protected array $keyFields = ['id'];

    /**
     * Required properties
     *
     * @var array
     */
    protected array $required = ['key'];

    /**
     * Properties that are of the object and not from the actual entity
     *
     * @var array
     */
    protected array $nonEntityProperties = ['key', 'keyFields', 'required', 'nonEntityProperties'];

    /**
     * Builds an entity given an array of field data
     *
     * @param   array  $fields  Data fields
     */
    public function __construct(array $fields)
    {
        // Sets each field into the entity
        foreach ($fields as $field => $value) {
            if (property_exists($this, $field)) {
                $this->$field = $value;
            }
        }

        // Creates the key using the $keyFields property
        if (!empty($this->keyFields)) {
            $keyValues = [];

            foreach ($this->keyFields as $keyField) {
                // If the field does not exist, it inserts a dot
                $keyValues[] = $this->$keyField ?? '.';
            }

            $this->key = implode('_', $keyValues);
        }

        // Sanitizes the entity
        $this->sanitize();

        // Validates required fields
        $this->validate();
    }

    /**
     * Gets the entity attributes
     *
     * @return  array
     */
    public function getAttributes(): array
    {
        $array = get_object_vars($this);

        if (!count($this->nonEntityProperties)) {
            return $array;
        }

        // Removes the non-entity properties from the resulting array
        foreach ($this->nonEntityProperties as $property) {
            if (isset($array[$property])) {
                unset($array[$property]);
            }
        }

        return $array;
    }

    /**
     * {@inheritDoc}
     *
     * @return object
     * @see    JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): object
    {
        return (object) $this->getAttributes();
    }

    /**
     * Validates the instance required properties
     *
     * @throws  UnderflowException
     */
    public function validate()
    {
        if (!count($this->required)) {
            return;
        }

        foreach ($this->required as $required) {
            if (!isset($this->$required) || null === $this->required) {
                throw new UnderflowException(sprintf('El valor de %s no fue proporcionado', $required));
            }
        }
    }

    /**
     * Sanitizes the entity
     *
     * @return bool
     */
    public function sanitize(): bool
    {
        foreach ($this->getAttributes() as $attribute => $value) {
            // Scape
            if (is_string($value)) {
                $this->$attribute = htmlspecialchars($this->$attribute, ENT_QUOTES, 'UTF-8');
            }
        }

        return true;
    }

    /**
     * Key getter
     *
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
