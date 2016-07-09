<?php
/**
 * Rating Plugin for Craft CMS
 *
 * @package   Rating
 * @author    Flipbox Factory
 * @copyright Copyright (c) 2015, Flipbox Digital
 * @link      https://flipboxfactory.com/craft/rating/
 * @license   https://flipboxfactory.com/craft/rating/license
 */

namespace craft\plugins\rating\services\traits;

use Craft;
use craft\app\base\Model as BaseModel;
use craft\app\db\ActiveRecord;
use craft\app\errors\Exception;

trait Model
{

    /**
     * @var array of all models
     */
    private $allModels;

    /**
     * @var array of all models indexed by Id
     */
    private $modelsById = [];

    /**
     * @var array of all models indexed by handle
     */
    private $modelsByHandle = [];

    /*******************************************
     * ABSTRACT
     *******************************************/

    /**
     * @param null $condition
     * @return ActiveRecord[]
     */
    abstract protected function findAllRecords($condition = null);

    /**
     * @param $condition
     * @return ActiveRecord
     */
    abstract protected function findRecord($condition);

    /**
     * @param $config
     * @return BaseModel
     */
    abstract protected function newModel($config);

    /**
     * @throws Exception
     */
    abstract protected function notFoundException();

    /**
     * @param null $id
     * @throws Exception
     */
    abstract protected function notFoundByIdException($id = null);

    /**
     * @param null $handle
     * @throws Exception
     */
    abstract protected function notFoundByHandleException($handle = null);


    /*******************************************
     * CACHE
     *******************************************/

    /**
     * @param BaseModel $model
     * @return $this
     */
    public function cacheModel(BaseModel $model)
    {

        // Check if already in cache by id
        if (!$this->isModelCachedById($model->id)) {

            // Cache it
            $this->modelsById[$model->id] = $model;

        }

        // Check if already in cache by handle
        if (!$this->isModelCachedByHandle($model->handle)) {

            // Cache it
            $this->modelsByHandle[$model->handle] = $model;

        }

        return $this;

    }

    /**
     * Find an existing cached model by ID
     *
     * @param $id
     * @return null
     */
    public function findCachedModelById($id)
    {

        // Check if already in cache
        if ($this->isModelCachedById($id)) {
            return $this->modelsById[$id];
        }

        return null;

    }

    /**
     * Find an existing cached model by handle
     *
     * @param $handle
     * @return null
     */
    public function findCachedModelByHandle($handle)
    {

        // Check if already in cache
        if ($this->isModelCachedByHandle($handle)) {
            return $this->modelsByHandle[$handle];
        }

        return null;

    }

    /**
     * Identify whether a model is cached by ID
     *
     * @param $id
     * @return bool
     */
    private function isModelCachedById($id)
    {
        return array_key_exists($id, $this->modelsById);
    }

    /**
     * Identify whether a model is cached by handle
     *
     * @param $handle
     * @return bool
     */
    private function isModelCachedByHandle($handle)
    {
        return array_key_exists($handle, $this->modelsByHandle);
    }

    /*******************************************
     * FIND
     *******************************************/

    /**
     * @return BaseModel[]
     */
    public function findAll($indexBy = null)
    {

        // Check cache
        if (is_null($this->allModels)) {

            $this->allModels = [];

            // Find record in db
            if ($records = $this->findAllRecords()) {

                foreach ($records as $record) {

                    // Perhaps in ID cache
                    if (!$model = $this->findCachedModelById($record->id)) {

                        // New
                        $model = $this->newModel($record);

                        // Cache it
                        $this->cacheModel($model);

                    }

                    $this->allModels[] = $model;

                }

            }

        }

        if (!$indexBy) {

            $models = $this->allModels;

        } else {

            $models = [];

            foreach ($this->allModels as $model) {

                $models[$model->$indexBy] = $model;

            }

        }

        return $models;

    }


    /**
     * @param $identifier
     * @return BaseModel|null
     */
    public function find($identifier)
    {

        // already an element
        if ($identifier instanceof BaseModel) {

            return $identifier;

        } elseif (is_numeric($identifier)) {

            return $this->findById($identifier);

        }

        return $this->findByHandle($identifier);


    }

    /**
     * @param $id
     * @return BaseModel|null
     */
    public function findById($id)
    {

        // Check cache
        if (!$model = $this->findCachedModelById($id)) {

            // Find record in db
            if ($record = $this->findRecord($id)) {

                // New model
                $model = $this->newModel($record);

                // Cache it
                $this->cacheModel($model);

            } else {

                $this->modelsById[$id] = null;

                return null;

            }

        }

        return $model;

    }


    /**
     * @param $handle
     * @return BaseModel|null
     */
    public function findByHandle($handle)
    {

        // Check cache
        if (!$model = $this->findCachedModelByHandle($handle)) {

            // Find record in db
            if ($record = $this->findRecord([
                'handle' => $handle
            ])
            ) {

                // New model
                $model = $this->newModel($record);

                // Cache it
                $this->cacheModel($model);

            } else {

                $this->modelsByHandle[$handle] = null;

                return null;

            }

        }

        return $model;

    }

    /*******************************************
     * GET
     *******************************************/

    /**
     * @param $identifier
     * @return BaseModel|null
     * @throws Exception
     */
    public function get($identifier)
    {

        $orgElement = $this->find($identifier);

        if (is_null($orgElement)) {

            $this->notFoundException();

        }

        return $orgElement;

    }

    /**
     * @param $id
     * @return BaseModel|null
     * @throws Exception
     */
    public function getById($id)
    {

        $orgElement = $this->findById($id);

        if (is_null($orgElement)) {

            $this->notFoundByIdException($id);

        }

        return $orgElement;

    }

    /**
     * @param $handle
     * @return BaseModel|null
     * @throws Exception
     */
    public function getByHandle($handle)
    {

        $orgElement = $this->findByHandle($handle);

        if (is_null($orgElement)) {

            $this->notFoundByHandleException($handle);

        }

        return $orgElement;

    }

}
