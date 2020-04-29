<?php

namespace App\B2c\Repositories\Entities\Application;

use App\B2c\Repositories\Contracts\ManualBizCustEquifaxInterface;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Models\BusinessCreditBureauInfo;
use App\B2c\Repositories\Models\CustomerCreditBureauInfo;

/**
 * Application repository class
 */
class ManualBizCustEquifaxRepository extends BaseRepositories implements ManualBizCustEquifaxInterface
{
    /**
     * Class constructor
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Create method
     *
     * @param array $attributes
     *
     * @return Response
     */
    public function create(array $attributes)
    {
        return BusinessCreditBureauInfo::saveBusinessCreditInfo($attributes);
    }

    /**
     *
     * @param array $attributes
     * @param int $app_id
     *
     * @return Response
     */
    public function update(array $attributes, $where=[])
    {        
        return BusinessCreditBureauInfo::updateApplication($where, $attributes);
    }

    public function all($columns = array('*')) {
        return BusinessCreditBureauInfo::all($columns);
    }

    public function delete($ids) 
    {
        
    }

    public function find($id, $columns = array('*'))
    {
        return BusinessCreditBureauInfo::find((int) $id, $columns);
    }

    public function save($attributes = array()) {
        
    }

    protected function destroy($ids) {
        
    }
    
    /**
     * Fetch Manual entered Business Bureau Credit Info by Application ID
     * @param int $appId
     * @return type
     */
    public function fetchBusinessByApplication($appId)
    {
        return BusinessCreditBureauInfo::whereAppId($appId)->first();
    }
    
    /**
     * Fetch Manual entered Business Bureau Credit Info by Application ID
     * @param int $appId
     * @param int $ownerId
     * @return type
     */
    public function fetchOwnerByApplication($appId, $ownerId)
    {
        return CustomerCreditBureauInfo::whereAppId($appId)->whereAppOwnerId($ownerId)->first();
    }
    
    /**
     * Create method
     *
     * @param array $attributes
     *
     * @return Response
     */
    public function createCustomerCreditInfo(array $attributes)
    {
        return CustomerCreditBureauInfo::saveCustomerCreditInfo($attributes);
    }

    /**
     *
     * @param array $attributes
     * @param int $customerCreditId
     *
     * @return Response
     */
    public function updateCustomerCreditInfo($where = [], $arrAppData = [])
    {        
        return CustomerCreditBureauInfo::updateApplication($where, $arrAppData);
    }
    
    /**
     * 
     * @param boolean $hard_delete_status
     * @param array $where
     * @return type
     */
    public function deleteBusinessBureauData($hard_delete_status, $where) 
    {
        return BusinessCreditBureauInfo::deleteBusinessBureauData($hard_delete_status, $where);
    }
    
    /**
     * 
     * @param boolean $hard_delete_status
     * @param array $where
     * @return type
     */
    public function deleteCustomerBureauData($hard_delete_status, $where) 
    {
        return CustomerCreditBureauInfo::deleteCustomerBureauData($hard_delete_status, $where);
    }
}