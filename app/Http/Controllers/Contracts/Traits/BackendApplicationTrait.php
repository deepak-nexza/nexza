<?php

namespace App\Http\Controllers\Contracts\Traits;

use Auth;
use Helpers;

trait BackendApplicationTrait {

    /**
     * Display send email
     *
     * @return \Illuminate\View\View
     */
    public function showSendEamilForm($blade) 
    {
        try {
            $request = request();
            $app_user_id = $request->get('app_user_id');
            $app_id = $request->get('app_id');

            $userData = $this->userRepo->find((int) $app_user_id, array('email'));
            $logged_id = Auth::user()->id;
            $listOfTemplate = $this->template->getTemplateByloggedIn($logged_id);
            return view($blade)->with('app_user_id', $app_user_id)
                            ->with('app_id', $app_id)
                            ->with('email', $userData->email)
                            ->with('emailTemplateList', $listOfTemplate);
        } catch (Exception $e) {
            return Helpers::ajaxResponse(false, null, Helpers::getExceptionMessage($e), null);
        }
    }

    /**
     * Showing the shared case form
     *
     * @param string $blade
     * @return \Illuminate\View\View
     */
    public function shareCaseView($blade) 
    {
        try {
            $request = request();
            $app_user_id = (int) $request->get('app_user_id');
            $app_id = (int) $request->get('app_id');

            $app_info = $this->application->find($app_id, array('app_id'));
            return view($blade)->with('app_user_id', $app_user_id)->with('app_id', $app_id);
        } catch (Exception $e) {
            return Helpers::ajaxResponse(false, null, Helpers::getExceptionMessage($e), null);
        }
    }

    /**
     * updating case owner and creating case owner log
     *
     * @param int $to_id
     * @param int $app_id
     * @return type
     */
    public function setCaseOwner($to_id, $app_id, $affiliate_id) 
    {
        try {
            $appData = [];
            $appData['case_owner_id'] = $to_id;
            $appData['affiliate_id'] = $affiliate_id;
            $appData['owner_assign_at'] = Helpers::getCurrentDateTime();
            $this->application->update($appData, $app_id);
            $caseOwnerLogArr = [];
            $caseOwnerLogArr['case_id'] = $app_id;
            $caseOwnerLogArr['case_owner_id'] = $to_id;
            $caseOwnerLogArr['affiliate_id'] = $affiliate_id;
            $caseOwnerLogArr['created_at'] = Helpers::getCurrentDateTime();
            $caseOwnerLogArr['created_by'] = \Auth::user()->id;
            $this->application->saveCaseOwnerLog($caseOwnerLogArr);
        } catch (\Exception $e) {
            if (empty($e->getMessage()) && $e->getStatusCode() == 400) {
                throw $e;
            } else {
                return redirect()->back()->withErrors(\Helpers::getExceptionMessage($e))->withInput();
            }
        }
    }
    
    /**
     * prepare status data
     * 
     * @param array $statusArr
     * @param array $appData
     * @param array $userData
     * 
     * return array
     */
    public function prepareStatusArrData($statusArr, $appData, $userData, $offerData)
    {
        $statusArr = array_reduce($statusArr, function($output, $element)use ($appData, $userData, $offerData){
            if(in_array($appData['current_status'], config('b2c_common.GROUP_MORE_INFO_REQ_STATUS')) && ($userData['user_level_id'] == config('b2c_common.CO_USERLEVEL') || $userData['user_level_id'] == config('b2c_common.SYSTEM_ADMIN'))) {
                if($element['id'] == config('b2c_common.MORE_INFO_REQ')) {
                    $output[$element['id']] = $element;
                }
            }

            
            if($appData['current_status'] == config('b2c_common.MORE_INFO_REQ') && $userData['user_level_id'] == config('b2c_common.RM_USERLEVEL')) {
                /* if($element['id'] == config('b2c_common.MANUAL_REVIEW')) {
                    $output[$element['id']] = $element;
                } */
                
                if($element['id'] == config('b2c_common.APP_CUR_REQ_INFO_UPDATED')) {
                    $output[$element['id']] = $element;
                }
            }
            
            if($appData['current_status'] == config('b2c_common.OFFER_ACCEPTED') && $userData['user_level_id'] == config('b2c_common.RM_USERLEVEL') ) {
//                if($element['id'] == config('b2c_common.MORE_INFO_REQ')) {
//                    $output[$element['id']] = $element;
//                }
                if($element['id'] == config('b2c_common.APPEAL')) {
                    $output[$element['id']] = $element;
                }
                if($element['id'] == config('b2c_common.AMEND')) {
                    $output[$element['id']] = $element;
                }
                if($element['id'] == config('b2c_common.ACCOUNT_OPENING_REQ')) {
                    $output[$element['id']] = $element;
                }
                if($element['id'] == config('b2c_common.ADD_CREDIT_CARD')) {
                    $output[$element['id']] = $element;
                }
            }
             if($appData['current_status'] == config('b2c_common.OFFER_ACCEPTED') && $userData['user_level_id'] == config('b2c_common.BO_USERLEVEL') ) {
//                if($element['id'] == config('b2c_common.MORE_INFO_REQ')) {
//                    $output[$element['id']] = $element;
//                }
             if(in_array($element['id'], config('b2c_common.BO_TO_BO_ASSIGN_STATUS'))) {
                    $output[$element['id']] = $element;
                }
            }
            if(in_array($appData['current_status'], config('b2c_common.ACCOUNT_OPENING_GROUP_STATUS'))) {
                if(in_array($element['id'], config('b2c_common.ACCOUNT_GROUP_STATUS'))) {
                    $output[$element['id']] = $element;
                }
            }
            if($appData['current_status'] == config('b2c_common.FOLLOW_UP_COMPLETED') && $userData['user_level_id'] == config('b2c_common.BO_USERLEVEL')) {
                if(in_array($element['id'], config('b2c_common.BO_GROUP_STATUS'))) {
                    $output[$element['id']] = $element;
                }
            }
            
            if(in_array($appData['current_status'], config('b2c_common.BO_ASSIGN_STATUS')) && $userData['user_level_id'] == config('b2c_common.BO_USERLEVEL')) {
                if(in_array($element['id'], config('b2c_common.BO_SIGNED_DOC_RECEIVED_STATUS'))) {
                    $output[$element['id']] = $element;
                }
            }
            
            if(in_array($appData['current_status'], config('b2c_common.CREDIT_DOC_PREP_REQUEST_SET_IN_PROGRESS')) && $userData['user_level_id'] == config('b2c_common.BO_USERLEVEL')) {
                if(in_array($element['id'], config('b2c_common.BO_TO_BO_ASSIGN_STATUS'))) {
                    $output[$element['id']] = $element;
                }
            }
            
            
            if(($appData['current_status'] == config('b2c_common.DECLINED') || $appData['current_status'] == config('b2c_common.ADD_OFFER') || $appData['current_status'] == config('b2c_common.OFFER_DECLINED')) && $userData['user_level_id'] == config('b2c_common.RM_USERLEVEL')) {
                if($element['id'] == config('b2c_common.APPEAL')) {
                    $output[$element['id']] = $element;
                }
                
            }
            //Amend Status If Offer not decline by UW
            if(($appData['current_status'] == config('b2c_common.ADD_OFFER') || $appData['current_status'] == config('b2c_common.OFFER_DECLINED')) && ($userData['user_level_id'] == config('b2c_common.RM_USERLEVEL') && $appData['current_status'] != config('b2c_common.DECLINED'))) {
               
                if($element['id'] == config('b2c_common.AMEND')) {
                    $output[$element['id']] = $element;
                }
            }
            
            if(($appData['current_status'] == config('b2c_common.CREDIT_DOC_SENT')) && $userData['user_level_id'] == config('b2c_common.RM_USERLEVEL')) {
                if($element['id'] == config('b2c_common.SIGNED_CREDIT_DOC')) {
                    $output[$element['id']] = $element;
                }
            }
            
            if($appData['current_status'] == config('b2c_common.ADD_CREDIT_CARD') && $userData['user_level_id'] == config('b2c_common.CO_USERLEVEL')) {
                if($element['id'] == config('b2c_common.MORE_INFO_REQ')) {
                    $output[] = $element;
                }
            }
            
            if(($appData['current_status'] == config('b2c_common.ADD_OFFER')  || $appData['current_status'] == config('b2c_common.CREDIT_DOC_SENT') || $appData['current_status'] == config('b2c_common.FUNDED')) && $appData['credit_check'] != 1) {
                if($element['id'] == config('b2c_common.ADD_CREDIT_CARD')) {
                    $output[] = $element;
                }
            }
            
            
            if(($appData['current_status'] == config('b2c_common.CONDITIONAL_APPROVED'))) {  
                if(in_array($element['id'], config('b2c_common.CONDITION_MEET_STATUS'))) {
                    $output[$element['id']] = $element;
                }
            }
//             if(($appData['current_status'] == config('b2c_common.ACCOUNT_OPENING_REQ')) && $userData['user_level_id'] == config('b2c_common.BO_USERLEVEL')) {
//                if($element['id'] == config('b2c_common.FOLLOW_UP_REQUIRED')) {
//                    $output[$element['id']] = $element;
//                }
//            }
            
            if(($appData['current_status'] == config('b2c_common.FOLLOW_UP_REQUIRED') && $userData['user_level_id'] == config('b2c_common.RM_USERLEVEL'))) {  
                if(in_array($element['id'], config('b2c_common.BRM_GROUP_STATUS'))) {
                    $output[$element['id']] = $element;
                }
            }
            
            return $output;
        }, []);
        return $statusArr;
    }
    
    
    /**
     * get total unsecured amount
     * 
     * @param array $attr
     * @return mixed
     */
    public function getTotalUnsecuredAmount($attr)
    {
        if (empty($attr)) {
            abort(400);
        }
        $unsecured_approved_amount = $this->application->getUnsecuredApprovedAmount(['app_id' => $attr['app_id'], 'app_user_id' => $attr['app_user_id']]);
        $unsecured_funded_amount = $this->application->getUnsecuredTotalFundedAmount(['app_id' => $attr['app_id'], 'app_user_id' => $attr['app_user_id']]);
        return (!empty($unsecured_approved_amount) ? $unsecured_approved_amount : 0 ) + (!empty($unsecured_funded_amount) ? $unsecured_funded_amount : 0);
    }
}
