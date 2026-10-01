<?php

namespace Spineda\DddFoundation\Repositories\Database\ORM\Behaviors;

use Spineda\DddFoundation\Contracts\Repositories\Behaviors\CrudRepository;
use Spineda\DddFoundation\Entities\AbstractEntity;
use Spineda\DddFoundation\Exceptions\Behaviors\Crud\RecordNotCreatedException;
use Spineda\DddFoundation\Exceptions\Behaviors\Crud\RecordNotDeletedException;
use Spineda\DddFoundation\Exceptions\Behaviors\Crud\RecordNotUpdatedException;
use Spineda\DddFoundation\Persistencies\Eloquent\AbstractModel;
use PDOException;

/**
 * ORM implementation for CRUD repositories
 *
 * @package Spineda\DddFoundation
 */
class ORMCrudRepository implements CrudRepository
{
    /**
     * {@inheritDoc}
     * @see  CrudRepository::create()
     *
     * @throws  RecordNotCreatedException
     */
    public function create(AbstractEntity | array $entity, AbstractModel $model): int
    {
        $record = $this->saveNewRecord($entity, $model);

        // Only an incrementing model knows the key it was given
        if ($record->getIncrementing()) {
            return $record->getKey();
        }

        return 0;
    }

    /**
     * {@inheritDoc}
     * @see  CrudRepository::update()
     *
     * @throws  RecordNotUpdatedException
     */
    public function update(AbstractModel $oldEntity, AbstractEntity | array $newEntity): AbstractModel
    {
        $record = $this->fillModel($oldEntity, $newEntity);

        try {
            if (!$record->save()) {
                throw new RecordNotUpdatedException();
            }
        } catch (PDOException $exception) {
            throw new RecordNotUpdatedException($exception->getMessage());
        }

        return $record;
    }

    /**
     * {@inheritDoc}
     * @see  CrudRepository::delete()
     *
     * @throws  RecordNotDeletedException
     */
    public function delete(AbstractModel $entity): bool
    {
        try {
            if (!$entity->delete()) {
                throw new RecordNotDeletedException();
            }
        } catch (PDOException $exception) {
            throw new RecordNotDeletedException($exception->getMessage());
        }

        return true;
    }

    /**
     * {@inheritDoc}
     * @see  CrudRepository::createNotIncrementing()
     *
     * @throws  RecordNotCreatedException
     */
    public function createNotIncrementing(AbstractEntity | array $entity, AbstractModel $model): bool
    {
        $this->saveNewRecord($entity, $model);

        return true;
    }

    /**
     * Saves a new record of the model, filled with the entity data
     *
     * @param   AbstractEntity|array  $entity  Entity
     * @param   AbstractModel         $model   Model
     *
     * @return  AbstractModel
     * @throws  RecordNotCreatedException
     */
    protected function saveNewRecord(AbstractEntity | array $entity, AbstractModel $model): AbstractModel
    {
        // Gets a new record for insertion
        $record = $this->fillModel($model->newInstance(), $entity);

        try {
            if (!$record->save()) {
                throw new RecordNotCreatedException();
            }
        } catch (PDOException $exception) {
            throw new RecordNotCreatedException($exception->getMessage());
        }

        return $record;
    }

    /**
     * Fills the model with the entity data
     *
     * @param   AbstractModel         $model   Model
     * @param   AbstractEntity|array  $entity  Entity
     *
     * @return  AbstractModel
     */
    protected function fillModel(AbstractModel $model, AbstractEntity | array $entity): AbstractModel
    {
        if ($entity instanceof AbstractEntity) {
            $entity = $entity->getAttributes();
        }

        foreach ($entity as $field => $value) {
            $model->$field = $value;
        }

        return $model;
    }
}
