<?php

namespace App\B2c\Repositories\Entities\Guest;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\B2c\Repositories\Contracts\GuestInterface;
use App\B2c\Repositories\Models\Master\PreQualifyingQuestions;
use App\B2c\Repositories\Models\Master\PromoCode;
use App\B2c\Repositories\Models\Master\Industry;
use App\B2c\Repositories\Models\Master\Business;
use App\B2c\Repositories\Models\KnockoutReference;
use App\B2c\Repositories\Models\UserPromoCode;
use App\B2c\Repositories\Models\TrackUser;
use App\B2c\Repositories\Models\FundedMonitoringReport;
use App\B2c\Repositories\Models\Master\LineOfPurpose;
use App\B2c\Repositories\Models\EquifaxResponseCronLog;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Contracts\Traits\CommonRepositoryTraits;
use App\B2c\Repositories\Models\AuditReports;
use App\B2c\Repositories\Models\MiReports;

class GuestRepository extends BaseRepositories implements GuestInterface
{

    use CommonRepositoryTraits;

    /**
     * Glass Repository Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create method
     * @param array $attributes
     */
    public function create(array $attributes)
    {
        //Implement
    }

    /**
     * Update method
     * @param array $attributes
     * @param integer $id
     */
    public function update(array $attributes, $id)
    {
        //Implement update
    }
    
    /**
     * Get all active pre qualifying question
     */
    public function getQualifyingQuestions(){
        return PreQualifyingQuestions::getQualifyingQuestions();
    }
    
    /**
     * Get all active industry type
     */
    public function getAllIndustry(){
        return Industry::getAllIndustry();
    }

    /**
     * Get all active Entities
     */
    public function getAllEntities(){
        return Business::getAllEntities();
    }
    
    /**
     * Get all active Line for Purpose
     */
    public function getAllPurposeForLine(){
        return LineOfPurpose::getAllPurpose();
    }
    
    /**
     *  save knockout reference
     * 
     * @param array $arrData
     * @return integer
     */
    public function saveKnockoutReference($knockout_id = null, $arrData = [])
    {
        return KnockoutReference::saveKnockoutReference($knockout_id, $arrData);
    }
    
    /**
     *  get knockout reference
     * 
     * @param array $arrData
     * @param array $select
     * @return array
     */
    public function getKnockoutWithRef($attribute = [], $select = [])
    {
        return KnockoutReference::getKnockoutWithRef($attribute, $select);
    }
    
    /**
     * get sub division Industry by division
     *
     * @param array $divIndustry
     * @return type
     */
    public function getIndustrySubDivision($divIndustry)
    {
        return Industry::getIndustrySubDivision($divIndustry);
    }

    /**
     * get group Industry by subdivision
     *
     * @param array $divIndustry
     * @return type
     */
    public function getIndustryGroupBySubdivision($subdivId)
    {
        return Industry::getIndustryGroupBySubdivision($subdivId);
    }

    /**
     * get industry class by group
     *
     * @param array $divIndustry
     * @return type
     */
    public function getIndustryClassByGroup($groupId)
    {
        return Industry::getIndustryClassByGroup($groupId);
    }
    
     /**
     * get all industry division
     *
     * @return type
     */
    public function getIndustryDivision()
    {
        return Industry::getIndustryDivision();
    }
    
    /**
     * get industry data
     *
     * @param array $conditions
     * @return type
     */
    public function getIndustryData($conditions = [], $select = [])
    {
        return Industry::getIndustryData($conditions, $select);
    }
    
    /**
     * 
     * @param type $conditions
     * @param type $select
     * @return type
     */
    public function updateTrackUser($session_id = null, $arrData = [])
    {
        return TrackUser::updateTrackUser($session_id, $arrData);
    }
    
    
    /**
     * get all promo code
     * 
     * @param array $attributes
     * @return type
     */
    public function getAllConditionalPromoCode($attributes = [])
    {
        return PromoCode::where('promo_code', $attributes['promo_code'])->first();
    }
    
    /**
     * update master promo code
     * 
     * @param type $conditions
     * @param type $select
     * @return type
     */
    public function updatePromoCode($inputArr = [], $where = [])
    {
        return PromoCode::updatePromoCode($inputArr, $where);
    }
    
    /**
     * update master promo code
     * 
     * @param type $conditions
     * @param type $select
     * @return type
     */
    public function saveUserPromoCode($app_user_id = null, $app_id = null, $arrData = [])
    {
        return UserPromoCode::saveUserPromoCode($app_user_id, $app_id, $arrData);
    }
    
    /**
     * Get all active industry data
     */
    public function getAllIndustryData(){
        return Industry::getAllIndustryData();
    }
     
    /**
     * save equifax response cron log
     * 
     * @param array $arrData
     * @return type
     */
    public function saveEquifaxResCronLog($arrData = [])
    {
        return EquifaxResponseCronLog::saveEquifaxResCronLog($arrData);
    }
    
    /**
     * check equifax res file
     * 
     * @param array $where
     * @return type
     */
    public function checkEquifaxResFile($where = [])
    {
        return EquifaxResponseCronLog::checkEquifaxResFile($where);
    }
    
    /**
     * save funded monitoring report data
     * 
     * @param array $arrData
     * @return type
     */
    public function saveMonthlyMonitoringReportData($arrData = [], $where = [])
    {
        return FundedMonitoringReport::saveMonthlyMonitoringReportData($arrData, $where);
    }
    
    /**
     * get all funded report
     * 
     * @param array $arrData
     * @return type
     */
    public function getAllFundedReport($where = [])
    {
        return FundedMonitoringReport::getAllFundedReport($where);
    }
    
    /**
     * Get all active pre qualifying question
     * 
     * @param array $where
     * @param array $select
     * @return type mixed
     */
    public function getConditionalQualifyingQuestions($where = [], $select = []){
        return PreQualifyingQuestions::getConditionalQualifyingQuestions($where, $select);
    }
    
    /**
     * get Audit report
     * 
     * @param array $arrData
     * @return type
     */
    public function getAuditReports($where = [])
    {
        return AuditReports::getAuditReports($where);
    }
    /**
     * get Mi report
     * 
     * @param array $arrData
     * @return type
     */
    public function getMiReports($where = [])
    {
        return MiReports::getMiReports($where);
    }
}