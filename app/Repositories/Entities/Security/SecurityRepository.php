<?php

namespace App\B2c\Repositories\Entities\Security;

use App\B2c\Repositories\Models\CashEquivalents;
use App\B2c\Repositories\Contracts\SecurityInterface;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Contracts\Traits\CommonRepositoryTraits;
use App\B2c\Repositories\Models\Master\SecurityType;
use App\B2c\Repositories\Models\Master\CheckListItem;
use App\B2c\Repositories\Models\OfferCheckList;
use App\B2c\Repositories\Models\SavingAccounts;
use App\B2c\Repositories\Models\Master\RealEstateType;
use App\B2c\Repositories\Models\Master\PropertyType;
use App\B2c\Repositories\Models\Master\UseOfProperty;
use App\B2c\Repositories\Models\GSA;
use App\B2c\Repositories\Models\RealestateAppraisal;
use App\B2c\Repositories\Models\AppraisalDoc;
use App\B2c\Repositories\Models\RealEstate;
use App\B2c\Repositories\Models\RealEstateCoOwner;
use App\B2c\Repositories\Models\RealEstateOwnership;
use App\B2c\Repositories\Models\Gic;


/**
 * Application repository class
 */
class SecurityRepository extends BaseRepositories implements SecurityInterface
{

    use CommonRepositoryTraits;
     

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
    protected function create(array $attributes)
    {
        return CashEquivalents::saveApplication($attributes);
    }

    /**
     *
     * @param array $attributes
     * @param int $app_id
     *
     * @return Response
     */
    public function update(array $attributes, $app_id)
    {        
        return CashEquivalents::updateApplication((int) $app_id, $attributes);
    }

    /**
     * Get all records method
     *
     * @param array $columns
     */
    public function all($columns = array('*'))
    {
        return CashEquivalents::all($columns);
    }
 
   

    /**
     * Find method
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function find($id, $columns = array('*'))
    {
        $varApplicationData = CashEquivalents::find((int) $id, $columns);

        return $varApplicationData;
    }
    
    /** get cash data
     * 
     * @return type
     */
    public function getSecurityTypes(){
         return SecurityType::getSecurityTypes();
    }
     
    /** get cash data by user_id and app_id
     * 
     * @return type
     */
    public function getCashEquivalents($app_id,$user_id){
         return CashEquivalents::getCashEquivalents($app_id,$user_id);
    }
    
    /** get cash data by form_id
     * 
     * @return type
     */
    public function getCashEquivalentsById($form_id,$app_id,$user_id){
        //dd($form_id);
         return CashEquivalents::getCashEquivalentsById($form_id,$app_id,$user_id);
    }
    
    /** save cash data
     * @return type
     */
    public function saveCashEquivalents($arrCashData, $id=null){
         return CashEquivalents::saveCashEquivalents($arrCashData,$id);
    }
    
     /**
     * delete cash_equivalent
     */
    public function deleteCashEquivalents($form_id,$app_id,$user_id)
    {
        return CashEquivalents::deleteCashEquivalents((int)$form_id,(int)$app_id,(int)$user_id);
    }
    
    
    /**
     * get All checklist item
     * @return $array
     */
    public function getAllCheckListItem($approval_type)
    {
        return CheckListItem::getActiveCheckListItem($approval_type);
    }
    
    /**
     * save Offer Checklist item 
     * @return true
     */
    
     public function saveUpdateAppCheckList($arrData,$id= null)
    {
        return OfferCheckList::saveUpdateAppCheckList($arrData,$id);
    }
    /**
     * save Offer Checklist item 
     * @return true
     */
     public function getAppChecklist($app_user_id,$app_id){
         
       return OfferCheckList::getAppChecklist($app_user_id,$app_id);
     }
          
     /**
     * save Offer Checklist item 
     * @return true
     */

     public function deleteCheckListApp($app_user_id,$app_id){
         
       return OfferCheckList::deleteCheckListApp($app_user_id,$app_id);
     }
    
     /**
     * update Checklist App by ID
     * @return true
     */
     public function updateAppCheckListById($app_user_id,$app_id,$edit_check_list_id,$arrData){
       return OfferCheckList::updateAppCheckListById($app_user_id,$app_id,$edit_check_list_id,$arrData);
     }
     
    /** save cash data
     * @return type
     */
    public function saveSavingAccounts($arrCashData, $id=null){ 
         return SavingAccounts::saveSavingAccounts($arrCashData, $id);
    }
    
    /**
     * get application cash data
     * 
     * @param array $attributes
     * @param array $selected
     * @return mixed
     */
    public function getApplicationCashEquivalentData($app_id,$user_id)
    {
        return  CashEquivalents::getApplicationCashEquivalentData($app_id,$user_id);  
    }
    /**
     * get application cash data
     * 
     * @param array $attributes
     * @param array $selected
     * @return mixed
     */
    public function getSavingAccounts($app_id , $user_id)
    {
        return  SavingAccounts::getSavingAccounts($app_id, $user_id);  
    }
    
    /**
     * Delete saving account by id
     * 
     * @param int $id
     * @return mixed
     */
    public function deleteSavingAccountById($id)
    {
        return  SavingAccounts::deleteSavingAccountById($id);  
    }
    /**
     * Delete chequing account by id
     * 
     * @param int $id
     * @return mixed
     */
    public function deleteChequingAccountById($id)
    {
        return  CashEquivalents::deleteChequingAccountById($id);  
    }
    
    /**
     * get active real estate type
     * @return true
     */
     public function getRealEstateType(){
         
       return RealEstateType::getActiveRealEstateType();
     }
    /**
     * get active property type 
     * @return true
     */
     public function getPropertyType($attributes=[],$select=[]){
         
       return PropertyType::getActivePropertyType($attributes,$select);
     }
    /**
     * get active property type 
     * @return true
     */
     public function getUseOfProperty($attributes=[],$select=[]){
         
       return UseOfProperty::getActiveUseOfProperty($attributes,$select);
     }
     
     /**
     * save gsa item
     * @return true
     */
    
     public function saveGsaFormData($arrData,$id)
    {
        return GSA::saveGsaFormData($arrData,$id);
    }
    
    /**
     * get form data
     * @param array $attributes
     * @param array $selected
     * @return mixed
     */
    public function getGsaFormData($app_user_id , $app_id)
    {
        return  GSA::getGsaFormData($app_user_id , $app_id);  
    }
    
     /**
     * save Offer Checklist item 
     * @return true
     */

     public function deleteGsaformData($app_user_id,$app_id){
         
       return GSA::deleteGsaformData($app_user_id,$app_id);
     }
     
     /**
     * Delete saving account by id
     * 
     * @param int $id
     * @return mixed
     */
    public function deleteRealEstateCoOwnerById($id)
    {
        return  RealEstateCoOwner::deleteRealEstateCoOwnerById($id);  
    }
    
    /** save real estate property data
     * @return type
     */
    public function getRealEstatePropertyDetails($where,$type=null){
         return RealEstate::getRealEstatePropertyDetails($where,$type);
    }
    /** save real estate property data
     * @return type
     */
    public function savePropertyDetails($arrPropertyData, $id=null){
         return RealEstate::savePropertyDetails($arrPropertyData,$id);
    }
    /** save property primary owner data
     * @return type
     */
    public function savePrimaryOwnerDetails($arrPrimaryOwnerData, $id=null){
         return RealEstateOwnership::savePrimaryOwnerDetails($arrPrimaryOwnerData,$id);
    }
    /** save saveCoOwnerDetails property data
     * @return type
     */
    public function saveCoOwnerDetails($arrCoOwnerData, $id=null){
         return RealEstateCoOwner::saveCoOwnerDetails($arrCoOwnerData,$id);
    }
    /**
     * save appraisal Data
     * @return true
     */
     public function saveRealestateAppraisal($arrData,$id=null)
    {
        return RealestateAppraisal::saveRealEstateAppraisal($arrData,$id);
    }
    
    /**
     * save Offer Checklist item 
     * @return true
     */
     public function saveAppraisalDoc($arrData)
    {
        return AppraisalDoc::saveAppraisalDoc($arrData);
    }
    
    /**
     * delete Checklist
     * @return true
     */
     public function deleteChecklist($checklist_id){
       
         return OfferCheckList::deleteChecklist($checklist_id);
     }
    
    
     /* Download document by encrypt id
     *
     * @param string $doc_encrypt_id
     *
     * @return type
     *
     * @since 0.1
     */
    public function getDownloadFile($doc_encrypt_id)
    {
        return AppraisalDoc::getDownloadFile($doc_encrypt_id);
    }
    
    /**
     * Delete saving account by id
     * 
     * @param int $id
     * @return mixed
     */
    public function deleteAppraisalDoc($id)
    {
        return  AppraisalDoc::deleteAppraisalDoc($id);  
    }
    
    /** save real estate property data
     * @return type
     */
    public function getRealEstatePriceTotal($where, $select = [], $relations = [])
    {
         return RealEstate::getRealEstatePriceTotal($where, $select, $relations);
    }
    
    public function updateofferCheckListById($id,$arrData){
        return OfferCheckList::updateofferCheckListById($id,$arrData);
    }
    
    
    /**
     * get application cash data
     * 
     * @param array $attributes
     * @param array $selected
     * @return mixed
     */
    public function getGicAccounts($app_id , $user_id)
    {
        return  Gic::getGicAccounts($app_id, $user_id);  
    }
    /**
     * Delete saving account by id
     * 
     * @param int $id
     * @return mixed
     */
    public function deleteGicAccountById($id)
    {
        return  Gic::deleteGicAccountById($id);  
    }
    
    /** save cash data
     * @return type
     */
    public function saveGicEquivalents($arrCashData, $id=null){ 
         return Gic::saveGicEquivalents($arrCashData, $id);
    }
    
    /**
     * get checklist item according to illegal entity
     * 
     * @param array $illegal entity id
     * @return mixed
     */
    public function getAllChecklistItemByIllegalEntity($illegal_entity_id)
    {
         return CheckListItem::getAllChecklistItemByIllegalEntity($illegal_entity_id);
    }
    
     /**
     * get checklist item according to condition
     * 
     * @param array $illegal entity id
     * @return mixed
     */
    public function getConditionalChecklist($where)
    {
         return CheckListItem::getConditionalChecklist($where);
    }
    
    /**
     * get checklist item according to condition
     * 
     * @param array $illegal entity id
     * @return mixed
     */
     public function getApprovalTypeChecklist($where)
    {
         return CheckListItem::getApprovalTypeChecklist($where);
    }
    
     /**
     * get checklist item according to condition
     * 
     * @param array $illegal entity id
     * @return mixed
     */
    public function getTotalPropertyPrice($where)
    {
         return RealEstate::getTotalPropertyPrice($where);
    }
     /**
     * get checklist item according to condition
     * 
     * @param array $illegal entity id
     * @return mixed
     */
    public function getTotalPropertyDebt($where)
    {
         return RealEstate::getTotalPropertyDebt($where);
    }
    
    /** save getCoOwners
     * @return type
     */
    public function getRealEstateCoOwner($app_id ){
         return RealEstateCoOwner::getRealEstateCoOwner($app_id);
    }
}