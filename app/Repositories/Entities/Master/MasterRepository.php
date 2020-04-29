<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Contracts\MasterInterface;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Models\Master\BusinessStructure;
use App\B2c\Repositories\Models\Master\Products;
use App\B2c\Repositories\Models\Master\InterestRate;
use App\B2c\Repositories\Models\Master\PrimeRate;
use App\B2c\Repositories\Models\Master\InterestRateLog;
use App\B2c\Repositories\Models\Master\LineOfPurpose;


/**
 * Master Repository
 */
class MasterRepository extends BaseRepositories implements MasterInterface
{
   

    /* Get All Active Business Structure
     * 
     * @param void()
     * 
     * @return object
     * 
     * @since 0.1
     */

    public function getBusinessStructure()
    {
        return BusinessStructure::getAllBusinessStructure();
    }

    /*
     * get Business Structure data
     * @param $id 
     */
    public function getBizStructuteData($id)
    {
        return BusinessStructure::getBizStructuteData($id);
    }


    /* Add or Update Business Structure
     * 
     * @param $bizData, $biz_id
     * 
     * @return integer
     */
    
    public function addOrUpdateBusinessStructure($bizData, $biz_id)
    { 
        $res = BusinessStructure::addOrUpdateBusinessStructure($bizData, $biz_id);
        return $res;
    }
    
    /* Get All Active Products 
     * 
     * @param void()
     * 
     * @return object 
     * 
     * @since 0.1
     */

    
    /* Get All Active Business Structure
     * 
     * @param void()
     * 
     * @return object roles
     * 
     * @since 0.1
     */

    public function getLoanPurpose()
    {
        return LineOfPurpose::getPurposeOfLoan();
    }
    
    /*
     * get purpose of loan data
     * @param $id
     */
    public function getLoanPurposeData($id)
    {
        return LineOfPurpose::getLoanPurposeData($id);
    }


    public function getProductList()
    {
        
        return Products::getAllProductList();
    }
    
    /* Add or Product
     * 
     * @param $prodData, $prod_id
     * 
     * @return integer
     */
    
    public function createOrUpdateProducts($prodData, $prod_id)
    { 
        $res = Products::createOrUpdateProducts($prodData, $prod_id);
        return $res;
    }
    
    /*
     * get Productdata
     * @param $id 
     */
    public function getProductsData($id)
    {
        return Products::getProductsData($id);
    }

    /**
     * Create
     *
     * @param array $attributes
     */
    protected function create(array $attributes)
    {
    }
    
    /*
     * Get All Line of Purpose Data
     * return Objects data
     */
    public function getAllLineOfPurpose()
    {
        return LineOfPurpose::getAllLineOfPurposeData();
    }

    /**
     * Update
     *
     * @param array $attributes
     * @param integer $id
     */
    protected function update(array $attributes, $id)
    {
    }

    protected function destroy($ids) {
        
    }

    public function all($columns = array()) {
        
    }

    public function delete($ids) {
        
    }

    public function find($id, $columns = array()) {
        
    }

    public function save($attributes = array()) {
        
    }
    
    /* Add or Update Loan Purpose
     * 
     * @param $bizData, $biz_id
     * 
     * @return integer
     */
    
    public function addOrUpdateLoanPurpose($bizData, $biz_id)
    { 
        $res = LineOfPurpose::addOrUpdateLoanPurpose($bizData, $biz_id);
        return $res;
    }
    
    /* Get All Product List
     *  
     *  
     * 
     * @since 0.1
     */

    public function getProductByPurposeId()
    {
        return Products::getProductByPurposeId();
    }
    
    /* Update interest rate
     *  
     *  
     * 
     * @since 0.1
     */

    public function createOrUpdateInterestRate($arrData, $id)
    {
        return InterestRate::createOrUpdateInterestRate($arrData, $id);
    }
    
    /* Update interest rate
     *  
     *  
     * 
     * @since 0.1
     */

    public function getAllInterestRate($attributes) 
    {
        return InterestRate::getAllInterestRate($attributes);
    }
    
     /* Update interest rate log data
     *  
     *  
     * 
     * @since 0.1
     */

    public function createOrUpdateInterestRateLogData($arrData)
    {
        return InterestRateLog::createOrUpdateInterestRateLogData($arrData);
    }
    
     /* Get active prime rate
     *  
     *  
     * 
     * @since 0.1
     */

    public function getActPrimeRate()
    {
        return PrimeRate::getActPrimeRate();
    }
}
