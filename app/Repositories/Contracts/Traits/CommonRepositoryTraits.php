<?php

namespace App\B2c\Repositories\Contracts\Traits;

trait CommonRepositoryTraits
{
    /**
     * Validating and parsing data passed thos this method
     *
     * @param array $attributes
     *
     * @return New record ID that was added
     *
     * @since 0.1
     */
    public function save($attributes = [], $app_id = null)
    {
        /**
         * Check Data is Array
         */
        if (! is_array($attributes)) {
            throw new InvalidDataTypeExceptions('Please send an array');
        }
        
        /**
         * Check Data is not blank
         */
        if (empty($attributes)) {
            throw new BlankDataExceptions('No Data Found');
        }
        
        return is_null($app_id) ?  $this->create($attributes) :  $this->update($attributes, $app_id);
    }
    
    /**
     * Get all records method
     *
     * @param array $columns
     *
     * @since 0.1
     */
    public function all($columns = array('*'))
    {
        //
    }

    /**
     * Find method
     *
     * @param mixed $id
     * @param array $columns
     *
     * @since 0.1
     */
    public function find($id, $columns = array('*'))
    {
        //
    }
    
    /**
     * Delete method
     *
     * @param mixed $ids
     *
     * @since 0.1
     */
    protected function destroy($ids)
    {
        //
    }

    /**
     * Delete method
     *
     * @param mixed $ids
     *
     * @since 0.1
     */
    public function delete($ids)
    {
        //
    }
}
