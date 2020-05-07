<?php

namespace App\Http\Controllers\Contracts\traits;

use Helpers;
use Redirect;
use Equifax;
use App\Repositories\Models\ApplicationOwner;
use App\Repositories\Models\ApplicationBusiness;
use App\Http\Controllers\Contracts\Traits\PrepareDataTrait;

trait EquifaxApiTrait {

    /**
     * Creating Equifax api request param
     *
     * @param  integer $userID
     * @param  integer $applicationID
     * @param  integer $ownerID
     * @return response
     */
    public function createEquifaxOwnerReport($userID, $applicationID,$ownerBackendID) {
       
        try { 
            // Invoke Equifax Request
            if (Helpers::checkApplicationStatus($applicationID) != config('b2c_common.KNOCKED_OUT')) {
            if($ownerBackendID==''){
            $ownerInfoData = $this->application->getOwnerInfoByAppId($applicationID)->toArray();
            $invokeParam = [];
            foreach ($ownerInfoData as $key => $owner_data) {
                $ownerID = $owner_data['app_owner_id'];
                $app_id = $owner_data['app_id'];
                if ($ownerID && $owner_data['is_credit_bureau_skip'] != config('b2c_common.MANUAL_SKIP')) {
                    $ownerStatus = ApplicationOwner::getHardPullStatus($ownerID)->toArray();
                     //if ($ownerStatus[0] != 1) {
                        if(isset($owner_data['city_id'])&& !empty($owner_data['city_id'])){
                            $invokeParam['city_name'] = Helpers::getCityName($owner_data['city_id']);
                        }else{
                            $invokeParam['city_name'] = "";    
                        }
                        $invokeParam['state_key'] = '';
                        if(isset($owner_data['state_id'])&& !empty($owner_data['state_id'])){
                            $invokeParam['state_key'] = Helpers::getStateName($owner_data['state_id'])->state_key;
                        }else{
                           $invokeParam['state_key'] =$owner_data['state_key']; 
                        }
                        $invokeParam['first_name'] = $owner_data['first_name'];
                        $invokeParam['last_name'] = $owner_data['last_name'];
                        $invokeParam['dob'] = $owner_data['dob'];
                        $invokeParam['postal_code'] = !empty($owner_data['zip_code']) ? $owner_data['zip_code'] : null;
                        $invokeParam['civic_number'] = $owner_data['street_name'];
                        $invokeParam['street_name'] = $owner_data['addr_line_one'];
                       if($owner_data['status']==1 || $owner_data['is_guarantor']==1 || isset($owner_data['is_credit_bureau_skip'])){
                            if($owner_data['is_credit_bureau_skip'] == config('b2c_common.AUTO_SKIP') && isset($owner_data['prev_equifax_res_id'])) {
                                $data=Equifax::getConsumerCreditReport($userID, $applicationID, $ownerID, $invokeParam, $owner_data['prev_equifax_res_id']);
                           } else {
                                $data=Equifax::getConsumerCreditReport($userID, $applicationID, $ownerID, $invokeParam);
                           }
                            //equifax response in manual table
                            if(empty($data) || $data['Error'] == 'No response from Personal Credit Bureau.') {
                                $attribute['app_id'] = $app_id;
                                $attribute['app_owner_id'] = $ownerID;
                                $attribute['app_user_id'] = $userID;
                                $this->prepareManualEquifaxPersonalCreditBureau($attribute);
                            }
                       }else{
                            $data['Error']='Api already triggered';
                        }

                      }
                   }
                }
            }
             else{
              $ownerID = $ownerBackendID;
              $owner_data = $this->application->getOwnerDetailById($ownerID)->toArray();
              $app_id = $owner_data['app_id'];
              if ($ownerID) {
                    $ownerStatus = ApplicationOwner::getHardPullStatus($ownerID)->toArray();
                    // if ($ownerStatus[0] != 1) {
                        $invokeParam['city_name'] = $owner_data['city_name'];
                        $invokeParam['state_key'] = '';
                        if(isset($owner_data['state_id'])&& !empty($owner_data['state_id'])){ 
                        $invokeParam['state_key'] = Helpers::getStateName($owner_data['state_id'])->state_key;
                        }else{
                        $invokeParam['state_key'] = $owner_data['state_key'];
                        }
                        $invokeParam['first_name'] = $owner_data['first_name'];
                        $invokeParam['last_name'] = $owner_data['last_name'];
                        $invokeParam['dob'] = $owner_data['dob'];
                        $invokeParam['postal_code'] = !empty($owner_data['zip_code']) ? $owner_data['zip_code'] : null;
                        $invokeParam['civic_number'] = $owner_data['street_name'];
                        $invokeParam['street_name'] = $owner_data['addr_line_one'];
                        if($owner_data['status']==1  || $owner_data['is_guarantor']==1){
                        $data=Equifax::getConsumerCreditReport($userID, $applicationID, $ownerID, $invokeParam);
                        }else{
                            $data['Error']='Api already triggered';
                        }
                        //equifax response in manual table
                        if(empty($data) || $data['Error'] == 'No response from Personal Credit Bureau.') {
                            $attribute['app_id'] = $app_id;
                            $attribute['app_owner_id'] = $ownerID;
                            $attribute['app_user_id'] = $userID;
                            $this->prepareManualEquifaxPersonalCreditBureau($attribute);
                        }
                         return $data;
                      
                //}
          }
        }
      }
      catch (Exception $e) {
            return Redirect::back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }
    
        /**
     * Creating Equifax api Bussiness request param
     *
     * @param  integer $userID
     * @param  integer $applicationID
     * @return response
     */
    public function createEquifaxBussinessReportFromCustomer($userID, $applicationID) {
        try {
            // Invoke Equifax Request
            if (Helpers::checkApplicationStatus($applicationID) != config('b2c_common.KNOCKED_OUT')) {
            $bizInfoData = $this->application->getBizInformation($userID, $applicationID)->toArray();
            $zipinfo = $this->application->getZipcodeInfo(['postal_code'=>$bizInfoData['postal_code']],['state_code']);
            $zip_code=isset($zipinfo[0]['state_code']) ? $zipinfo[0]['state_code']:"";
            $invokeParam = [];
            $app_biz_id = $bizInfoData['app_biz_id'];
            $app_user_id = $bizInfoData['app_user_id'];
            $app_id = $bizInfoData['app_id'];
            $appData = $this->application->find($app_id);
            if ($bizInfoData) {
                $cityName = '';
                if(isset($bizInfoData['city_id']) && !empty($bizInfoData['city_id'])){
                     $cityName = Helpers::getCityName($bizInfoData['city_id']);
                } else{
                     $cityName = isset($bizInfoData['city_name']) ? $bizInfoData['city_name'] : null;
                } 
                if(isset($cityName)&& !empty($cityName)){
                $invokeParam['city_name'] = $cityName;
                }else{
                $invokeParam['city_name'] ="";    
                }
                $stateName = '';
                if(isset($bizInfoData['state_id']) && !empty($bizInfoData['state_id'])){
                     $stateName = Helpers::getStateNameByStateId($bizInfoData['state_id']);
                } else{
                     $stateName = isset($bizInfoData['state_name']) ? $bizInfoData['state_name'] : null;
                } 
                if(isset($stateName)&& !empty($stateName)){
                $invokeParam['state_key'] = $zip_code;
                }else{
                $invokeParam['state_key'] ="";   
                }
                $invokeParam['firm_name'] = !empty($bizInfoData['biz_name']) ? $bizInfoData['biz_name'] : null;
                $invokeParam['firm_number'] = !empty($bizInfoData['biz_number']) ? $bizInfoData['biz_number'] : null;
                $invokeParam['postal_code'] = !empty($bizInfoData['postal_code']) ? $bizInfoData['postal_code'] : null;
                $invokeParam['file_number'] = "BEST MATCH";
               }
                $bussinessStatus = ApplicationBusiness::getHardPullStatus($applicationID)->toArray();

                if($appData['is_biz_bureau_skip'] == config('b2c_common.AUTO_SKIP') && isset($appData['prev_equifax_res_id'])) {
                    $data=Equifax::getBussinessCreditReport($userID, $applicationID, $invokeParam, $app_biz_id, $appData['prev_equifax_res_id']);
                } else {
                     $data=Equifax::getBussinessCreditReport($userID, $applicationID, $invokeParam, $app_biz_id);
                }
               if(empty($data) || (!empty($data['Error']) && $data['Error'] == 'No response from Business Credit Bureau.')) {
                    //entry in credit bureau manual table i.e. b2c_business_credit_bureau_infos
                    $attribute['app_id'] = $app_id;
                    $attribute['app_biz_id'] = $app_biz_id;
                    $attribute['app_user_id'] = $app_user_id; 
                    $this->prepareManualEquifaxCreditBureauData($attribute);
                }
                return $data;
          }  
        } catch (Exception $e) {
            return Redirect::back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }
    /**
     * Creating Equifax api Bussiness request param
     *
     * @param  integer $userID
     * @param  integer $applicationID
     * @return response
     */
    public function createEquifaxBussinessReport() {

        try {
             $request       = request();
             $app_id        = (int) $request->request->get('app_id');
             $app_user_id   = (int) $request->request->get('app_user_id'); 
            // Invoke Equifax Request
            if (Helpers::checkApplicationStatus($app_id) != config('b2c_common.KNOCKED_OUT')) {
                $bizInfoData = $this->application->getBizInformation($app_user_id, $app_id)->toArray(); 
                $zip_code=!empty($request->get('state_key')) ? $request->get('state_key') : "";
                $invokeParam = [];
                $app_biz_id = $bizInfoData['app_biz_id'];
                //$invokeParam['city_name'] = !empty($request->get('city_id_show')) ? $request->get('city_id_show') : "";
                $invokeParam['city_name'] = !empty($request->get('city_name')) ? $request->get('city_name') : "";
                $invokeParam['state_key'] = $zip_code;
                $invokeParam['firm_name'] = !empty($request->get('biz_name')) ? $request->get('biz_name') : "";
                $invokeParam['file_number'] = !empty($request->get('file_number')) ? $request->get('file_number') : "BEST MATCH";
                $invokeParam['postal_code'] = !empty($request->get('postal_code')) ? $request->get('postal_code') : "";
                $bussinessStatus = ApplicationBusiness::getHardPullStatus((int) $request->get('app_id'))->toArray();
                $data=Equifax::getBussinessCreditReport($app_user_id, $app_id, $invokeParam, $app_biz_id);
                
                if(empty($data) || (!empty($data['Error']) && $data['Error'] == 'No response from Business Credit Bureau.')) {
                    //entry in credit bureau manual table i.e. b2c_business_credit_bureau_infos
                    $attribute['app_id'] = $app_id;
                    $attribute['app_biz_id'] = $app_biz_id;
                    $attribute['app_user_id'] = $app_user_id; 
                    $this->prepareManualEquifaxCreditBureauData($attribute);
                }
                return $data;
          }
        } catch (Exception $e) { 
            return Redirect::back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }
    
    
    
    
    /**
     * Creating Equifax api request param
     *
     * @param  integer $userID
     * @param  integer $applicationID
     * @param  integer $ownerID
     * @return response
     */
    public function createEquifaxOwnerReportBackend() {
       
        try {
            // Invoke Equifax Request
            $request = request();
            $ownerBackendID = (int) $request->request->get('app_owner_id');
            $app_id = (int) $request->request->get('app_id');
            $app_user_id = (int) $request->request->get('app_user_id');
            if (Helpers::checkApplicationStatus($app_id) != config('b2c_common.KNOCKED_OUT')) {

                $ownerID = $ownerBackendID;
                $owner_data = $this->application->getOwnerDetailById($ownerID)->toArray();
                if ($ownerID) {
                    $ownerStatus = ApplicationOwner::getHardPullStatus($ownerID)->toArray();
                    //$invokeParam['city_name'] = $request->request->get('city_id_show');
                    $invokeParam['city_name'] = $request->request->get('city_name');
                   /* if (!empty($request->get('state_id'))) {
                        $invokeParam['state_key'] = Helpers::getStateName($request->request->get('state_id'))->state_key;
                    } else {
                        $invokeParam['state_key'] = "";
                    }*/
                    if (!empty($request->get('state_key')) && !empty($request->get('state_name'))) { 
                         $invokeParam['state_key'] = $request->request->get('state_key');
                    }else{
                         $invokeParam['state_key'] = "";
                    }
                    $invokeParam['first_name'] = !empty($request->request->get('first_name')) ? $request->request->get('first_name') : "";
                    $invokeParam['last_name'] = !empty($request->request->get('last_name')) ? $request->request->get('last_name') : "";
                    $invokeParam['dob'] = ($request->request->get('dob')!=null) ? Helpers::getDateTimeInClientTz($request->request->get('dob'), 'm-d-Y', 'Y-m-d') : null;
                    $invokeParam['postal_code'] = !empty($request->request->get('postal_code')) ? $request->request->get('postal_code') : "";
                    $invokeParam['civic_number'] = !empty($request->request->get('street_name')) ? $request->request->get('street_name') : "";
                    $invokeParam['street_name'] = !empty($request->request->get('addr_line_one')) ? $request->request->get('addr_line_one') : "";
                    $invokeParam['sin_no'] = !empty($request->request->get('sin_no')) ? str_replace('-', '', $request->request->get('sin_no')) : '';
                    if ($owner_data['status'] == 1 || $owner_data['is_guarantor'] == 1 || !empty($owner_data['is_credit_bureau_skip'])) {
                        $data = Equifax::getConsumerCreditReport($app_user_id, $app_id, $ownerID, $invokeParam);
                        //entry in credit bureau manual table i.e. b2c_business_credit_bureau_infos
                        if(empty($data) || $data['Error'] == 'No response from Personal Credit Bureau.') {
                            $attribute['app_id'] = $app_id;
                            $attribute['app_owner_id'] = $ownerID;
                            $attribute['app_user_id'] = $app_user_id;
                            $this->prepareManualEquifaxPersonalCreditBureau($attribute);
                        }
                        return $data;
                    }
                }
            }
        } catch (Exception $e) {
            return Redirect::back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }

    /**
     * equifax response in manual b2c_business_credit_bureau_infos
     * 
     * @param array $attributes
     */
    public function prepareManualEquifaxCreditBureauData($attributes)
    {
        try{
            $app_id = $attributes['app_id'];
            $app_biz_id = $attributes['app_biz_id'];
            $app_user_id = $attributes['app_user_id'];
            
            $ci_score_index = $pi_score_index = $is_manual_reset = null;
            $deliquent_on_trade = 2;
            $legal_suits_amount = $returned_cheques = $collection_amount = $judgements_count = $is_bankrupt = $derog_count = 0;
            $where = ['app_user_id' => $app_user_id, 'app_id' => $app_id];
            $relation = ['equifaxBusinessScores', 'equifaxBusinessRefDetail', 'equifaxBusinessDerogatoryDetail', 'equifaxBusinessBankruptcy', 'equifaxBusinessResData', 'equifaxBizCollectionData', 'equifaxBizLegalDatail', 'equifaxBizFinancialData'];
            $appData = $this->application->getAllApplicationData($where, [], $relation)->toArray();

            if(count($appData) > 0) {
                $equiResData = isset($appData[0]['equifax_business_res_data'][0]) ? $appData[0]['equifax_business_res_data'][0] : [];
                if(isset($equiResData['hit_indicator']) && $equiResData['hit_indicator'] == 'HIT') {
                    $is_manual_reset = 1;
                    $equiScoreData = isset($appData[0]['equifax_business_scores']) ? $appData[0]['equifax_business_scores'] : [];
                    $equiRefDetail = isset($appData[0]['equifax_business_ref_detail']) ? $appData[0]['equifax_business_ref_detail'] : [];
                    $businessDerogatory = isset($appData[0]['equifax_business_derogatory_detail']) ? $appData[0]['equifax_business_derogatory_detail'] : [];
                    $businessBankruptcy = isset($appData[0]['equifax_business_bankruptcy']) ? $appData[0]['equifax_business_bankruptcy'] : [];
                    $businessCollection = isset($appData[0]['equifax_biz_collection_data']) ? $appData[0]['equifax_biz_collection_data'] : [];
                    $bizLegalDetail = isset($appData[0]['equifax_biz_legal_datail']) ? $appData[0]['equifax_biz_legal_datail'] : [];
                    $bizFinancialDetail = isset($appData[0]['equifax_biz_financial_data']) ? $appData[0]['equifax_biz_financial_data'] : [];
                    $bizQuaterlyPayment = Helpers::getEquifaxBussinessAllPaymentTermQuaterlyData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id], ['past_due_period3'], ['orderBy' => 'ASC'])->toArray();
                    //collection details
                    if(count($businessCollection) > 0) {
                        $collectionData = $this->prepareBusinessCollectionData($businessCollection);
                        $collection_amount = isset($collectionData['collection_amount']) ? $collectionData['collection_amount'] : null;
                    }
                    
                    //legal details
                    if(count($bizLegalDetail) > 0) {
                        $legalData = $this->prepareBusinessLegalData($bizLegalDetail);
                        $judgements_count = isset($legalData['judgements_count']) ? $legalData['judgements_count'] : null;
                        $legal_suits_amount = isset($legalData['legal_suits_amount']) ? $legalData['legal_suits_amount'] : null;
                    }
                    
                    //financial details
                    if(count($bizFinancialDetail) > 0) {
                        $financialData = $this->prepareBizFinancialData($bizFinancialDetail);
                        $derog_count = isset($financialData['derog_count']) ? $financialData['derog_count'] : null;
                        $deliquent_on_trade = isset($financialData['cust_not_correct']) ? $financialData['cust_not_correct'] : null;
                    }
                    
                    //reference details
                    if(count($equiRefDetail) > 0) {
                        foreach($equiRefDetail as $ref_data) {
                            if($ref_data['tot_past_due'] > 0 ) {
                                $deliquent_on_trade = 1;
                            }
                        }
                    }

                    $is_bankrupt = count($businessBankruptcy) > 0 ? 1 : 2;
                    //score details
                    if(count($equiScoreData) > 0) {
                        $scoreData = $this->prepareBusinessScoreData($equiScoreData);
                        $ci_score_index = isset($scoreData['ci_score_index']) ? $scoreData['ci_score_index'] : null;
                        $pi_score_index = isset($scoreData['pi_score_index']) ? $scoreData['pi_score_index'] : null;
                    }

                    //derogatory details
                    if(count($businessDerogatory) > 0) {
                        $derogData = $this->prepareBusinessDerogData($businessDerogatory);
                        $returned_cheques = $derogData['returned_cheques'];
                    }
                    
                    if(isset($bizQuaterlyPayment[0])) {
                        if($bizQuaterlyPayment[0]['past_due_period3'] > 0) {
                            $derog_count = $derog_count + 1;
                        }
                    }

                    if(isset($bizQuaterlyPayment[1])) {
                        if($bizQuaterlyPayment[1]['past_due_period3'] > 0) {
                            $derog_count = $derog_count + 1;
                        }
                    }
                    
                    $arrData['app_id'] = $app_id;
                    $arrData['app_biz_id'] = $app_biz_id;
                    $arrData['ci_score_index'] = $ci_score_index;
                    $arrData['pi_score_index'] = $pi_score_index;
                    $arrData['deliquent_on_trade'] = $deliquent_on_trade;
                    $arrData['legal_suits_amount'] = $legal_suits_amount;
                    $arrData['returned_cheques_amount'] = $returned_cheques;
                    $arrData['collection_claims_amount'] = $collection_amount;
                    $arrData['judgement_count'] = $judgements_count;
                    $arrData['is_bankrupt'] = $is_bankrupt;
                    $arrData['derogatory_records'] = $derog_count;
                    $arrData['is_manual'] = 0;
                    
                    //hard delete equifax response data
                    $this->manualBizCustRepo->deleteBusinessBureauData($hard_delete_status = true, ['app_id' => $app_id, 'app_biz_id' => $app_biz_id, 'is_manual' => 0]);
                    //soft delete manual bureau data
                    $this->manualBizCustRepo->deleteBusinessBureauData($hard_delete_status = false, ['app_id' => $app_id, 'app_biz_id' => $app_biz_id, 'is_manual' => 1]);
                    //create equifax response data
                    $this->manualBizCustRepo->create($arrData);
                } else {
                    $this->manualBizCustRepo->update(['deleted_at'=>null], ['app_id' => $app_id, 'app_biz_id' => $app_biz_id, 'is_manual' => 1]);
                    $this->manualBizCustRepo->deleteBusinessBureauData($hard_delete_status = true, ['app_id' => $app_id, 'app_biz_id' => $app_biz_id, 'is_manual' => 0]);
                }
            }
        } catch (Exception $ex) {
            return Redirect::back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }
    
    /**
     * equifax personal response in manual customer_credit_bureau_infos
     * 
     * @param array $attributes
     */
    public function prepareManualEquifaxPersonalCreditBureau($attributes)
    {
        try{
            $app_id = $attributes['app_id'];
            $app_owner_id = $attributes['app_owner_id'];
            $app_user_id = $attributes['app_user_id'];
            $secured_exposer = $unsecured_exposer = 0;
            $fico_score = $bni_score = $age_of_trade = null;
            $inquiry_count = $collection_count = $cust_not_current = $inactive_trade = $active_trade = 0;
            $is_bankrupt = $charge_off = $legal_suit = $any_warning = $serious_derog = 2;
            $where = ['app_user_id' => $app_user_id, 'app_id' => $app_id, 'app_owner_id' => $app_owner_id];
            $relations = ['ownerEquifaxTrade', 'equifaxResponseData', 'equifaxCollectionData', 'equifaxBankruptcies', 'equifaxFraudWarnings', 'equifaxScoreData', 'equifaxInquiriesData', 'equifaxLegalDetails', 'equifaxStatements', 'equifaxTradePaymentProfile', 'equifaxTradeNarrative'];
            $ownerData = $this->application->getAllConditionalOwnerData($where, [], $relations)->toArray();

            $equiPerResData = isset($ownerData[0]['equifax_response_data'][0]) ? $ownerData[0]['equifax_response_data'][0] : [];
            if(isset($equiPerResData['hit_code']) && $equiPerResData['hit_code'] != '00') {
                $ownerScoreData = isset($ownerData[0]['equifax_score_data']) ? $ownerData[0]['equifax_score_data'] : [];
                $ownerTradeData = isset($ownerData[0]['owner_equifax_trade']) ? $ownerData[0]['owner_equifax_trade'] : [];
                $ownerInquiryData = isset($ownerData[0]['equifax_inquiries_data']) ? $ownerData[0]['equifax_inquiries_data'] : [];
                $ownerStatementData = isset($ownerData[0]['equifax_statements']) ? $ownerData[0]['equifax_statements'] : [];
                $liability_amt = isset($ownerData[0]['equifax_bankruptcies'][0]['liability_amt']) ? $ownerData[0]['equifax_bankruptcies'][0]['liability_amt'] : null;
                $ownerLegalData = isset($ownerData[0]['equifax_legal_details']) ? $ownerData[0]['equifax_legal_details'] : [];
                $ownerCollectiondata = isset($ownerData[0]['equifax_collection_data']) ? $ownerData[0]['equifax_collection_data'] : [];
                $ownerTradePayment = isset($ownerData[0]['equifax_trade_payment_profile']) ? $ownerData[0]['equifax_trade_payment_profile'] : [];
                $hit_strength = isset($equiPerResData['hit_code']) ? $equiPerResData['hit_code'] : '00';
                $tradeNarrative = isset($ownerData[0]['equifax_trade_narrative']) ? $ownerData[0]['equifax_trade_narrative'] : [];

                //is bankrupt
                $is_bankrupt = isset($liability_amt) && $liability_amt > 0 ? 1 : 2;
                //prepare owner scores
                if(count($ownerScoreData) > 0) {
                    $scoreData = $this->prepareOwnerScoreData($ownerScoreData);
                    $fico_score = isset($scoreData['fico_score']) ? $scoreData['fico_score'] : null;
                    $bni_score = isset($scoreData['bni_score']) ? $scoreData['bni_score'] : null;
                }
                //prepare owner trade
                if(count($ownerTradeData) > 0) {
                    $returnData = $this->prepareOwnerTradeData($ownerTradeData);
                    $age_of_trade = isset($returnData['age_of_trade']) ? $returnData['age_of_trade'] : null;
                    $cust_not_current = isset($returnData['cust_not_current']) ? $returnData['cust_not_current'] : null;
                    
                    $ownerTradeData = array_reduce($ownerTradeData, function ($output, $element) {
                        $output[$element['trade_id']] = $element;
                        return $output;
                    });

                    if(count($tradeNarrative) > 0) {
                        $tradeNarrative = array_reduce($tradeNarrative, function ($output, $element) {
                            $output[$element['trade_id']][] = $element;
                            return $output;
                        });
                    }
                    
                    foreach($ownerTradeData as $trade) {
                        $active = 0;
                        //narrative status condition for active status
                        if(isset($tradeNarrative[$trade['trade_id']])) {
                            foreach($tradeNarrative[$trade['trade_id']] as $narrative) {
                                if(!in_array($narrative['code'], config('b2c_common.INACTIVE_TRADES'))) {
                                    $active = 1;
                                }
                            }
                        }
                        if($active == 1) {
                            $active_trade = $active_trade + 1;
                        }
                        
                        if(strpos(strtoupper($trade['name']), 'HSBC') !== false && isset($tradeNarrative[$trade['trade_id']]) && $ownerData[0]['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA')) {
                            $returnData = Helpers::sucuredUnsecuredExposerCalc($trade, $tradeNarrative[$trade['trade_id']], $secured_exposer, $unsecured_exposer);
                            $secured_exposer = $returnData['secured_exposer'];
                            $unsecured_exposer = $returnData['unsecured_exposer'];
                        }
                    }
                }
                //prepare owner inquiry
                if(count($ownerInquiryData) > 0) {
                    $inquiry_count = $this->prepareOwnerInquiryData($ownerInquiryData);
                }
                //prepare owner statement
                if(count($ownerStatementData) > 0) {
                    $any_warning = $this->prepareOwnerStatementData($ownerStatementData);
                }
                //prepare legal data
                if(count($ownerLegalData) > 0) {
                    $legal_suit = 1;
                    //$legal_suit = $this->prepareOwnerLegalData($ownerLegalData);
                }
                //prepare collection data
                if(count($ownerCollectiondata) > 0) {
                    $collection_count = $this->prepareOwnerCollectionData($ownerCollectiondata);
                }
                
                //prepare trade payment data
                if(count($ownerTradePayment) > 0) {
                    $paymentData = $this->prepareOwnerTradePaymentData($ownerTradePayment);
                    $charge_off = isset($paymentData['charge_off']) ? $paymentData['charge_off'] : null;
                    $serious_derog = isset($paymentData['serious_derog']) ? $paymentData['serious_derog'] : 2;
                }
                
                if(count($ownerTradeData) > 0 && count($tradeNarrative) == 0) {
                    $active_trade = 1;
                }

                if($age_of_trade < 12) {
                    $inactive_trade = 1;
                } else if($age_of_trade >= 12 && $age_of_trade < 36 && $active_trade <= 1) {
                    $inactive_trade = 1;
                } else if($age_of_trade >= 36 && $active_trade == 0) {
                    $inactive_trade = 1;
                }
                
                $secured_exposer = $secured_exposer * ($ownerData[0]['own_percentage']/100);
                $unsecured_exposer = $unsecured_exposer * ($ownerData[0]['own_percentage']/100);
                
                $arrCustBureauData['app_id'] = $app_id;
                $arrCustBureauData['app_owner_id'] = $app_owner_id;
                $arrCustBureauData['fico_score'] = $fico_score;
                $arrCustBureauData['bni_score'] = $bni_score;
                $arrCustBureauData['age_of_oldest_trade'] = $age_of_trade;
                $arrCustBureauData['inactive_trades_no'] = $inactive_trade;
                $arrCustBureauData['inquiries_no'] = $inquiry_count;
                $arrCustBureauData['any_warning'] = $any_warning;
                $arrCustBureauData['is_bankrupt'] = $is_bankrupt;
                $arrCustBureauData['is_charge_off'] = $charge_off;
                $arrCustBureauData['is_serious_derog'] = $serious_derog;
                $arrCustBureauData['is_legal_suits'] = $legal_suit;
                $arrCustBureauData['collection_filed_count'] = $collection_count;
                $arrCustBureauData['is_not_current'] = $cust_not_current;
                $arrCustBureauData['hit_strength'] = $hit_strength;
                $arrCustBureauData['is_manual'] = 0;
                $arrCustBureauData['secured_exposer'] = $secured_exposer;
                $arrCustBureauData['unsecured_exposer'] = $unsecured_exposer;
                //hard delete equifax customer bureau data
                $this->manualBizCustRepo->deleteCustomerBureauData($hard_delete_status = true, ['app_id' => $app_id, 'app_owner_id' => $app_owner_id, 'is_manual' => 0]);
                //soft delete manual customer bureau data
                $this->manualBizCustRepo->deleteCustomerBureauData($hard_delete_status = false, ['app_id' => $app_id, 'app_owner_id' => $app_owner_id, 'is_manual' => 1]);
                //create equifax bureau data
                $this->manualBizCustRepo->createCustomerCreditInfo($arrCustBureauData);
            } else {
                $this->manualBizCustRepo->updateCustomerCreditInfo(['app_id' => $app_id, 'app_owner_id' => $app_owner_id, 'is_manual' => 1], ['deleted_at'=>null]);
                $this->manualBizCustRepo->deleteCustomerBureauData($hard_delete_status = true, ['app_id' => $app_id, 'app_owner_id' => $app_owner_id, 'is_manual' => 0]);
            }
        } catch (Exception $ex) {
            return Redirect::back()->withErrors(Helpers::getExceptionMessage($e))->withInput();
        }
    }
}
