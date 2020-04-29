<?php

namespace App\B2c\Repositories\Entities\Application;

use App\B2c\Repositories\Models\Question;
use App\B2c\Repositories\Models\Master\Zipcode;
use App\B2c\Repositories\Models\Master\City;
use App\B2c\Repositories\Models\Application;
use App\B2c\Repositories\Models\OwnerFormAccess;
use App\B2c\Repositories\Models\AppLoanPurpose;
use App\B2c\Repositories\Models\ApplicationOwner; 
use App\B2c\Repositories\Models\ApplicationLeadOwnerInfo; 
use App\B2c\Repositories\Models\ApplicationCaseOwnerInfo; 
use App\B2c\Repositories\Models\ApplicationUserRole; 
use App\B2c\Repositories\Models\ShareLead;
use App\B2c\Repositories\Models\Iovation;
use App\B2c\Repositories\Models\ShareApp;
use Biz2Credit\Equifax\Models\EquifaxAddress;
use Biz2Credit\Equifax\Models\EquifaxBankruptcy;
use Biz2Credit\Equifax\Models\EquifaxTrades;
use Biz2Credit\Equifax\Models\EquifaxEmployment;
use Biz2Credit\Equifax\Models\EquifaxReqRes;
use App\B2c\Repositories\Models\Master\AppStatus;
use App\B2c\Repositories\Models\ApplicationStatusLog; 
use App\B2c\Repositories\Models\ApplicationNotes; 
use App\B2c\Repositories\Models\Master\AnnualSales;
use App\B2c\Repositories\Models\AppQuestions;
use App\B2c\Repositories\Models\CreditLimit;
use App\B2c\Repositories\Models\KnockoutReference;
use App\B2c\Repositories\Models\ApplicationBusiness;
use App\B2c\Repositories\Models\Master\LineOfPurpose;
use App\B2c\Repositories\Contracts\ApplicationInterface;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Contracts\Traits\CommonRepositoryTraits;
use Biz2Credit\ESC\Repositories\Models\EscData;
use App\B2c\Repositories\Models\ApplicationOffer;
use App\B2c\Repositories\Models\ApplicationStatus;
use App\B2c\Repositories\Models\GlobalDscr;
use App\B2c\Repositories\Models\ApplicationAccounts;
use App\B2c\Repositories\Models\ApplicationAccountsLog;
use App\B2c\Repositories\Models\DecisionReasonCode;
use App\B2c\Repositories\Models\Master\DecisionReason;
use App\B2c\Repositories\Models\DecisionReasonCodeLog;
use App\B2c\Repositories\Models\CATransaction;
use App\B2c\Repositories\Models\ApplicationOwnerLog;
use App\B2c\Repositories\Models\Master\UserRoles;
use App\B2c\Repositories\Models\Master\State;
use App\B2c\Repositories\Models\ApplicationDocument;
use App\B2c\Repositories\Models\Master\Products;
use App\B2c\Repositories\Models\Master\InterestRate;
use App\B2c\Repositories\Models\Master\InterestRateType;
use App\B2c\Repositories\Models\Master\RepaymentMode;
use App\B2c\Repositories\Models\Monitoring;
use App\B2c\Repositories\Models\EmailSend;
use App\B2c\Repositories\Models\AppIndustryLog;
use App\B2c\Repositories\Models\SkipCreditBureauLog;
use App\B2c\Repositories\Models\SecuredUnsecuredCredit;
use App\B2c\Repositories\Models\Master\LoanSecurity;
use App\B2c\Repositories\Models\Master\InterestRateRel;
use App\B2c\Repositories\Models\AppealReason;
use App\B2c\Repositories\Models\CustomerCreditBureauInfo;
use App\B2c\Repositories\Models\Master\CancelAppReason;
use App\B2c\Repositories\Models\Master\UWApprovingReasons;
use App\B2c\Repositories\Models\ApplicationOfferRel;
use App\B2c\Repositories\Models\ApplicationOfferColletralPledge;
use App\B2c\Repositories\Models\AmountProductBifurcation;

/**
 * Application repository class
 */
class ApplicationRepository extends BaseRepositories implements ApplicationInterface
{

    use CommonRepositoryTraits;
    /**
     * Owner repository
     *
     * @var object
     */
    public $owner;

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
        return Application::saveApplication($attributes);
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
        return Application::updateApplication((int) $app_id, $attributes);
    }

    /**
     * Get all records method
     *
     * @param array $columns
     */
    public function all($columns = array('*'))
    {
        return Application::all($columns);
    }

    /**
     *  Save Sin For Owner
     * 
     * @param Integer $app_id
     * @param array $owner_data
     * @return integer
     */
    public function saveSin($owner_id, $owner_data)
    {
        return ApplicationOwner::saveSin((int) $owner_id, $owner_data);
    }
    /**
     *  Get Sin For Owner
     * 
     * @param Integer $app_id
     * @param Integer $owner_id
     * @return string|boolean
     */
    public function getSin($app_id, $owner_id)
    {
        return ApplicationOwner::getSin((int) $app_id, (int) $owner_id);
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
        $varApplicationData = Application::find((int) $id, $columns);

        return $varApplicationData;
    }
    
    /**
     * Save Business Information
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function saveBusinessBasicInfo($business_data, $app_user_id = null, $app_id = null)
    {
        return ApplicationBusiness::saveBusinessBasicInfo($business_data, (int)$app_user_id, (int) $app_id);
    }
    /**
     * Save Business Information
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function saveOwnerInfo($owner_data, $owner_id = null)
    {
        return ApplicationOwner::saveOwnerInfo($owner_data, (int) $owner_id);
    }
    
     /**
     * Save Application Information
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function saveApplication($application_data)
    {
        return Application::saveApplication($application_data);
    }
    
     /**
     * Save Application Information
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function getBizByBizId($application_data)
    {
        return ApplicationBusiness::getBizByBizId($application_data);
    }
    
    /**
     * Save Application Information
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function saveQuestionsmapping($application_data)
    {
        return AppQuestions::saveQuestionsmapping($application_data);
    }

    /**
     * @return array of loan purpose
     */
    public function getAllPurposeOfLoan()
    {
        return LineOfPurpose::getAllPurposeOfLoan();
    }
    
    /**
     * @return save loan Purpose
     */
    public function saveLoanPurposeInfo($loanData)
    {
        return AppLoanPurpose::saveLoanPurpose($loanData);
    }
    
    /**
     * @return array of annual sales
     */
    public function getAnnualSales()
    {
        return AnnualSales::getAllAnnualSales();
    }
    
    /**
     * Update Annual Sales
     */
    public function updateAnnualSales($app_id, $arrAppData)
    {
        return Application::updateAppData((int)$app_id, $arrAppData);
    }
    
    /**
     * Update Borrower Loan Amount
     */
    public function updateBorrowerLoanAmount($app_id, $arrAppData)
    {
        return Application::updateAppData((int)$app_id,$arrAppData);
    } 
    
     /**
     * Update Borrower Loan Amount
     */
    public function updateAppData($app_id, $arrAppData)
    {
        return Application::updateAppData((int)$app_id,$arrAppData);
    }
    
    /**
     * Get application Owners
     * 
     * @param type $app_id
     * @return type
     */
    public function getOwnerDetailById($owner_id)
    {
        return ApplicationOwner::getOwnerDetailById((int) $owner_id);
    }
    /**
     * Get application Owners
     * 
     * @param type $app_id
     * @return type
     */
    public function checkOwnerCount($app_id)
    {
        return ApplicationOwner::checkOwnerCount((int) $app_id);
    }
    
    /**
     * create email access form by id
     *
     * @param integer $guarantor_id
     *
     * return mixed
     */
    public function createEmailAccessForm($form_data)
    {
        return OwnerFormAccess::saveEmailForm($form_data);
    }
    
    /**
     * Get latest email access form detail
     *
     * @param integer $guarantor_id
     *
     * return mixed
     */
    public function getLatestEmailAccessForm($owner_id , $app_id)
    {
        return OwnerFormAccess::getLatestEmailAccessForm( (int) $owner_id, (int) $app_id);
    }
    
    
    
    /**
     * Get email access form detail by id
     *
     * @param integer $access_id
     *
     * return mixed
     */
    public function getEmailAccessFormById($access_id)
    {
        return OwnerFormAccess::getAccessFormById((int) $access_id);
    }
    
    /**
     * Save Guarantor information Method.
     *
     * @param array $guarantorData
     *
     * @throws InvalidDataTypeExceptions
     * @throws BlankDataExceptions
     * @return Boolean
     */
    public function saveGuarantorInfo($guarantorData, $guarantor_id)
    {
        // Check Data is Array
        if (!is_array($guarantorData)) {
            throw new InvalidDataTypeExceptions('Please send an array');
        }

        // Check Data is not blank
        if (empty($guarantorData)) {
            throw new BlankDataExceptions('No Data Found');
        }

        return ApplicationOwner::saveOwnerInfo($guarantorData, (int) $guarantor_id);
    }
    
    /**
     * update email access form by id
     *
     * @param integer $guarantor_id
     *
     * return mixed
     */
    public function UpdateEmailAccessForm($form_data, $access_form_id,$type=null)
    {
        return OwnerFormAccess::UpdateEmailForm($form_data, (int) $access_form_id,$type=null);
    }
    
     /**
     * Get Main Gurantor by app_id
     *
     * @param integer $app_id
     *
     * @return mixed Array | Boolean false
     */
    public function getMainGurantorByAppid($app_id)
    {
        return ApplicationOwner::getMainOwnerByAppid((int) $app_id);
    }
    /* 
     * Get application Owners
     * 
     * @param type $app_id
     * @return type
     */
    public function getOwnerInfoByAppId($app_id)
    {
        return ApplicationOwner::getOwnerInfoByAppId((int) $app_id);
    }
    
    /**
     * Get all application list
     * @return array of applications list
     */
    public function getApplicationList()
    {
         return Application::getApplicationList();
    }
    
     /**
     * @return get loan Purpose by app_id & user_id
     */
    public function getLoanPurposeInfo($whereArr = [], $select = [])
    {
        return AppLoanPurpose::getLoanPurposeByID($whereArr, $select);
    }
    
    /**
     * @return get loan Purpose by app_id & user_id
     */
    public function deleteLoanPurposeInfo($user_id,$app_id)
    {
        return AppLoanPurpose::deleteLoanPurposeInfo((int)$app_id,(int)$user_id);
    }

    /**
     * get city by postcode
     *
     * @param string $searchtxt
     * @param int $record_count
     * @return type
     */
    public function getCityStateByPostcode($searchtxt, $record_count)
    {
        return Zipcode::getCityStateByPostcode($searchtxt, $record_count);
    }
    
    /**
     * get city by postcode
     *
     * @param string $searchtxt
     * @param int $record_count
     * @return type
     */
    public function getStatePostcodeByCity($searchtxt, $record_count)
    {
        return City::getStatePostcodeByCity($searchtxt, $record_count);
    }
    
    /**
     *  Get Latest Application*
     *
     * @param integer $user_id User ID
     */
    public function getLatestAppFrontend($user_id)
    {
        return Application::getLatestAppFrontend((int) $user_id);
    }
    
    /**
    * Save lead owner info
    * 
    */    
    public function saveLeadOwner($leadOwnerArr)
    {
        return ApplicationLeadOwnerInfo::saveLeadOwnerInfo($leadOwnerArr);
    }
    /**
    * Save Case Owner info
    * 
    */    
    public function saveCaseOwner($caseOwnerArr)
    {
        return ApplicationCaseOwnerInfo::saveCaseOwnerInfo($caseOwnerArr);
    }
    /**
    * Save Share lead info
    * 
    */    
    public function saveShareLeadInfo($shareLeadInfo)
    {
        return ShareLead::saveShareLeadInfo($shareLeadInfo);
    }
    /**
    * Save Share Case info
    * 
    */    
    public function saveShareCaseInfo($shareCaseInfo)
    {
        return ShareApp::saveShareCaseInfo($shareCaseInfo);
    }
    /**
    * Get application and business data
    * 
    */
    public function getApplicationBusiness($app_user_id)
    {
        return Application::getApplicationBusiness($app_user_id);
    }

    /**
    * Save User Role info
    * 
    */    
    public function saveUserRole($userData)
    {
        return ApplicationUserRole::saveUserRoleInfo($userData);

    }
    /**
    * Get business information
    * 
    */
    public function getBizInformation($app_user_id, $app_id, $res_data_id = null)
    {
        return ApplicationBusiness::getBizInformation($app_user_id, $app_id, $res_data_id);
    }
    
    /**
    * Get owner information
    * 
    */
    public function getOwnerInfoByFirstAppId($app_id)
    {
        return ApplicationOwner::getOwnerInfoByFirstAppId($app_id);

    }
    
    public function getApplicationByID($user_id,$app_id)
    {
        return Application::getApplicationByID($user_id,$app_id);
    }
    
    public function updateApplication($app_id,$arrAppData)
    {
        return Application::updateApplication($app_id,$arrAppData);
    }
    
    /**
     * Get a application details 
     * @param int $user_id, $app_id
     * @return array
     */
    public function getAppDetail($attribute = [], $relations = []) {
        $result = Application::getAppDetail($attribute, $relations);
        return $result ?: false;
    }
    
    
    /**
     * Get a application details 
     * @param int $user_id, $app_id
     * @return array
     */
    public function getApplicationNotes($attribute = []) {
        $result = ApplicationNotes::getApplicationNotes($attribute);
        return $result ?: false;
    }
    
    /**
     * Get a application details 
     * @param int $app_id
     * @return array
     */
    public function getAppStatusLog($app_id) { 
        $result = ApplicationStatusLog::getAppStatusLog( (int) $app_id);
        return $result ?: false;
    }
    
    /**
     * @return array of applications
     */
    public function getAppStatus()
    {
       return AppStatus::getAllAppStatus();
    }

     /**
     * Save case note
     * @param array $attribute
     * @return id
     */
    public function saveCaseNotes($attribute = []) {
        $result = ApplicationNotes::saveCaseNotes($attribute);
        return $result ?: false;
    }
    
    /**
     * Get business details
     * @param array $attribute
     * @return id
     */
    public function getApplicationConclusion($app_user_id) {
         return Application::getApplicationConclusion($app_user_id);
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
     *  Delete owner based on owner id
     * 
     * @param array $arrData
     * @return integer
     */
    public function deleteOwnerbyOwnerId($owner_id)
    {
        return ApplicationOwner::deleteOwnerbyOwnerId($owner_id);
    }
    
    /**
     * Count owner based on owner id
     * 
     * @param array $arrData
     * @return integer
     */
    public function countSendEmailForGuarantor($owner_id ,  $app_id)
    {
        return OwnerFormAccess::countSendEmailForGuarantor($owner_id ,(int) $app_id);
    }
    
    /**
     * Update owner based on owner id
     * 
     * @param array $owner_id,$arrData
     * @return integer
     */
    public function UpdateByOwnerId($owner_id,$arrOwnerData)
    {
        return OwnerFormAccess::UpdateByOwnerId($owner_id,$arrOwnerData);
    }
    
    /**
     * To get the sum of owner percentage
     * 
     * @param integer $app_id
     * return integer
     */
    public function sumOwnerPercentage($app_id)
    {
        return ApplicationOwner::sumOwnerPercentage($app_id);
    }

    
    /**
     * Get User Application
     *
     * @param  Integer $user_id
     * @return array or Boolean
     */
    public function getUserApplication($app_data)
    {
        return Application::getAppDetail($app_data);
    }


    
    /**
     * Get Main Primary Owner by app_id
     *
     * @param integer $app_id
     *
     * @return mixed Array | Boolean false
     */
    public function getOwnerDetailByAppid($attribute = [])
    {
        return ApplicationOwner::getOwnerDetailByAppid($attribute);
    }
    
    /**
     * Get Additional Owner by app_id
     *
     * @param integer $app_id
     *
     * @return mixed Array | Boolean false
     */
    public function getAdditionalOwnerByAppid($attribute = [])
    {
        return ApplicationOwner::getAdditionalOwnerByAppid($attribute);
    }

    /**
     * get owner data w.r.t. attributes
     * 
     * @param array $attribute
     * @param array $select
     * @return type
     * @throws InvalidDataTypeExceptions
     */
    public function getAllConditionalOwnerData($attributes = [], $select = ['*'], $relations = [])
    {
        return ApplicationOwner::getAllConditionalOwnerData($attributes, $select, $relations);
    }
    
    /**
     * get all application data
     */
    public function getAllApplicationData($attributes = [], $select = [], $relations = [])
    {
        return Application::getAllApplicationData($attributes, $select, $relations);
    }
   
    /**
     * Count total applications
     * @param type $app_status
     * @return type
     */ 
    public function countTotalApp($app_status)
    {
        return Application::countTotalApp($app_status);
    }
    
    /**
     * Count total progress app
     * @param type $current_date,$start_date,$app_status
     * @return type
     */
    public function countTotalProgressApp($current_date,$start_date,$app_status)
    {
        return Application::countTotalProgressApp($current_date,$start_date,$app_status);
    }
    
     /**
     * Count total doc app
     * @param type $current_date,$start_date,$app_status
     * @return type
     */
    public function countTotalDocApp($current_date,$start_date,$app_status)
    {
        return Application::countTotalDocApp($current_date,$start_date,$app_status);
    }
    
    /**
     * Count count status app
     * @param type $current_date,$start_date,$app_status
     * @return type
     */
    public function countStatusApp($current_date,$start_date,$app_status=[])
    {
        return Application::countStatusApp($current_date,$start_date,$app_status);
        
    }
    
    public function countManualReview($current_date,$start_date,$cur_status)
    {
        return Application::countManualReview($current_date,$start_date,$cur_status);
        
    }
    
    /**
    * 
    * Get Esc Information
    * 
    */    
    public function getEscInformation($attributes = [])
    {
        return EscData::businessInfoAlreadyExist($attributes);
    }
    
    /**
    * Get Equifax Address
    * 
    * @param array $whereArr,$select
    * @return mixed Array | Boolean false 
    */ 
    public function getAddress($whereArr = [] , $select = [])
    {
        return Equifax::getAddress($whereArr,$select);
    }
    
    /**
    * Get Equifax Employment Data
    * 
    * @param array $whereArr,$select
    * @return mixed Array | Boolean false
    */ 
    public function getEquifaxEmploymentData($whereArr = [] , $select = [])
    {
        return EquifaxEmployment::getEquifaxEmploymentData($whereArr,$select);
    }
    
    public function countTrades($whereArr = [])
    {
        return EquifaxTrades::countTrades($whereArr);
    }
    
    public function countInquires()
    {
        return EquifaxReqRes::countTrades($whereArr);
    }
    
    /**
     * limit calculation data
     * 
     * @param array $where
     */
    public function getDebtCapacity($where = [])
    {
        return \App\B2c\Repositories\Models\DebtCapacity::getDebtCapacity($where);
    }
    
     /**
     * save credit limit
     * 
     * @param array $where
     */
    public function saveCreditLimit($where = [], $arrData = [])
    {
        return CreditLimit::saveCreditLimit($where, $arrData);
    }
    
   /** 
    * Get Esc Data From b2c_app_biz table
    * @param type $attributes
    * @return type
    */    
    public function getAppBizData($where = [] , $select = []){
        return ApplicationBusiness::getAppBizData($where, $select);
    }

    /**
     * Get Zipcode info like city_id & state_id
     * @param type $whereArr
     * @param type $select
     * @return type
     */
    public function getZipcodeInfo($whereArr = [],$select = []) { 
        
        return Zipcode::getZipcodeInfo($whereArr, $select);
    }

    /**
     * Save Iovation details to db
     * @param type $arrAppData
     * @return type
     */
    public function saveIovationDetails($arrAppData = [])
    {
        return Iovation::saveIovationDetails($arrAppData);

    }
    
    public function getIovationData($whereArr = [] , $select = [])
    {
         return Iovation::getIovationData($whereArr , $select);
    }
    
    /**
     * Save application offer data
     *
     * @param array $arrayInfo
     * @param integer $offer_id
     *
     * @return mixed integer or boolean
     *
     * @since 0.1
     */
    public function saveOffer(array $arrayInfo, $offer_id = null)
    {
        return empty($offer_id) ? ApplicationOffer::saveOffer($arrayInfo) : ApplicationOffer::find($offer_id)->update($arrayInfo);
    }
    
    /**
     * Update offer data
     * 
     * @param int $app_id
     * @param array $offerData
     * @return type
     */
    public function updateOfferData($app_id, array $offerData, $is_current_offer = null)
    {
        return ApplicationOffer::updateOfferData($app_id, $offerData, $is_current_offer);
    }
    
    
    
    /**
     * Get offer data
     * 
     * @param integer $app_id
     * @return type
     */
    public function getofferData($app_id, $is_current_offer = null, $relations = [])
    {
        return ApplicationOffer::getOffer($app_id, $is_current_offer, $relations);
    }
    
    /**
     * Get offer data
     * 
     * @param integer $app_id
     * @return type
     */
    public function getLatestOffer($app_id)
    {
        return ApplicationOffer::getLatestOffer((int) $app_id);
    }
    
    /**
     * Get Offer Details
     *
     * @param integer $offer_id
     * @return type
     */
    public function offerData($offer_id) {
        return ApplicationOffer::find((int) $offer_id);
    }
    
    /**
     * Get application owners
     *
     * @param integer $appId
     * @return mixed integer or boolean
     */
    public function getApplicationOwners($appId)
    {
        return Application::getApplicationOwners((int) $appId);
    }
    
     /**
     * Get offer detail
     * 
     * @param array $attributes
     * @param array $select
     * @return type
     */
    public function getOfferDetail($attributes = [], $select = [], $orderBy = false, $relations = [])
    {
        return ApplicationOffer::getOfferDetail($attributes, $select, $orderBy, $relations);
    }
    
    /**
     * Check case already shared or not to selected team memeber
     * @param integre $lead_id
     * @param integre $to_id
     * @return boolean
     */
    public function isCaseAlreadyShared($app_user_id, $app_id, $to_id)
    {
        return ShareApp::isCaseAlreadyShared($app_user_id, $app_id, $to_id);
    }
    
    /**
     * get all manual status
     * @param int $status_type
     * @return obj
     */
    public function getStatusListByLang($status_type)
    {
        return ApplicationStatus::getStatusListByLang($status_type);
    }
    
    /**
     * get all lead status
     *
     * @return object
     */
    public function getAllStatus()
    {
        return ApplicationStatus::getAllStatus();
    }
    
    /**
     * Save application offer data
     *
     * @param array $arrayInfo
     * @param integer $offer_id
     *
     * @return mixed integer or boolean
     *
     * @since 0.1
     */
    public function saveTempIncomeLiability($arrayInfo = [], $id = null)
    {
        \App\B2c\Repositories\Models\DebtCapacity::saveTempIncomeLiability($arrayInfo, $id);
    }
    
    /**
     * Get status name by status id
     *
     * @param integer $status id
     *
     * @return mixed integer or boolean
     *
     * @since 0.1
     */
    public function getStatusNameByStatusId($status_id)
    {
        return ApplicationStatus::getStatusNameByStatusId($status_id); 
    }
    
    /**
     * Get Global Dscr data by app_id
     *
     * @param int $app_id
     *
     * @return array
     */
    public function getGlobalDscrData($app_id)
    {
        return GlobalDscr::getGlobalDscr((int) $app_id);
    }
    
    /**
     * Save Global DSCR Detail
     *
     * @param integer $app_id
     * @param array $arrayInfo
     * @return type
     */
    public function saveGlobaldscr(array $arrayInfo)
    {
        return GlobalDscr::saveGlobalDscr($arrayInfo);
    }
    
    /**
     * Save Global DSCR Detail
     *
     * @param integer $app_id
     * @param array $arrayInfo
     * @return type
     */
    public function deleteGlobalDscr($app_id)
    {
        return GlobalDscr::deleteGlobalDscr((int) $app_id);
    }
    
     /**
     * Save account number
     * @param array $attribute
     * @return id
     */
    public function saveAccountNo($attribute = []) {
        $result = ApplicationAccounts::saveAccountNo($attribute);
        return $result ? true : false;
    }
    
    /**
     * Get account numbers 
     * @param int $user_id, $app_id
     * @return array
     */
    public function getAccountNumbers($whereArr = [], $select=[]) {
        return ApplicationAccounts::getAccountNumbers($whereArr, $select);
    }
    
    /**
     * Save multiple decision code
     *
     * @param array $arrData
     * @return type boolean
     */
    public function saveDecisionReasonCode($arrData = [])
    {
        return DecisionReasonCode::saveDecisionReasonCode($arrData);
    }
    
    /**
     * get decision reference code
     *
     * @param array $arrData
     * @param array $select
     * @return type boolean
     */
    public function getDecisionReasonCode($arrData = [], $select = [])
    {
        return DecisionReasonCode::getDecisionReasonCode($arrData, $select);
    }
    
    /**
     * Update Global DSCR Detail
     *
     * @param integer $app_id
     * @param array $arrData
     * @return type
     */
    public function updateDscr($app_id, $arrData)
    {
        return GlobalDscr::updateDscr((int) $app_id, $arrData);
    }
    
    /**
     * Save application offer data
     *
     * @param array $arrayInfo
     * @param integer $app_id
     *
     * @return mixed integer or boolean
     *
     * @since 0.1
     */
    public function saveDebtCapacityByAppId($arrayInfo = [], $app_id = null)
    {
        \App\B2c\Repositories\Models\DebtCapacity::saveDebtCapacityByAppId($arrayInfo, $app_id);
    }
    
    /**
     * get the decision reason value
     * 
     * @return type
     */
    public function getDecisionReason() 
    {
        return DecisionReason::getDecisionReason();
    }
    
    /**
     * Delete account numbers 
     * @param int $user_id, $app_id
     * @return boolean
     */
    public function deleteAccountNumbers($whereArr = []) {
        return ApplicationAccounts::deleteAccountNumbers($whereArr);
    }
    
    /**
     * @return get loan Purpose by app_id & user_id
     */
    public function getFirstLoanPurposeInfo($whereArr = [], $select = [])
    {
        return AppLoanPurpose::getFirstLoanPurposeByID($whereArr, $select);
    }
    /**
     * get total count of bankruptcy
     *
     * @return object
     */
     public function countBankruptcy($whereArr)
    {
        return EquifaxBankruptcy::countBankruptcy($whereArr);
    }
    
    /**
     *  delete decision reason code
     *
     * @param array $where
     * @return type boolean
     */
    public function deleteDecisionReasonCode($where = [])
    {
        return DecisionReasonCode::deleteDecisionReasonCode($where);
    }
    
    /**
     * Save multiple decision code
     *  get app details by APPID
     *
     * @param array $app_id
     * @return type array
     */
    public function getAppDetailsById($app_id)
    {
        return Application::find($app_id);
    }
    
    /**
     *  get all B2C category
     *
     * @return type array
     */
    public function getB2CCategory()
    {
        return \Biz2Credit\Yodlee\Repositories\Models\FinanceDataB2CTransCategory::select('*')->orderBy('cat_name')->get();
    }
    
    /**
     *  save CA bank transactions
     *
     * @param array $arrData
     * @return type boolean
     */
    public function saveDecisionReasonCodeLog($arrData = [])
    {
        return DecisionReasonCodeLog::saveDecisionReasonCodeLog($arrData);
    }
    
     /**
     * Save account number Log
     * @param array $attribute
     * @return id
     */
    public function saveAccountNoLog($attribute = []) {
        $result = ApplicationAccountsLog::saveAccountNoLog($attribute);
        return $result ? true : false;
    }
    
    /**
     * Get account numbers Log 
     * @param int $user_id, $app_id
     * @return array
     */
    public function getAccountNumbersLog($whereArr = [], $select=[]) {
        return ApplicationAccountsLog::getAccountNumbersLog($whereArr, $select);
    }
    
    /**
     * Get additional owner details
     * @param int $user_id, $app_id
     * @return array
     */
    public function getAppOwnerDetail($whereArr = [], $select=[], $offset=NULL) {
        return ApplicationOwner::getAppOwnerDetail($whereArr, $select, $offset);
    }
    
    /**
     * Get auto decision log
     * 
     * @param type $where
     * @param type $select
     * @return type
     */
    public function getAutoDecisionLog($where = [], $select = [])
    {
        return DecisionReasonCodeLog::getAutoDecisionLog($where, $select);
    }
    
    /**
     * Get offer data
     * 
     * @param integer $app_id
     * @return type
     */
    public function getManualOffer($app_id)
    {
        return ApplicationOffer::getManualOffer($app_id);
    }
     
    /**
     *  save CA transaction 
     * 
     * @param type $arrData
     * @return type
     */
    public function saveCATransactions($arrData)
    {
        return CATransaction::saveCATransactions($arrData);
    }
    
    /**
     *  check transactions exist or not
     *
     * @param array $transactionId
     * @return type boolean
     */
    public function checkCATransactions($transactionId)
    {
        return CATransaction::checkCATransactions($transactionId);
    }
    /**
     *  check transactions exist or not
     *
     * @param array $transactionId
     * @return type boolean
     */
    public function updateCATransactions($arrData, $transactionId)
    {
        return CATransaction::updateCATransactions($arrData, $transactionId);
    }
    
    /**
     *  Update Yodlee transaction
     *
     * @param string $query
     * @return type boolean
     */
    public function updateYodleeTransaction($query)
    {
        return \Biz2Credit\Yodlee\Repositories\Models\FinanceDataTransaction::updateYodleeTransaction($query);
    }

     /**
     * Delete app owner form access by owner id
     * 
     * @param int $owner_id
     * @return type boolean
     */
    public function deleteAppOwnerFormAccessbyOwnerId($owner_id)
    {
        return OwnerFormAccess::deleteAppOwnerFormAccessbyOwnerId($owner_id);
    }
    
    /**
    * get all user roles
    * 
    */    
    public function getAllRoleData()
    {
        return UserRoles::getAllRoleData();
    }
    
    /**
    * get all states
    * 
    */    
    public function getAllStateData()
    {
        return State::getAllStateData();
    }
    
    /**
     * Get application id by user id
     * 
     * @param type $user_id
     * @return type
     * @throws InvalidDataTypeExceptions
     */    
    public function getYodleeRegApplications()
    {
        return Application::getYodleeRegApplications();
    }
    
     /**
     * Get All uploaded native pdf details
     * 
     * @param integer $app_id
     * @return mixed
     */
    public function getAllUploadedNativePdfDetails($app_id)
    {
        return ApplicationDocument::getAllUploadedNativePdfDetails($app_id);
    }    

    /**
     * update offer data
     * 
     * @param integer $offer_id
     * @param array $attributes
     * return integer
     
    public function updateOfferData($offer_id = null, $attributes = [])
    {
        return ApplicationOffer::updateOfferData($offer_id, $attributes);
    }
    
    /** get case owner log
     * 
     * @param type $caseOwnerArr
     * @return type
     */
    public function getCaseOwnerLog($attributes = [], $select = [])
    {
        return ApplicationCaseOwnerInfo::getCaseOwnerLog($attributes, $select);
    }
    
    /**
     * Save Owner Information in log
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function saveOwnerLogInfo($owner_data)
    {
        return ApplicationOwnerLog::saveOwnerLogInfo($owner_data);
    }
    
    /**
     * Get monitoring details
     * 
     * @param type $where
     * @param type $select
     */
    public function getMonitoringDetails($where = [], $select = []){
        return Monitoring::getMonitoringDetails($where, $select);
    }
    
    /**
     * Count total applications
     * @param type $app_status
     * @return type
     */ 
    public function countAllApp()
    {
        return Application::countAllApp();
    }
    
    
    /**
     * get application count
     * 
     * @param array $attribute
     * @return mixed
     */
    public function getAppCount($attribute)
    {
       return Application::getAppCount($attribute); 
    }
    
    /**
     * get application question data
     * @param array $attribute
     * @param array $select
     * @return mixed
     */
    public function getAppQuestionData($attribute, $select = [])
    {
        return AppQuestions::getAppQuestionData($attribute, $select);
    }
    
    /**
     * Get selected app details
     * 
     * @param array $attribute
     * @param array $select
     * @return mixed    
     */
    public function getAppData($attribute, $select = [])
    {
        return Application::getAppData($attribute, $select);
    }
    
    
    public function getProductByPurpose($attributes)
    {
      return Products::getProductByPurpose($attributes);
        
    }
    
    /**
     * Get All interest rate 
     *
     * @return mixed
     */
    public function getAllInterestRateList()
    {
      return InterestRateType::getAllInterestRateList();   
    }
    
    
    /**
     * Get All interest rate 
     *
     * @return mixed
     */
    public function getAllRepamentList()
    {
      return RepaymentMode::getAllRepamentList();   
    }
    
    /**
     * Get total  approved amount
     * 
     * @param array $attributes
     * @return mixed
     */
    public function getTotalApprovedAmount($attributes)
    {
        return Application::getTotalApprovedAmount($attributes);
    }
    
    /**
     * Get total funded amount
     * 
     * @param array $attributes
     * @return mixed
     */
    public function getTotalFundedAmount($attributes)
    {
        return Application::getTotalFundedAmount($attributes);
    }
    /*
     * check knockOut status
     * 
     * @return mixed
     */
    public function checkKnockOutApp($app_id)
    {
       return Application::checkKnockOutApp( (int) $app_id );
    }
    
 
     /**
     * get application purpose data
     * 
     * @param array $attributes
     * @param array $selected
     * @return mixed
     */
    public function getApplicationPurposeData($attributes , $selected= [])
    {
        return  AppLoanPurpose::getApplicationPurposeData($attributes, $selected);  
    }
    
    
    /**
     * Delete loan purpose by id
     * 
     * @param int $id
     * @return mixed
     */
    public function deleteLoanPurposeById($id)
    {
        return  AppLoanPurpose::deleteLoanPurposeById($id);  
    }
    
    /**
     * @return array of loan purpose
     */
    public function getPurposeList()
    {
        return LineOfPurpose::getPurposeList();
    }
    
    
    /**
     * Delete app question data
     * 
     * @param array $attributes
     * @return  mixed 
     */
    public function deleteAppQuestions($attributes)
    {
         return AppQuestions::deleteAppQuestions($attributes);
        
    }

    /**
     * check if token Exists for  Business Information
     *
     * @param mixed $token
     */
    public function checkIfTokenExpire($token)
    {
        return ApplicationBusiness::checkIfTokenExpire($token);
    }
    
   /**
     * Get latest email access form detail
     *
     * @param integer $guarantor_id
     *
     * return mixed
     */
    public function getLatestEmailAccessbyuserID($user_id,$type=null)
    {
        return OwnerFormAccess::getLatestEmailAccessbyuserID((int) $user_id);
    } 
    
     /**
     * Save monitoring details
     * 
     * @param type $monitoringArr
     * @return type
     */
    public function saveMonitoringDetails($monitoringArr){
        return Monitoring::saveMonitoringDetails($monitoringArr);
    }
    
    /**
     * Get knockout data
     * 
     * @param id $knockout_id
     * @param array $arrData
     * @return mixed
     */
     public function getKnockoutWithRef($knockout_id = null, $arrData = [])
    {
        return KnockoutReference::getKnockoutWithRef($knockout_id, $arrData);
    }
    
    /**
     * Get send mail data 
     * 
     * @param int $app_user_id
     * @return mixed
     */
    public function getSentEmailData($app_user_id)
    {
        return EmailSend::getSentEmailData($app_user_id);
    }

    /**
     * Get send mail data 
     * 
     * @param int $app_user_id
     * @return mixed
     */
     public function expireLinkOwnerID($owner_id,$arrOwnerData)
    {
        return OwnerFormAccess::UpdateByOwnerId($owner_id,$arrOwnerData);
    }
    
      /**
     * Get send mail data 
     * 
     * @param int $app_user_id
     * @return mixed
     */
     public function expireLinkuserID($user_id,$arrOwnerData)
    {
        return OwnerFormAccess::expireLinkuserID($user_id,$arrOwnerData);
    }
    
    /**
     * save credit bureau log
     * 
     * @param array $attributes
     * @return mixed
     */
     public function saveCreditBureauLog($attributes)
    {
        return SkipCreditBureauLog::saveCreditBureauLog($attributes);
    }
    
    /**
     * get product info by purpose id
     * 
     * @param array $attributes
     * @return type
     */
    public function getProductByPurposeId()
    {
      return Products::getProductByPurposeId();
    }
    
    /**
     *  Get unsecured amount
     * 
     * @param array $attributes
     * @return mixed
     */
    public function getUnsecuredApprovedAmount($attributes)
    {
        return Application::getUnsecuredApprovedAmount($attributes);
    }
      
    /**
     * Get unsecured  funded amount 
     * 
     * @param array $attributes
     * @return mixed
     */
    public function getUnsecuredTotalFundedAmount($attributes)
    {
        return Application::getUnsecuredTotalFundedAmount($attributes);
    }
    
    /**
     * get all business data
     * 
     * @param array $whereArr
     * @return mixed
     */    
    public function getAllAppBusinessData($whereArr = [], $select=[])
    {
        return ApplicationBusiness::getAllAppBusinessData($whereArr, $select);
    }
      
    /**
     * get business data w.r.t. attributes
     * 
     * @param array $attribute
     * @param array $select
     * @return type
     * @throws InvalidDataTypeExceptions
     */
    public function getAllConditionalBusinessData($attributes = [], $select = ['*'], $relations = [])
    {
        return ApplicationBusiness::getAllConditionalBusinessData($attributes, $select, $relations);
    }
    
    /**
     * Get account numbers 
     * @param array $arrAppId, $select
     * @return array
     */
    public function getAccountNumbersWithMultipleAppId($arrAppId=[]) {
        return ApplicationAccounts::getAllAccountNumbersByAppIds($arrAppId);
    }

    /** get product data
     * 
     * @return type
     */
    public function getProductsData($produdt_id = null)
    {
        return Products::getProductsData($produdt_id);
    }
    
    /**
     * delete sec unsecured credit data w.r.r $attributes
     * 
     * @param array $attributes
     * @return type
     */
    public function deleteSecUnsecuredCreditData($attributes = [])
    {
        return SecuredUnsecuredCredit::deleteSecUnsecuredCreditData($attributes);
    }
    
    /**
     * save credit detail data
     * 
     * @param array $attributes
     * @return type
     */
    public function saveSecuredUnsecuredCreditData($attributes = [])
    {
        return SecuredUnsecuredCredit::saveSecuredUnsecuredCreditData($attributes);
    }
    
    /**
     * get credit detail data
     * 
     * @param array $attributes
     * @return type
     */
    public function getSecUnsecuredCreditData($attributes = [], $select = [])
    {
        return SecuredUnsecuredCredit::getSecUnsecuredCreditData($attributes, $select);
    }
    
    /**
     * get email form access data
     *
     * @param array $attributes
     *
     * return mixed
     */
    public function getApprovalEmailAccessData($attributes = [])
    {
        return OwnerFormAccess::getApprovalEmailAccessData($attributes);
    }
    
    /**
     * Check Business Name Existence
     *
     * @return boolean
     * @throws InvalidDataTypeExceptions
     * @throws BlankDataExceptions
     */
    public function checkBusinessNameExistence($attributes = []) 
    {
        return ApplicationBusiness::checkBusinessNameExistence($attributes);
    }
     /**
     * update email access form by id
     *
     * @param integer $guarantor_id
     *
     * return mixed
     */
    public function UpdateEmailAccessFormData($updateArr,$app_id,$app_user_id,$consent_type)
    {
        return OwnerFormAccess::UpdateEmailAccessFormData($updateArr, (int) $app_id, (int) $app_user_id ,$consent_type);
    }
    
    /**
     * get interest type
     * 
     * @param array $attributes
     * @return type
     */
    public function getInterestRateType($attributes)
    {
      return InterestRateType::getInterestRateType($attributes);
    }
    
    /**
     * get interest type
     * 
     * @param array $attributes
     * @return type
     */
    public function getAllInterestRate($attributes)
    {
      return InterestRate::getAllInterestRate($attributes);
    }
    
    /**
     * get loan security
     * 
     * @param array $attributes
     * @return type
     */
    public function getAllLoanSecurity($attributes)
    {
      return LoanSecurity::getAllLoanSecurity($attributes);
    }
    
    /**
     * get loan security
     * 
     * @param array $attributes
     * @return type
     */
    public function getAllInterestRateData($attributes)
    {
      return InterestRate::getAllInterestRateData($attributes);
    }
    
    /**
     * get product info
     * 
     * @param array $attributes
     * @return type
     */
    public function getAllProductData($attributes = [])
    {
      return Products::getAllProductData($attributes);
    }
    
    /**
     * save offer Appeal reason
     * 
     * @param array $attributes
     * @return type
     */
    public function saveAppealReason($attributes = [])
    {
      return AppealReason::saveOfferAppealReason($attributes);
    }
   
    /**
     * Update offer data by Id
     * 
     * @param int $app_id
     * @param array $offerData
     * @return type
     */
    public function updateOfferDataExtend($app_id, array $offerData, $is_current_offer = null)
    {
        return ApplicationOffer::updateOfferDataExtend($app_id, $offerData, $is_current_offer);
    }
    /**
     * get cust credit bureau info
     * 
     * @param type $offer_id
     * @param type $attributes
     * @return type
     */
    
    public function updateOfferByID($offer_id, $attributes = null)
    {
        return ApplicationOffer::updateOfferDataByID($offer_id, $attributes);
    }
    
    
     /* get cust credit bureau info
     * 
     * @param array $attributes
     * @param array $select
     * @return type
     */
    public function getAllCustCreditBureauData($attributes = [], $select = ['*'])
    {
        return CustomerCreditBureauInfo::getAllCustCreditBureauData($attributes, $select);
    }
    
     /**
     * update account number
     * @param array $attribute
     * @return id
     */
    public function updateAccountNumber($account_id,$account_number) {
        $result = ApplicationAccounts::updateAccountNumber($account_id,$account_number);
        return $result ? true : false;
    }
        
    /**
     * @return array of cancel application reason
    */
    public function getCancelAppReason()
    {
       return CancelAppReason::getAllCancelAppReason();
    }
    
    /**
     * Get knockout user details
     * 
     * @param array $attributes
     * @param array $select
     * @return array
     */
    public function getKnockoutUserDetail($attributes = [], $select = [])
    {
        return KnockoutReference::getKnockoutUserDetail($attributes, $select);
    }
    
    /**
     * Get a application details 
     * @param int $app_id
     * @return array
     */
    public function getAppStatusLogwithKnockout($app_id) { 
        $result = ApplicationStatusLog::getAppStatusLogwithKnockout( (int) $app_id);
        return $result ?: false;
    }
        
    /**
     * get cust credit bureau info
     * 
     * @param array $attributes
     * @param array $select
     * @return type
     */
    public function getOffer($app_id)
    {
        return ApplicationOffer::getOffer($app_id);
    }
    
    /**
     * update current offer
     * @param array $select
     * @return type
     */
     public function updateOfferByOfferID($where,$arrData){
         return ApplicationOffer:: updateOfferByOfferID($where,$arrData);
     }
     
     /**
     * get all approval reasons
     * 
     * @param array $attributes
     * @return type
     */
    public function getAllApprovalReasons($attributes = [])
    {
      return UWApprovingReasons::getAllApprovalReasons($attributes);
    }
    
   
    /**
     * save offer colletral data
     * 
     * @param array $attributes
     * @return type
     */
    public function saveAmountProductBifurcation($attributes = [])
    {
        return AmountProductBifurcation::saveAmountProductBifurcation($attributes);
    }
    
    /**
     * delete amount product bifurcation
     * 
     * @param array $where
     * @return type
     */
    public function deleteAmountProductBifurcation($where = [])
    {
        return AmountProductBifurcation::deleteAmountProductBifurcation($where);
    }
    
    /**
     * update loan purpose
     * 
     * @param array $where
     * @return type
     */
    public function upateApplicationPurpose($where = [], $id = null)
    {
        return AppLoanPurpose::upateApplicationPurpose($where, $id);
    }
    
    /**
     * get amount product bifurcation
     * 
     * @param array $attributes
     * @param array $select
     * @return type
     */
    public function getAmountProductBifurcationData($attributes = [], $select = [])
    {
        return AmountProductBifurcation::getAmountProductBifurcationData($attributes, $select);
    }
    
    /**
     * save offer group data
     * 
     * @param int $offer_group_id
     * @param array $arrData
     * @return type
     */
    public function saveOfferColletralData($arrData = [])
    {
        return ApplicationOfferColletralPledge::saveOfferColletralData($arrData);
    }
    
    public function saveOfferGroupData($offer_group_id = null, $arrData = [])
    {
        return ApplicationOfferRel::saveOfferGroupData($offer_group_id, $arrData);
    }
    
    /**
     * get offer group data
     * 
     * @param array $attributes
     * @param array $select
     * @return type
     */
    public function getOfferGroupData($attributes = [], $select = [])
    {
        return ApplicationOfferRel::getOfferGroupData($attributes, $select);
    }
    
    /**
     * get offer colletral data
     * 
     * @param array $attributes
     * @param array $select
     * @return type
     */
    public function getOfferColletralData($attributes = [], $select = [])
    {
        return ApplicationOfferColletralPledge::getOfferColletralData($attributes, $select);
    }
    
    /**
     * delete offer colletral
     * 
     * @param array $attributes
     * @return type
     */
    public function deleteOfferColletralData($attributes = [])
    {
        return ApplicationOfferColletralPledge::deleteOfferColletral($attributes);
    }
    
    /**
     *  Get secured approved amount
     * 
     * @param array $attributes
     * @return mixed
     */
    public function getSecuredApprovedAmount($attributes)
    {
        return Application::getSecuredApprovedAmount($attributes);
    }
      
    /**
     * Get secured funded amount 
     * 
     * @param array $attributes
     * @return mixed
     */
    public function getSecuredTotalFundedAmount($attributes)
    {
        return Application::getSecuredTotalFundedAmount($attributes);
    }
    
    /**
     * get application industry log data
     * 
     * @param int $app_id
     * 
     * @return array
     */
    public function getApplicationIndustryLog($app_id)
    { 
      return AppIndustryLog::getApplicationIndustryLog($app_id);
    }
    /**
     * save application industry log data
     * 
     * @param array $arrData
     * @return type
     */
    public function saveApplicationIndustryLog($arrData = [])
    {
      return AppIndustryLog::saveApplicationIndustryLog($arrData);
    } 
}