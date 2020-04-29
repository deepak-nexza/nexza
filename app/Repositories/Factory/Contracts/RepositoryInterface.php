<?php namespace App\B2c\Repositories\Factory\Contracts;

/**
 * RepositoryInterface provides the standard functions to be expected of ANY
 * repository.
 */
interface RepositoryInterface
{
    /**
     * Save method
     *
     * @param array $attributes
     */
    public function save($attributes = []);
    
    /**
     * Get all records method
     *
     * @param array $columns
     */
    public function all($columns = array('*'));

    /**
     * Find method
     *
     * @param mixed $id
     * @param array $columns
     */
    public function find($id, $columns = array('*'));

    /**
     * Delete method
     *
     * @param mixed $ids
     */
    public function delete($ids);
}
