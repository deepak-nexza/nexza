<?php

namespace App\Http\Controllers\Contracts\Traits;

use Event;
use Session;
use Helpers;
use Carbon\Carbon;

trait CommonTrait {
    /**
    * save knock-out reference
    *
    * @return mixed
    */
    
    public function saveKnockoutRefCode($attributes)
    {
        try {
            $quesArr = null;
            $entityArr = null;
            $promo_code = null;
            $knockout_id = null;
            $industryArr = [];
            $ref_count = config('b2c_common.ONE');
            
            if(Session::has('PreQualifiedQues')) {
                Session::forget('PreQualifiedQues');
            }
            if(Session::has('industry')){
                Session::forget('industry');
            }
            if (Session::has('entity')) {
                Session::forget('entity');
            }
            
            $app_user_id  = isset($attributes['app_user_id']) ? $attributes['app_user_id'] : null;
            $app_id       = isset($attributes['app_id']) ? (int) $attributes['app_id'] : null;
            $knockout_ref = isset($attributes['knockout_code']) ? $attributes['knockout_code'] : null;
            $knockout_desc = isset($attributes['knockout_desc']) ? $attributes['knockout_desc'] : null;
            $quesArr  = isset($attributes['questionData']) && count($attributes['questionData'] > 0) ? json_encode($attributes['questionData']) : null;

            if(isset($attributes['industryArr'])){
                $industryArr['division'] = $attributes['industryArr']['division'];
                $industryArr['sub_division'] = $attributes['industryArr']['sub_division'];
                $industryArr['indtyp'] = $attributes['industryArr']['indtyp'];
                Session::forget('industry');
            }

            $entityArr  = isset($attributes['entityArr']['entitytyp']) && $attributes['entityArr']['entitytyp'] > 0 ? $attributes['entityArr']['entitytyp'] : null;

            $knockData = Helpers::getKnockoutWithRef(['app_user_id' => $app_user_id, 'app_id' => $app_id, 'knockout_reference' => $knockout_ref], ['id', 'ref_count'])->toArray();
            Session::Push('is_knockout', true);
            if($app_id > 0) {
                if (Helpers::checkApplicationStatus($app_id) == config('b2c_common.KNOCKED_OUT')) {
                    return response()->json(['success' => false]);
                }
                //update Application status
                $arrAppData['current_status'] = config('b2c_common.KNOCKED_OUT');
                Helpers::updateAppStatus(['app_user_id' => (int) $app_user_id, 'app_id' => (int) $app_id], $arrAppData);
                $message = trans('messages.application_knocked_out');
                $activity_type = config('b2c_common.KNOCKED_OUT_ACTIVITY');
                Helpers::trackApplicationActivity($message, $app_user_id, $app_id, $activity_type);
            } else {
                if (Session::has('promo_code')) {
                    $promo_code = Session::get('promo_code');
                    $promoArr = $this->guestRepo->getAllConditionalPromoCode(['promo_code' => $promo_code])->toArray();
                    if(isset($promoArr['code_type']) && $promoArr['code_type'] == config('b2c_common.PROMO_CODE_TYPE.GENERIC')) {
                        $this->guestRepo->updatePromoCode(['is_active' => config('b2c_common.DEACTIVE'), 'expired_at' => Carbon::now()], ['promo_code' => $promo_code]);
                    }
                }
                /* Save Track User Info** */
                $message = trans('messages.application_knocked_out');
                $promo_code = isset($promo_code['promo_code']) ? $promo_code['promo_code'] : null;
                $session_data = Helpers::getTrackData(['session_id' => session()->getId()])->toArray();
                if(count($session_data) > 0 && isset($session_data[0]['promo_code'])) {
                    if(isset($session_data[0]['promo_code'])) {
                        $promo_code = $session_data[0]['promo_code'] . ', '. $promo_code;
                    }
                    if(isset($session_data[0]['knockout_code'])) {
                        $knockout_ref = $session_data[0]['knockout_code'] . ', '. $knockout_ref;
                    }
                }
                Helpers::saveTrackUserInfo($quesArr, $industryArr, $entityArr, $message, $promo_code, $knockout_ref);
            }
            if(isset($knockData) && count($knockData) > 0) {
                $knockout_id = isset($knockData[0]['id']) ? $knockData[0]['id'] : null;
                $ref_count = isset($knockData[0]['ref_count']) ? $knockData[0]['ref_count'] + config('b2c_common.ONE') : config('b2c_common.ONE');
            }
            Helpers::saveKnockoutReference($knockout_id, ['app_id' => $app_id, 'app_user_id' => $app_user_id, 'knockout_reference' => $knockout_ref, 'ref_count' => $ref_count, 'knockout_desc'=>$knockout_desc]);
        } catch (Exception $ex) {
            return Helpers::ajaxResponse(false, null, Helpers::getExceptionMessage($e), null, null);
        }
    }
    

    /**
    * Update Backend User in Application
    * 
    * @param int $app_user_id
    * @param int $app_id
    * @param array $userData
    * @return type
    */     
    protected function assignCaseToBackendUser($appDataArra = []) {
        try {
            $leadOwnerInfo = [
                'lead_id' => $appDataArra['app_user_id'],
                'owner_id' => $appDataArra['to_id'],
                'role_level' => $appDataArra['role_level'],
                'created_at' => Helpers::getCurrentDateTime(),
                'created_by' => $appDataArra['app_user_id'],
            ];
            
            $this->application->saveLeadOwner($leadOwnerInfo);
            
            $shareLeadInfo = [
                'from_id' => $appDataArra['from_id'],
                'to_id' => $appDataArra['to_id'],
                'app_user_id' => $appDataArra['app_user_id'],
                'assign_status' => $appDataArra['assign_status'],
                'role_level' => $appDataArra['role_level'],
            ];
            $this->application->saveShareLeadInfo($shareLeadInfo);
            $insert_share =  isset($appDataArra['insert_share']) && ($appDataArra['insert_share'] == false) ? false  : true ;          
            if ($insert_share == true) {
                $shareCaseInfo = [
                    'from_id' => $appDataArra['from_id'],
                    'to_id' => $appDataArra['to_id'],
                    'app_user_id' => $appDataArra['app_user_id'],
                    'app_id' => $appDataArra['app_id'],
                    'assign_status' => $appDataArra['assign_status'],
                    'role_level' => $appDataArra['role_level'],
                    'sharing_comment' => isset($appDataArra['sharing_comment']) ? $appDataArra['sharing_comment'] : null
                ];
                $this->application->saveShareCaseInfo($shareCaseInfo);
            }
            $updateAppData = [
                'case_owner_id' => $appDataArra['to_id'],
                'owner_assign_at' => Helpers::getCurrentDateTime(),
                'current_assignee' => $appDataArra['current_assignee'],
            ];
            
            if($appDataArra['role_level'] == config('b2c_common.RM_ROLE_LEVEL')) {
                $updateAppData['current_rm'] = $appDataArra['to_id'];
                $updateAppData['rm_assign_at'] = Helpers::getCurrentDateTime();;
            } else if($appDataArra['role_level'] == config('b2c_common.CO_ROLE_LEVEL')) {
                $updateAppData['current_co'] = $appDataArra['to_id'];
                $updateAppData['co_assign_at'] = Helpers::getCurrentDateTime();
            } else if($appDataArra['role_level'] == config('b2c_common.BO_ROLE_LEVEL')) {
                $updateAppData['current_bo'] = $appDataArra['to_id'];
                $updateAppData['bo_assign_at'] = Helpers::getCurrentDateTime();
            }

            $this->application->updateAppData($appDataArra['app_id'], $updateAppData);

            $caseOwnerInfo = [
                'lead_id' => $appDataArra['app_user_id'],
                'case_id' => $appDataArra['app_id'],
                'owner_id' => $appDataArra['to_id'],
                'role_level' => $appDataArra['role_level'],
                'created_at' => Helpers::getCurrentDateTime(),
                'created_by' => \Auth::user()->id,
            ];
            $this->application->saveCaseOwner($caseOwnerInfo);
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    /**
     * auto decision on basis of yodlee bank
     *
     * @param array $appData
     */
    public function autoDecisionAndAssign($appData, $appDataInfo, $is_backend = false)
    {
        $arrAppData = [];
        $offer_group_id = null;
        $app_id         = $appData['app_id'];
        $app_user_id    = $appData['app_user_id'];
        $decision       = isset($appData['decision']) ? $appData['decision'] : null;
        $customerData   = \Auth::user();
        $currentRMOwner = $this->userRepo->find($appData->current_rm);
        $arrUsers = $this->userRepo->find($app_user_id);
        $appData = $this->application->find($app_id);
        $arrUsers->app_locale = $appData['app_locale'];
        $limitData = Helpers::getCreditLimitData(['app_user_id' => $app_user_id, 'app_id' => $app_id], ['cust_limit', 'interest_rate']);
        $systemUser = $this->userRepo->getUserData(['user_level_id' => config('b2c_common.SYSTEM_USERLEVEL')], ['id']);
        $amountBifurProduct = $this->application->getAmountProductBifurcationData(['app_id' => $app_id, 'app_user_id' => $app_user_id])->toArray();
        $interest_rate = isset($amountBifurProduct[0]['interest_rate']) ? $amountBifurProduct[0]['interest_rate'] : null;
        $appDataArr = [
            'app_user_id' => $app_user_id,
            'app_id' => $app_id,
            'from_id' => \Auth::user()->id,
            'to_id' => $appDataInfo['0']->current_rm,
            'role_level' => config('b2c_common.RM_ROLE_LEVEL'),
            'assign_status' => 1,
            'current_assignee' => config('b2c_common.RM_CURRENT_ASSIGNEE'),
            'loan_amt' => isset($appDataInfo['0']->loan_amount) ? Helpers::formatMoney($appDataInfo['0']->loan_amount) : null,
            'role' => 'System',
            'co_name' => 'System',
            'cust_name' => isset($appDataInfo['0']->first_name) ? $appDataInfo['0']->first_name. " ".$appDataInfo['0']->last_name : null,
            'business_name' => isset($appDataInfo['0']->biz_name) ? $appDataInfo['0']->biz_name : null,
            'rm_email' => $currentRMOwner->email,
            'comment' => trans('activity_messages.system_auto_decision'),
            'term' => config('b2c_common.OFFER_TERM'),
            'intrest_rate' => $interest_rate,
            'approved_amt' => isset($limitData[0]['cust_limit']) ? $limitData[0]['cust_limit'] : null,
        ];
        $offerData = [];
        $tot_funded_amt = 0;
        if(count($amountBifurProduct) > 0) {
            foreach($amountBifurProduct as $offer) {
                $tot_funded_amt = $tot_funded_amt + $offer['total_amount'];
                $offerData[] = [
                    'app_id' => (int) $app_id,
                    'product_id' => isset($offer['product_id']) ? $offer['product_id'] : null,
                    'amortization' => isset($offer['amortization']) ? $offer['amortization'] : null,
                    'sanc_amount' => isset($offer['total_amount']) ? $offer['total_amount'] : null,
                    'is_approve_decline' => ($decision == config('b2c_common.APPROVED')) ? 1 : 4,
                    'message' =>  trans('activity_messages.system_auto_decision'),
                    'interest_rate' => isset($offer['interest_rate']) ? $offer['interest_rate'] : null,
                    'interest_rate_type' => isset($offer['interest_type']) ? $offer['interest_type'] : null,
                    'loan_security' => isset($offer['loan_security_type']) ? $offer['loan_security_type'] : null,
                    'is_current_offer' => 1,
                    'is_conditionally' => 2,
                    'is_auto_decision' => config('b2c_common.YES'),
                    'offer_status' => ($decision == config('b2c_common.APPROVED')) ? 1 : 4,
                    'created_at' => Helpers::getCurrentDateTime(),
                    'created_by' => isset($systemUser['id']) ? $systemUser['id'] : \Auth::user()->id,
                    'updated_at' => Helpers::getCurrentDateTime(),
                    'updated_by' => isset($systemUser['id']) ? $systemUser['id'] : \Auth::user()->id
                ];
            }
        }else {
            $offerData[0]['app_id']            = $app_id;
            $offerData[0]['offer_status']      = ($decision == config('b2c_common.APPROVED')) ? 1 : 4;
            $offerData[0]['message']           = trans('activity_messages.system_auto_decision');
            $offerData[0]['is_approve_decline'] = ($decision == config('b2c_common.APPROVED')) ? 1 : 4;
            $offerData[0]['sanc_amount']       = isset($limitData[0]['cust_limit']) ? $limitData[0]['cust_limit'] : null;
            $offerData[0]['interest_rate']     = $interest_rate;
            $offerData[0]['is_conditionally']  = 2;
            $offerData[0]['amrt_year']         = config('b2c_common.AMORTIZATION_YEARS');
            $offerData[0]['is_auto_decision']  = config('b2c_common.YES');
            $offerData[0]['is_current_offer']  = config('b2c_common.YES');
            $offerData[0]['created_by']        = isset($systemUser['id']) ? $systemUser['id'] : \Auth::user()->id;
            $offerData[0]['updated_by']        = isset($systemUser['id']) ? $systemUser['id'] : \Auth::user()->id;
            $offerData[0]['updated_at']        = Helpers::getCurrentDateTime();
            $offerData[0]['created_at']        = Helpers::getCurrentDateTime();
            $tot_funded_amt = isset($limitData[0]['cust_limit']) ? $limitData[0]['cust_limit'] : null;
        }
        
        if($decision == config('b2c_common.APPROVED')) {
            if($is_backend == true) {
                $arrAppData['current_status'] = config('b2c_common.OFFER_ADDED');
            } else {
                $arrAppData['current_status'] = config('b2c_common.OFFER_STATUS_SENT');
            }
            
            $mailBodyParam = [
                'app_user_id' => $app_user_id,
                'app_id' => $app_id,
                'rm_name' => $currentRMOwner->first_name." ".$currentRMOwner->last_name,
                'rm_email' => $currentRMOwner->email,
                'custmer_name' => $customerData->first_name." ".$customerData->last_name,
                'sanction_amt' => isset($appData['loan_amount']) ? $appData['loan_amount'] : null,
                'term' => config('b2c_common.OFFER_TERM'),
                'intrest_rate' => isset($interest_rate) ? $interest_rate : null,
           ];
            
            if(count($offerData) > 0) {
                foreach($offerData as $offer_data) {
                    $this->application->saveOffer($offer_data);
                }
            }
            $offerDataFind = $this->application->getOfferDetail(['app_id' => $app_id, 'is_auto_decision' => 1,'is_current_offer' =>1], ['offer_id']);
            if(count($offerDataFind) > 0){
                foreach($offerDataFind as $offerData) {
                    $offer_id = isset($offerData['offer_id']) ? $offerData['offer_id'] : '';
                    if($offer_id > 0) {
                        $offer_group_id = $offer_id.$app_id;
                        $this->application->updateOfferData($app_id, ['offer_group_id' => $offer_group_id]);
                        break;
                    }
                }
            }
            $this->application->updateAppData($app_id, ['funded_amt' => $tot_funded_amt]);
            //Auto approval checklist required code Start
                if(count($offer_group_id) > 0) {
                    $getChecklistData =  $this->security->getAllChecklistItemByIllegalEntity((int)$appData['legal_entity_id'],['is_active'=>1,'is_auto_approval'=>1,'is_bussiness_establish'=>2])->toArray();
                    $appBizDetail     = $this->application->getBizInformation($app_user_id,$app_id)->toArray();
                    $monthsInBiz      = !empty($appBizDetail['date_established']) ? Helpers::calculateMonthsDiff($appBizDetail['date_established'] ): 0;
                    if($monthsInBiz < 24){
                       $getChecklistDataBussiness =  $this->security->getAllChecklistItemByIllegalEntity((int)$appData['legal_entity_id'],['is_active'=>1,'is_auto_approval'=>1,'is_bussiness_establish'=>1])->toArray();
                       $getChecklistData =  array_merge($getChecklistData,$getChecklistDataBussiness);
                    }
                    if(count($getChecklistData) >0){
                       $getChecklistData = array_map("unserialize", array_unique(array_map("serialize", $getChecklistData)));
                       $this->AutoApprovalSaveChecklist($getChecklistData,$app_user_id,$app_id,$offer_group_id,$appData); 
                    } 
                    $updateAppData['is_uw_checklist'] = 1;
                    $this->application->updateAppData($app_id, $updateAppData);
                }
            //Auto approval checklist required code End
            if($is_backend == false) {
                Event::fire("application.send_offer_to_customer", serialize($arrUsers->toArray() + ['app_id' => $app_id]));
            }
            Event::fire("application.on_application_approve", serialize($appDataArr));
            $message = trans('activity_messages.application_approved');
            Helpers::trackApplicationActivity($message, $app_user_id, $app_id, 96);
        } else if($decision == config('b2c_common.REFER')) {
            $arrAppData['current_status'] = config('b2c_common.MANUAL_REVIEW');
        } else if($decision == config('b2c_common.DECLINE')) {
            $arrAppData['current_status'] = config('b2c_common.DECLINED');
            if(count($offerData) > 0) {
                foreach($offerData as $offer_data) {
                    $this->application->saveOffer($offer_data);
                }
            }
            $offerDataFind = $this->application->getOfferDetail(['app_id' => $app_id, 'is_auto_decision' => 1,'is_current_offer' =>1], ['offer_id']);
            if(count($offerDataFind) > 0){
                foreach($offerDataFind as $offerData) {
                    $offer_id = isset($offerData['offer_id']) ? $offerData['offer_id'] : '';
                    if($offer_id > 0) {
                        $offer_group_id = $offer_id.$app_id;
                        $this->application->updateOfferData($app_id, ['offer_group_id' => $offer_group_id]);
                        break;
                    }
                }
            }
            
            $this->application->updateAppData($app_id, ['interest_rate' => $interest_rate]);
            Event::fire("application.on_application_declined", serialize($appDataArr));
            $message = trans('activity_messages.application_declined');
            Helpers::trackApplicationActivity($message, $app_user_id, $app_id, 95);
        }
        
        if($offer_group_id > 0) {
            $this->application->saveOfferGroupData($offer_group_id, ['offer_group_id' => $offer_group_id, 'program_type' => $appData['program_type']]);
        }
        
        $arrNotes['app_id'] = $app_id;
        $arrNotes['note_type'] = '0';
        $arrNotes['created_by'] = isset($systemUser['id']) ? $systemUser['id'] : \Auth::user()->id;
        $arrNotes['updated_by'] = isset($systemUser['id']) ? $systemUser['id'] : \Auth::user()->id;
        $arrNotes['activity_type_id'] = '16';
        $arrNotes['txt_comment'] = trans('activity_messages.system_auto_decision');
        $this->application->saveCaseNotes((array) $arrNotes);
        
        $arrAppData["status_modify_date"] = Helpers::getCurrentDateTime();
        $arrAppData['app_status'] = config('b2c_common.APP_APPLICATION_COMPLETED');
        $arrAppData['app_submit_at'] = Helpers::getCurrentDateTime();
        $arrAppData['system_user'] = isset($systemUser['id']) ? $systemUser['id'] : \Auth::user()->id;
        Helpers::updateAppStatus(['app_user_id' => $app_user_id, 'app_id' => $app_id], $arrAppData);
        $message = trans('activity_messages.system_auto_decision');
        Helpers::trackApplicationActivity($message, $app_user_id, $app_id, 96);
    }
    
     /**
     * Create Application
     * 
     * @param Object $user
     * @return type
     */
    public function saveApplication($request, $app_user_id, $userData = null)
    {
        try {
            $arrApp = [];
            $arrApp["app_user_id"] = $app_user_id;
            $arrApp["current_assignee"] = config('b2c_common.RM_CURRENT_ASSIGNEE');
            $arrApp["app_status"] = config('b2c_common.APP_IN_PROGRESS_APPLICTAION');
            $arrApp["current_status"] = config('b2c_common.APP_CUR_IN_PROGRESS_APPLICATION');
            $arrApp["status_modify_date"] = Helpers::getCurrentDateTime();
            $arrApp["user_privacy_consent"] = 1;
            $arrApp["is_created_from"] = 2;
            $arrApp["version"] = 2;
            $arrApp["app_locale"] = !empty(app()->getLocale())?app()->getLocale():"en";
            

            if ($userData != null && isset($userData) && $userData->id > 0) {
                $arrApp["case_owner_id"] = (int) $userData->id;
                $arrApp["owner_assign_at"] = Helpers::getCurrentDateTime();
                $arrApp["current_rm"] = (int) $userData->id;
                $arrApp["rm_assign_at"] = Helpers::getCurrentDateTime();
            }
            if ($request->get('app_id') > 0) {
                $app_id = $request->get('app_id');
            } else {
                  //Save Application 
                $app_id = $this->application->saveApplication($arrApp);
                $this->saveCaseOwner($app_id, $app_user_id, (int) $userData->id);
                
                $this->saveShareCase($app_id, $app_user_id, $userData->id);
                if (!empty($app_id)) {
                    /**                     * *******Save application status log********** */
                    $arrAppStatus['status_id'] = config('b2c_common.APP_CUR_IN_PROGRESS_APPLICATION');
                    $arrAppStatus['app_id'] = $app_id;
                    $arrAppStatus['app_user_id'] = $app_user_id;
                    $arrAppStatus["created_at"] = Helpers::getCurrentDateTime();
                    $arrAppStatus["created_by"] = $userData->id;
                    $arrAppStatus["updated_at"] = Helpers::getCurrentDateTime();
                    $arrAppStatus["updated_by"] = $userData->id;
                    Helpers::saveApplicationStatus($arrAppStatus);
                }
            }

            
            return $app_id;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
     /**
     * Create case owner log
     * 
     * @param int $user_id
     * @return type
     */
    public function saveCaseOwner($app_id, $user_id, $adminRM = null) {
        try {
            $caseOwnerInfo = [
                'lead_id' => $user_id,
                'case_id' => $app_id,
                'owner_id' => $adminRM,
                'role_level' => config('b2c_common.RM_ROLE_LEVEL'),
                'created_at' => Helpers::getCurrentDateTime(),
                'created_by' => $adminRM,
            ];
            $lead_owner_id = $this->application->saveCaseOwner($caseOwnerInfo);

            return $lead_owner_id;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    /**
     * Save Pre-Qualifying question
     * 
     * @param array $preQualifiedQues
     */
    public function saveQuestions($preQualifiedQues, $app_user_id, $app_id) 
    {

        try {
            $this->application->deleteAppQuestions(['app_id'=>$app_id,'app_user_id'=>$app_user_id]);
            $arrQues = [];
            if (!empty($preQualifiedQues)) {
                $arrQuesMulti = [];
                foreach ($preQualifiedQues as $key => $val) {
                    $arrQues["app_user_id"] = $app_user_id;
                    $arrQues["app_biz_id"] = null;
                    $arrQues["app_id"] = $app_id;
                    $arrQues["quest_id"] = $key;
                    $arrQues["answer"] = $val;
                    $arrQues["created_at"] = Helpers::getCurrentDateTime();
                    $arrQues["created_by"] = $this->user_data->id;
                    $arrQues["updated_at"] = Helpers::getCurrentDateTime();
                    $arrQues["updated_by"] = $this->user_data->id;
                    $arrQuesMulti[] = $arrQues;
                }
                $this->application->saveQuestionsmapping($arrQuesMulti);
            }
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    /**
     * Save Share case info
     * 
     * @param int $user_id & $app_id
     * @return type
     */
    public function saveShareCase($app_id, $app_user_id , $login_user_id) {
        try {
            $shareCaseInfo = [
                'from_id' => $app_user_id,
                'to_id' => $login_user_id,
                'app_user_id' => $app_user_id,
                'app_id' => $app_id,
                'assign_status' => 1,
                'role_level'=>config('b2c_common.RM_ROLE_LEVEL')
            ];
            $shareapp_id = $this->application->saveShareCaseInfo($shareCaseInfo);
            return $shareapp_id;
        } catch (\Exception $ex) {
            return redirect()->back()->withErrors(Helpers::getExceptionMessage($ex));
        }
    }
    
    /**
     * Save knockout log
     * 
     * @param array $arrRefData
     * @param array $attr
     * @return mixed
     */
    public function saveBackendKnockoutLog($arrRefData)
    {
        try {
            return $this->application->saveKnockoutReference(null, $arrRefData);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Update current route
     * 
     * @param int $app_id
     * @param String $action
     * @throws \App\Http\Controllers\Contracts\Traits\Exception
     */
    public function updateCurrentRoute($app_id ,$action)
    {
        try {
            if ($action != 'app_detail_edit' && $action != 'app_credit_edit') {
                Helpers::updateCurrentRoute($app_id);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateApplicatonStatus($user_id , $app_id ,$attr ,$action)
 {
        try {
            if ($action != 'app_detail_edit') {
                Helpers::updateAppStatus(['app_user_id' => (int) $user_id, 'app_id' => (int) $app_id], $attr);
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
    /**
     * Function to save data in  to send email
     * @param int $toUserName,$email,$recordData,$recordStatus,$subject
     * return $array
     */
     public function acceptRejectArray($toUserName,$email,$recordData,$recordStatus,$subject){
        $arrdata = [];
        $arrdata['from_username']      = Auth()->user()->first_name." ".Auth()->user()->last_name;
        $arrdata['to_username']        = $toUserName;
        $arrdata['to_email']           = $email;
        $arrdata['record_data']        = $recordData;
        $arrdata['record_status']      = $recordStatus ? 'authorized' : 'rejected';
        $arrdata['subject']            = $subject;
        return $arrdata;
     }
    
    /**
     * function to save data in  log
     * @param int $id,$requestData,$table_column,$ip,$type
     * return $array
     */
    public function saveDataMasterLog($id,$requestData,$table_column,$ip,$type){
        $arrDataLog = [];
        $arrDataLog[$table_column]            = $id;
        $arrDataLog['ip']                     = $ip;
        $arrDataLog['type']                   = $type;
        $arrDataLog['request_data']           = json_encode($requestData);
        return $arrDataLog;
    }
    
    /**
     * Save Auto approval Checklist
     * @param int $getChecklistData
     */
    public function AutoApprovalSaveChecklist($getChecklistData,$app_user_id,$app_id,$offer_group_id,$appData){
        $arrCheckList =[];
        if(count($getChecklistData)> 0){
            foreach($getChecklistData as $key => $checklistData) {
                $arrCheckList['app_user_id']          = !empty($app_user_id) ? $app_user_id : '';
                $arrCheckList['app_id']               = !empty($app_id) ?  $app_id : '';
                $arrCheckList['offer_group_id']       = !empty($offer_group_id) ? (int)$offer_group_id : '';
                $arrCheckList['checklist_item_id']    = !empty($checklistData['id'])? $checklistData['id'] : null;
                $arrCheckList['approval_type']        = 2;//Standar approval;
                    if($appData['legal_entity_id'] == config('b2c_common.Corporation') || $appData['legal_entity_id'] == config('b2c_common.Holding_company')){
                        if($checklistData['id'] == 1 || $checklistData['id'] == 3 || $checklistData['id'] == 4 || $checklistData['id'] == 11){
                          $arrCheckList['is_uw_remarks'] = config('b2c_common.required');  
                        }else{
                            $arrCheckList['is_uw_remarks'] = config('b2c_common.not_required');
                        }  
                    }else if($appData['legal_entity_id'] == config('b2c_common.Sole_proprietorship') || $appData['legal_entity_id'] == config('b2c_common.Partnership')){
                        if($checklistData['id'] == 1 || $checklistData['id'] == 9 || $checklistData['id'] == 10 || $checklistData['id'] == 11){
                          $arrCheckList['is_uw_remarks'] = config('b2c_common.required');  
                        }else{
                          $arrCheckList['is_uw_remarks'] = config('b2c_common.not_required');
                        }
                    }else{
                       $arrCheckList['is_uw_remarks'] = config('b2c_common.not_required');         
                    }
                   $this->security->saveUpdateAppCheckList($arrCheckList,null);
            }
        }
    }
    
    /* * prepare offer colletral data
     * @param array $collateralPledge
     * @param int $offer_id
     * @param int $key
     */
    public function prepareColletralPledgeData($collateralPledge, $offer_id, $key, $app_id)
    {
        $pledgeData = [];
        $current_time = Helpers::getCurrentDateTime();
        $created_by = \Auth::user()->id;
        if(isset($collateralPledge[$key]) && count($collateralPledge[$key]) > 0) {
            foreach($collateralPledge[$key] as $colletral) {
                foreach($colletral as $data) {
                    $new_data = explode('_', $data);
                    $pledgeData[] = [
                        'is_checked' => 1,
                        'app_id' => $app_id,
                        'offer_id' => $offer_id,
                        'created_by' => $created_by,
                        'updated_by' => $created_by,
                        'updated_at' => $current_time,
                        'created_at' => $current_time,
                        'security_id' => isset($new_data[2]) ? $new_data[2] : null,
                        'security_type' => isset($new_data[1]) ? $new_data[1] : null,
                    ];
                }
            }
        }
        return $pledgeData;
    }
    
     /**
     * prepare and save static decision reason
     * 
     * @param array $attributes
     */
    public function prepareSaveDecisionReasonData($attributes)
    {
        $loggedInData = \Auth::user();
        $curent_date = Helpers::getCurrentDateTime();
        $resultData[0] = [
            'app_id' => $attributes['app_id'],
            'app_user_id' => $attributes['app_user_id'],
            'decision' => 'Refer',
            'code' => isset($attributes['reasons']['code']) ? $attributes['reasons']['code'] : null,
            'reason' => isset($attributes['reasons']['reason']) ? $attributes['reasons']['reason'] : null,
            'created_by' => isset($loggedInData->id) ? $loggedInData->id : null,
            'updated_by' => isset($loggedInData->id) ? $loggedInData->id : null,
            'created_at' => $curent_date,
            'updated_at' => $curent_date,
        ];
        $resultDataNew[0] = [
            'app_id' => $attributes['app_id'],
            'app_user_id' => $attributes['app_user_id'],
            'decision' => 'Refer',
            'code' => isset($attributes['reasons']['code']) ? $attributes['reasons']['code'] : null,
            'reason' => isset($attributes['reasons']['reason']) ? $attributes['reasons']['reason'] : null,
            'is_auto_manual' => 1,
            'created_by' => isset($loggedInData->id) ? $loggedInData->id : null,
            'updated_by' => isset($loggedInData->id) ? $loggedInData->id : null,
            'created_at' => $curent_date,
            'updated_at' => $curent_date,
        ];
        $this->application->saveDecisionReasonCode($resultData);
        $this->application->saveDecisionReasonCodeLog($resultDataNew);
    }
    
    /**
     * prepare offer data from product bifurfication
     * 
     * @param array $amountBifurProduct
     * @return type
     */
    public function prepareApprovedOfferProductData($amountBifurProduct)
    {
        $offerData = [];
        if(count($amountBifurProduct) > 0) {
            foreach($amountBifurProduct as $key => $product) {
                $offerData[] = [
                    'product_id' => isset($product['product_id']) ? $product['product_id'] : null,
                    'amortization' => isset($product['amortization']) ? $product['amortization'] : null,
                    'sanc_amount' => isset($product['total_amount']) ? $product['total_amount'] : null,
                    'interest_rate' => isset($product['interest_rate']) ? $product['interest_rate'] : null,
                    'interest_rate_type' => isset($product['interest_type']) ? $product['interest_type'] : null,
                    'loan_security' => isset($product['loan_security_type']) ? $product['loan_security_type'] : null,
                ];
            }
        }
        return $offerData;
    }

    /**
     * prepare array 
     * @param array $attributes
     */
     public function newAddedChecklist($checklistData,$app_user_id,$app_id)
     {
        if(count($checklistData) > 0){
            $arrCheckList = $newaddedChecklist = [];
            foreach($checklistData as $id){
                $extraChecklist = $this->security->masterChecklistfind($id);
                $arrCheckList['id']                   = $id;                    
                $arrCheckList['app_user_id']          = $app_user_id;
                $arrCheckList['app_id']               = $app_id;
                $arrCheckList['checklist_item_id']    = $id;
                $arrCheckList['checklist_item']       = $extraChecklist['checklist_item'];
                $arrCheckList['checklist_item_text']  =  null ;
                $arrCheckList['is_uw_remarks']        =  null;
                $arrCheckList['is_brm_remarks']       =  null;
                $arrCheckList['is_bo_remarks']        =  null;
                $newaddedChecklist[] = $arrCheckList;
            }
        }
        return $newaddedChecklist;
      }

    /**
     * OfferTail
     * @param Request $request
     * @return mixed
     */
    public function saveOfferTrail($app_id, $offerGroupId,$status_id,$offer_accepted)
    {
        $arrData = [];
        $loggedInData  = \Auth::user();
        $curent_date   = Helpers::getCurrentDateTime();
        if(!empty($app_id) && !empty($offerGroupId) && !empty($status_id)){
            //update offer group id
            $OfferData = $this->application->getOfferTrailData(['app_id'=>$app_id]);
            if(count($OfferData)>0){
               $this->application->updateOfferTrailData(['app_id'=>$app_id],['is_current_status'=>2]);
            }
            $arrData['app_id']           = $app_id;
            $arrData['offer_group_id']   = $offerGroupId;
            $arrData['status_id']        = $status_id;
            $arrData['is_current_status']= 1;
            $arrData['offer_accepted']   = isset($offer_accepted) && !empty($offer_accepted) ? $offer_accepted : null ;
            $arrData['created_by']       = isset($loggedInData->id) ? $loggedInData->id : null;
            $arrData['created_at']       = $curent_date;
            $arrData['updated_by']       = isset($loggedInData->id) ? $loggedInData->id : null;
            $arrData['updated_at']       = $curent_date;
            $this->application->saveOfferTrail($arrData);
        } 
    }
}
