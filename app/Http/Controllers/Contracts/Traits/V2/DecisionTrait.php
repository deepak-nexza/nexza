<?php

namespace App\Http\Controllers\Contracts\Traits\V2;

use Helpers;
use App\Http\Controllers\Contracts\Traits\PrepareDataTrait;

trait DecisionTrait {
    
     
    /**
     * decision engine and limit calculation
     * 
     * @param array $attributes
     */
    public function decisionEngine($attributes)
    {
        $riskFactor = null;
        $riskFactorArr = [];
        $ownerScore = [];
        $equifaxPR = [];
        $keyOwners = [];
        $equifaxPRReasons = [];
        $equifaxReasons = [];
        $consumerAlert = 2;
        $personal_debt = 0;
        $is_owner_esc_verified = 2;
        $appId = $attributes['app_id'];
        $appUserId = $attributes['app_user_id'];
        $isBackend  = !empty($attributes['is_backend']) ? $attributes['is_backend'] : null;
        $this->application->deleteDecisionReasonCode(['app_id' => $appId, 'app_user_id' => $appUserId]);
        $select = ['app_owner.app_user_id', 'app_owner.app_id', 'app_owner.app_owner_id', 'app_owner.own_percentage', 'app_owner.dob', 'app_owner.state_id', 'app_owner.is_guarantor', 'app_owner.res_data_id'];
        $relations = ['ownerCreditBureauInfo', 'equifaxStatements', 'equifaxSpecialServices', 'ownerEquifaxTrade', 'equifaxTradeNarrative'];
        $ownerData = $this->application->getAllConditionalOwnerData(['app_user_id'=>$appUserId, 'app_id'=>$appId, 'order_by' => 'own_percentage'], $select, $relations)->toArray();
        $totalOwner = count($ownerData);
        $appData = $this->application->find($appId);
        if(count($ownerData) > 0) {
            //key owners for segmentation
            foreach($ownerData as $owner) {
                if($owner['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA_51_PERCENT')) {
                    $ownerScore = [];
                    $ownerScore = $this->setOwnerScores($owner, $ownerScore);
                    break;
                } else if($owner['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA')) {
                    $ownerScore = $this->setOwnerScores($owner, $ownerScore);
                }
            }
            
            //key owners for equifax PR and BR
            foreach($ownerData as $ownerDetail) {
                if($ownerDetail['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA')) {
                    $keyOwners[] = $ownerDetail;
                }
                //primary owner esc verified
                if($ownerDetail['is_guarantor'] == 1 && !empty($ownerDetail['res_data_id'])) {
                    $is_owner_esc_verified = 1;
                }
            }

            if(count($keyOwners) > 0) {
                foreach($keyOwners as $key => $owners) {
                    //personal risk factor
                    $notCurrent = 2;
                    $thinFile = 0;
                    $dpd = 2;
                    $chargeOff = 2;
                    $collectionCount = 0;
                    $legalSuit = 2;
                    $fraud = 2;
                    $bankrupt = 2;
                    $hitStrength = 00;
                    $inquiryCount = 0;
                    
                    //equifax trade details
                    if(!empty($owners['owner_credit_bureau_info'])) {
                        $notCurrent = !empty($owners['owner_credit_bureau_info']['is_not_current'])?$owners['owner_credit_bureau_info']['is_not_current']:$notCurrent;
                        $thinFile = !empty($owners['owner_credit_bureau_info']['inactive_trades_no'])?$owners['owner_credit_bureau_info']['inactive_trades_no']:$thinFile;
                        $dpd = !empty($owners['owner_credit_bureau_info']['is_serious_derog'])?$owners['owner_credit_bureau_info']['is_serious_derog']:$dpd;
                        $chargeOff = !empty($owners['owner_credit_bureau_info']['is_charge_off'])?$owners['owner_credit_bureau_info']['is_charge_off']:$chargeOff;
                        $collectionCount = !empty($owners['owner_credit_bureau_info']['collection_filed_count'])?$owners['owner_credit_bureau_info']['collection_filed_count']:$collectionCount;
                        $legalSuit = !empty($owners['owner_credit_bureau_info']['is_legal_suits'])?$owners['owner_credit_bureau_info']['is_legal_suits']:$legalSuit;
                        $fraud = !empty($owners['owner_credit_bureau_info']['any_warning'])?$owners['owner_credit_bureau_info']['any_warning']:$fraud;
                        $bankrupt = !empty($owners['owner_credit_bureau_info']['is_bankrupt'])?$owners['owner_credit_bureau_info']['is_bankrupt']:$bankrupt;
                        $hitStrength = !empty($owners['owner_credit_bureau_info']['hit_strength'])?$owners['owner_credit_bureau_info']['hit_strength']:$hitStrength;
                        $inquiryCount = !empty($owners['owner_credit_bureau_info']['inquiries_no'])?$owners['owner_credit_bureau_info']['inquiries_no']:$inquiryCount;
                    }
                    
                    $statementData = isset($owners['equifax_statements']) ? $owners['equifax_statements'] : [];
                    $servicesData = isset($owners['equifax_special_services']) ? $owners['equifax_special_services'] : [];
                    $equifaxTrade = isset($owners['owner_equifax_trade']) ? $owners['owner_equifax_trade'] : [];
                    $tradeNarrative = isset($owners['equifax_trade_narrative']) ? $owners['equifax_trade_narrative'] : [];
                    
                    if(count($equifaxTrade) > 0) {
                        $equifaxTrade = array_reduce($equifaxTrade, function ($output, $element) {
                            $output[$element['trade_id']] = $element;
                            return $output;
                        });
                        
                        if(count($tradeNarrative) > 0) {
                            $tradeNarrative = array_reduce($tradeNarrative, function ($output, $element) {
                                $output[$element['trade_id']][] = $element;
                                return $output;
                            });
                        }
                        //personal debt calc for limit
                        $personalDebt = Helpers::payementRateTermCalc($equifaxTrade, $tradeNarrative, $appData->legal_entity_id);
                        $personalDebtArr[] = $personalDebt * ($owners['own_percentage']/100);
                        $personal_debt = array_sum($personalDebtArr);
                    }

                    if(count($statementData) > 0 || count($servicesData) > 0) {
                        $consumerAlert = 1;
                    }
                    $dataEquiPrArr = [
                        'equifax_pr_rate' => true,
                        'not_current' => $notCurrent,
                        'serious_derog' => $dpd,
                        'charge_off' => $chargeOff,
                        'collection_filed' => $collectionCount,
                        'legal_suit' => $legalSuit,
                        'fraud' => $fraud,
                        'bankrupt' => $bankrupt,
                        'hit_strength' => $hitStrength,
                        'thin_file' => $thinFile,
                        'high_inquiries' => $inquiryCount
                    ];
                    $result = $this->ruleEngineDataPrepare($dataEquiPrArr);
                    if(!empty($result['all_matching_decision'])) {
                        $equiPr = array_column($result['all_matching_decision'], 'decision');
                        $equifaxPR = array_merge($equiPr, $equifaxPR);
                        $equifaxReasons = isset($result['all_matching_decision']) ? $result['all_matching_decision'] : [];
                        $equifaxPRReasons = array_merge($equifaxReasons, $equifaxPRReasons);
                    } else {
                        $equifaxPR = array_merge([$result['status']], $equifaxPR);
                    }
                }
            }
        }
        $equifaxBr = null;
        $equifaxPr = count($equifaxPR) > 0 ? max($equifaxPR) : null;
        //save equifax pr reasons code
        if(count($equifaxPRReasons) > 0) {
            $prResultData = Helpers::prepareEquifaxRiskFactorReasonData($equifaxPRReasons, $appUserId, $appId);
            $this->application->saveDecisionReasonCode($prResultData);
            $this->application->saveDecisionReasonCodeLog($prResultData);
        }
        $retChecks = 0;
        $judgements = 0;
        $legalSuit = 0;
        $collectionAmount = 0;
        $custNotCurrent = 2;
        $derogCount = 0;
        $businessBankrupt = 2;
        $hitStrength = 00;
        $monthsInBiz = 0;
        $equifaxBrVal = [];
        $appBizDetail = $this->application->getBizInformation($appUserId, $appId);
        if($appBizDetail) {
            $monthsInBiz = !empty($appBizDetail->date_established) ? Helpers::calculateMonthsDiff($appBizDetail->date_established ): 0;
        }
        if(!empty($appBizDetail->businessCreditBureauInfo)) {
            $retChecks = $appBizDetail->businessCreditBureauInfo->returned_cheques_amount;
            $judgements = $appBizDetail->businessCreditBureauInfo->judgement_count;
            $legalSuit = $appBizDetail->businessCreditBureauInfo->legal_suits_amount;
            $collectionAmount = $appBizDetail->businessCreditBureauInfo->collection_claims_amount;
            $custNotCurrent = $appBizDetail->businessCreditBureauInfo->deliquent_on_trade;
            $derogCount = $appBizDetail->businessCreditBureauInfo->derogatory_records;
            $businessBankrupt = $appBizDetail->businessCreditBureauInfo->is_bankrupt;
            $hitStrength = 11;
        }
        //equifax br rule engine
        $dataEquiBrArr = [
            'equifax_br_rate' => true,
            'not_current' => $custNotCurrent,
            'ret_cheques' => $retChecks,
            'judgements' => $judgements,
            'collection_amount' => $collectionAmount,
            'legal_suit' => $legalSuit,
            'derog_count' => $derogCount,
            'bankrupt' => $businessBankrupt,
            'hit_strength' => $hitStrength,
        ];
        $result = $this->ruleEngineDataPrepare($dataEquiBrArr);
        if(!empty($result['all_matching_decision'])) {
            $equiBr = array_column($result['all_matching_decision'], 'decision');
            $equifaxBrVal = array_merge($equiBr, $equifaxBrVal);
            $brResultData = Helpers::prepareEquifaxRiskFactorReasonData($result['all_matching_decision'], $appUserId, $appId);
            $this->application->saveDecisionReasonCode($brResultData);
            $this->application->saveDecisionReasonCodeLog($brResultData);
        } else {
            $equifaxBrVal = [$result['status']];
        }
        $equifaxBr = count($equifaxBrVal) > 0 ? max($equifaxBrVal) : null;
        if(count($ownerScore) > 0) {
            foreach($ownerScore as $key=>$fico) {
                $result = $this->ruleEngineDataPrepare(['risk_factor_calc' => true, 'bni_score' => $fico['bni'], 'fico_score' => $fico['fico'], 'age_of_trade' => $fico['age_of_trade']]);
                $riskFactorArr[$key] = $result['status'];
                $ownerArr = [];
                $ownerArr['fico_score'] = $fico['fico'];
                $ownerArr['bni_score'] = $fico['bni'];
                $ownerArr['age_of_trade'] = $fico['age_of_trade'];
                $this->application->saveOwnerInfo($ownerArr, $key);
                
            }
        }

        if(count($riskFactorArr) > 0) {
            //best fico
            $riskFactor = min($riskFactorArr);
            $best_fico_bni_data = Helpers::calcBestFicoBniData(['riskFactorArr' => $riskFactorArr, 'ownerScore' => $ownerScore]);
            if($best_fico_bni_data) {
                $arrScoreData['fico_score'] = isset($best_fico_bni_data['fico']) ? $best_fico_bni_data['fico'] : null;
                $arrScoreData['bni_score'] = isset($best_fico_bni_data['bni']) ? $best_fico_bni_data['bni'] : null;
                $this->application->updateApplication((int) $appId, $arrScoreData);
            }
        }
       
        //call rule engine api for risk factor
        $data = ['app_user_id' => $appUserId, 
            'app_id' => $appId, 
            'owner_count' => $totalOwner,
            'risk_factor' => $riskFactor,
            'equifax_pr' => $equifaxPr,
            'equifax_br' => $equifaxBr,
            'is_backend' => $isBackend,
            'months_in_biz' => $monthsInBiz,
            'consumer_alert' => $consumerAlert,
            'is_owner_esc_verified' => $is_owner_esc_verified,
            'ownerScore' => $ownerScore,
            'personal_debt' => $personal_debt
        ];
        
        $message = trans('activity_messages.decision_hit',['page'=> 'Decision Engine']);
        Helpers::trackApplicationActivity($message, $appUserId, $appId);
        $this->riskFactorCalculationV2($data, $hitStrength);
    }
    
    /**
     * rule engine api for risk factor, interest rate and risk multiplier
     * 
     * @param array $attributes
     */
    public function riskFactorCalculationV2($attributes, $hitStrength)
    {
        $appUserId      = $attributes['app_user_id'];
        $appId           = $attributes['app_id'];
        $isBackend       = $attributes['is_backend'];
        $riskFactor      = $attributes['risk_factor'];
        $monthsInBiz      = $attributes['months_in_biz'];
        $personal_debt = $attributes['personal_debt'] * 12;
        $default_value = -1;
        $interest_rate = null;
        $over_all_risk_factor = null;
        $debtResult = $this->application->getDebtCapacity(['app_user_id'=>$appUserId, 'app_id'=>$appId])->toArray();
     
        $existing_dscr  = isset($debtResult[0]['existing_dscr']) ? $debtResult[0]['existing_dscr'] : $default_value;
        $nsf_last_3_month = isset($debtResult[0]['nsf_last_3_month']) ? $debtResult[0]['nsf_last_3_month'] : $default_value;
        $nsf_last_12_month = isset($debtResult[0]['nsf_last_12_month']) ? $debtResult[0]['nsf_last_12_month'] : $default_value;
        $ebitda = isset($debtResult[0]['ebitda']) ? $debtResult[0]['ebitda'] : null;
        $tot_existing_debt = isset($debtResult[0]['tot_existing_debt']) ? $debtResult[0]['tot_existing_debt'] : null;
        if(!empty($ebitda) && ($tot_existing_debt > 0 || $personal_debt > 0)) {
            $existing_dscr = $ebitda/($tot_existing_debt+$personal_debt);
        } else {
            $existing_dscr = $ebitda;
        }
        $dataArr = ['overall_risk_factor' => true, 'equifax_pr' => $attributes['equifax_pr'], 'equifax_br' => $attributes['equifax_br'], 'segmentation' => $riskFactor, 'dscr' => isset($existing_dscr) ? $existing_dscr : $default_value, 'nsf_three_months' => $nsf_last_3_month, 'nsf_twelve_months'=>$nsf_last_12_month, 'months_in_biz' => $monthsInBiz];
       
        $result = $this->ruleEngineDataPrepare($dataArr);
        if(isset($result['status']) && $result['status'] > 0) {
            $over_all_risk_factor = $result['status'];
        }
        
        $prime_interest_rate = $this->userRepo->getActivePrimeRate();
        if(!empty($prime_interest_rate)) {
            $interest_rate = $prime_interest_rate + 2;
        }

        $where = ['app_user_id'=>$appUserId, 'app_id'=>$appId];
        $arrData = [ 'app_user_id'=>$appUserId, 
            'app_id'=>$appId, 
            'risk_factor'=>$riskFactor,
            'system_risk_factor'=>$over_all_risk_factor,
            'equifax_pr'=>$attributes['equifax_pr'],
            'equifax_br'=>$attributes['equifax_br'],
            'personal_debt' => $personal_debt,
       ];
        //save credit limit data
        $this->application->saveCreditLimit($where, $arrData);
        
        if(isset($attributes['risk_factor'])) {
            //limit calculator
            $data = ['app_user_id' => $appUserId, 
                'app_id' => $appId, 
                'interest_rate' => $interest_rate, 
                'risk_factor' => $riskFactor,
                'over_all_risk_factor' => $over_all_risk_factor,
                'owner_count' => $attributes['owner_count'],
                'equifax_pr' => $attributes['equifax_pr'],
                'equifax_br' => $attributes['equifax_br'],
                'debtResult' => $debtResult,
                'is_backend' => $isBackend,
                'consumer_alert' => $attributes['consumer_alert'],
                'is_owner_esc_verified' => $attributes['is_owner_esc_verified'],
                'ownerScore' => $attributes['ownerScore'],
                'personal_debt' => $personal_debt,
                'existing_dscr' => $existing_dscr
            ];
            $this->limitCalculatorV2($data, $hitStrength, $monthsInBiz);
        }
    }
    
    /**
     * limit calculation and final decision
     * 
     * @param array $attributes
     */
    public function limitCalculatorV2($attributes, $hitStrength, $monthsInBiz)
    {
        $codeArr = $referArr = $declineCodeArr = [];
        $appId         = $attributes['app_id'];
        $appUserId     = $attributes['app_user_id'];
        $isBackend     = $attributes['is_backend'];
        $personal_debt  = $attributes['personal_debt'];
        $existing_dscr  = $attributes['existing_dscr'];
        $threshold_dscr = config('b2c_common.THRESHOLD_DSCR');
        $new_interest_rate = ($attributes['interest_rate']/100);
        $interest_rate = $attributes['interest_rate'];
        $amortization_years = config('b2c_common.AMORTIZATION_YEARS');
        $room_for_debt = $unsecured_max = $operating_cost = null;
        $appData = $this->application->find($appId);
        $temp_other_income = 0;

        $debtResult = $attributes['debtResult'];
        $ebitda         = isset($debtResult[0]['ebitda']) ? $debtResult[0]['ebitda'] : null;
        $revenue        = isset($debtResult[0]['revenue']) ? $debtResult[0]['revenue'] : null;
        $other_income = isset($debtResult[0]['other_income']) ? $debtResult[0]['other_income'] : null;
        $tot_existing_debt = isset($debtResult[0]['tot_existing_debt']) ? $debtResult[0]['tot_existing_debt'] : null;
        $temp_nsf_last_3_month = isset($debtResult[0]['nsf_last_3_month']) ? $debtResult[0]['nsf_last_3_month'] : null;
        $temp_nsf_last_12_month = isset($debtResult[0]['nsf_last_12_month']) ? $debtResult[0]['nsf_last_12_month'] : null;
        $temp_bank_stmt = isset($debtResult[0]['is_available_recent_stmt']) ? $debtResult[0]['is_available_recent_stmt'] : null;
        $is_manual_stmt = isset($debtResult[0]['is_manual_stmt']) ? $debtResult[0]['is_manual_stmt'] : null;
        $temp_sum_loan_credit = isset($debtResult[0]['sum_loan_credit']) ? $debtResult[0]['sum_loan_credit'] : null;
        $operating_cost = $revenue - $ebitda;
        $default_operating_val = config('b2c_common.DEFAULT_OPERATING_VAL_DECISION');
        if($revenue == null || $ebitda == null) {
            $operating_cost = $default_operating_val;
        }
        if($other_income > 0) {
            $temp_other_income = ($revenue > 0) ? (($other_income/$revenue)*100) : 0;
        }

        if($appData['annual_sale_amt'] > 0) {
            $revenue_variance = ((($appData['annual_sale_amt'] - $revenue)/$appData['annual_sale_amt'])*100);
        } else {
            $revenue_variance = 0;
        }
        $getAllscannedPdf = \Helpers::getAllnonNativeDoc($appId,2);
        $available_debt = $ebitda/$threshold_dscr;
        $unsecured_max = config('b2c_common.UNSECURED_MAX');
        $total_debt = $personal_debt + $tot_existing_debt;
        $room_for_debt = ($available_debt - $total_debt);
        $room_for_debt = isset($room_for_debt) && $room_for_debt >= 0 ? $room_for_debt : 0;
        $future_value = 0;
        $pv_result = Helpers::presentValue($new_interest_rate, $amortization_years, -$room_for_debt, $future_value);
        $pv_result = ($pv_result > 0) ? $pv_result : 0;
        $amount_requested = (int) $appData['loan_amount'];
        $revenue_limit = ($revenue * (20/100));
        $limitArr = [$unsecured_max, $pv_result, $amount_requested, $revenue_limit];
        $dscr_based_limit = min($limitArr);
        if($new_interest_rate == null) {
            $dscr_based_limit = null;
        }
        
        $secured_exposer = $unsecured_exposer = 0;
        $custBureauData = $this->application->getAllCustCreditBureauData(['app_id'=>$appId], ['unsecured_exposer', 'secured_exposer'])->toArray();
        if(count($custBureauData) > 0 && ($appData->legal_entity_id == 1 || $appData->legal_entity_id == 2)) {
            foreach($custBureauData as $custData) {
                $secured_exposer = $secured_exposer + $custData['secured_exposer'];
                $unsecured_exposer = $unsecured_exposer + $custData['unsecured_exposer'];
            }
        }
        //exposure calc
        $max_internal_exposer = (config('b2c_common.MAX_SECURED_LIMIT') - ($secured_exposer + $unsecured_exposer));
        $max_internal_exposer = ($max_internal_exposer > 0) ? $max_internal_exposer : 0;
        $max_unsecured_exposure = $max_internal_exposer - $unsecured_exposer;
        $max_unsecured_exposure = ($max_unsecured_exposure > 0) ? $max_unsecured_exposure : 0;
        $max_secured_exposure = $max_unsecured_exposure - $secured_exposer;
        $max_secured_exposure = ($max_secured_exposure > 0) ? $max_secured_exposure : 0;
        $security_asset_value = isset($appData['security_assessed_val']) ? $appData['security_assessed_val'] : config('b2c_common.SECURITY_ASSESSED_VALUE');
        
        $limit = Helpers::calcStandardLimit($dscr_based_limit, $max_internal_exposer, $security_asset_value, $unsecured_exposer);
        $where = ['app_user_id'=>$appUserId, 'app_id'=>$appId];
        $arrData = [ 'max_debt'=> $unsecured_max, 'debt_room' => $room_for_debt, 'risk_capacity' =>$pv_result, 'cust_limit'=>$limit];
        //save credit limit data
        $this->application->saveCreditLimit($where, $arrData);
        if(isset($debtResult[0]['id'])) {
            $this->application->saveTempIncomeLiability(['existing_dscr' => $existing_dscr], (int)$debtResult[0]['id']);
        }
        $default_value = -1;
        $esc_business_data = isset($appData['esc_res_data_id']) ? 1 : 2;
        $industry_cls = \Helpers::getIndustryName($appData['industry_id'])->class;
        $dataArr = [
            'final_decision' => true, 
            'line_of_credit' => (int) $appData['loan_amount'], 
            'risk_factor' => isset($attributes['risk_factor']) ? (int) $attributes['risk_factor'] : $default_value, 
            'loan_credits'=> isset($temp_sum_loan_credit) ? $temp_sum_loan_credit : $default_value, 
            'existing_dscr' => isset($existing_dscr) ? $existing_dscr : $default_value, 
            'legal_entity_id' => $appData['legal_entity_id'],
            'owner_count' => $attributes['owner_count'],
            'nsf_3_months' => isset($temp_nsf_last_3_month) ? $temp_nsf_last_3_month : $default_value,
            'nsf_12_months' => isset($temp_nsf_last_12_month) ? $temp_nsf_last_12_month : $default_value,
            'equifax_br' => isset($attributes['equifax_br']) ? (int) $attributes['equifax_br'] : $default_value,
            'equifax_pr' => isset($attributes['equifax_pr']) ? (int) $attributes['equifax_pr'] : $default_value,
            'revenue_variance' => ($revenue_variance > 0) ? $revenue_variance : 0,
            'bank_stmt' => ($getAllscannedPdf > 0 && $is_manual_stmt!=1) ? 2 :
            (isset($temp_bank_stmt)  ? $temp_bank_stmt : 2),
            'other_income_percentage' => isset($temp_other_income) ? $temp_other_income : $default_value,
            'iovation' => isset($appData['fraud_score']) ? $appData['fraud_score'] : 0,
            'owner_percentage' => $appData['legal_age_owners_perc'],
            'esc_business' => $esc_business_data,
            'primary_owner_esc' => isset($attributes['is_owner_esc_verified']) ? $attributes['is_owner_esc_verified'] : 2,
            'limit_lt_lowlimit' => isset($limit) ? $limit : $default_value,
            'hit_strength' => $hitStrength,
            'months_in_biz' => $monthsInBiz,
            'consumer_alert' => $attributes['consumer_alert'],
            'is_missing_trans' => isset($debtResult[0]['is_missing_trans']) ? $debtResult[0]['is_missing_trans'] : $default_value,
            'operating_cost' => isset($operating_cost) ? $operating_cost : $default_operating_val,
            'sic_professional' => !empty($industry_cls) ? $industry_cls : $default_value
        ];
        $result = $this->ruleEngineDataPrepare($dataArr);
        if(isset($result['status'])) {
            $decision = Helpers::decisionByRuleEngine($result['status']);
        }
        $arrAppData['decision'] = $decision;
        $loggedInData = \Auth::user();
        $curent_date = Helpers::getCurrentDateTime();
        $programme_30k = true;
        $program_type = config('b2c_common.STANDARD_PROGRAM');
        $new_system_risk_factor = $attributes['over_all_risk_factor'];
        $new_segmentation = isset($attributes['risk_factor']) ? (int) $attributes['risk_factor'] : $default_value;
        $reasonsData = $reasonsDataLog = [];
        if(isset($result['all_matching_decision'])) {
            $decisionResult = $result['all_matching_decision'];
            $is_refer = true;
            if(count($decisionResult) > 0) {
                foreach($decisionResult as $result) {
                    $resultData[] = [
                        'app_user_id' => $appUserId,
                        'app_id' => $appId,
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'created_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'updated_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'created_at' => $curent_date,
                        'updated_at' => $curent_date,
                    ];
                    $resultDataNew[] = [
                        'app_user_id' => $appUserId,
                        'app_id' => $appId,
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'is_auto_manual' => isset($isBackend) ? 2 : 1,
                        'created_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'updated_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'created_at' => $curent_date,
                        'updated_at' => $curent_date,
                    ];
                    
                    if(!empty($result->program) && $result->program == 'FALSE') {
                        $programme_30k = false;
                        if($result->decision == 'Approved' || $result->decision == 'Decline') {
                            $is_refer = false;
                        }
                    }
                }
                $reasonsData = $resultData;
                $reasonsDataLog = $resultDataNew;
            } else {
                $arrReasonData = [];
                $arrReasonData['app_user_id'] =  $appUserId;
                $arrReasonData['app_id'] =  $appId;
                $arrReasonData['decision'] =  isset($result['status']) ? $result['status'] : null;
                $arrReasonData['code'] =  isset($result['code']) ? $result['code'] : null;
                $arrReasonData['reason'] =  isset($result['decision_desc']) ? $result['decision_desc'] : null;
                $arrReasonData['created_by'] =  isset($loggedInData->id) ? $loggedInData->id : null;
                $arrReasonData['updated_by'] =  isset($loggedInData->id) ? $loggedInData->id : null;
                $arrReasonData['created_at'] =  $curent_date;
                $arrReasonData['updated_at'] =  $curent_date;
                $this->application->saveDecisionReasonCode($arrReasonData);
                $this->application->saveDecisionReasonCodeLog($arrReasonData);
            }
            
            if($programme_30k == false && $is_refer == true) {
                $arrAppData['decision'] = config('b2c_common.REFER');
            }
            $stated_revenue_20_percent = $appData['annual_sale_amt'] * 20/100;
            $returnData = [];
            $high_risk_industry = \Helpers::getIndustryName($appData['industry_id'])->is_high_credit_industry;
            $new_30k_approved_amt = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.30k_PROGRAM'));
            $ownerScore = $attributes['ownerScore'];
            $limit_program_type = config('b2c_common.STANDARD_PROGRAM');
            $standard_limit = $limit;
            if($decision == config('b2c_common.APPROVE')) {
                $limitArr = [$limit, $new_30k_approved_amt];
                $max_program_type_limit = array_keys($limitArr, max($limitArr));
                if(isset($max_program_type_limit[0]) && $max_program_type_limit[0] == config('b2c_common.30K_PROGRAM_MAX_KEY')) {
                    $limit = $new_30k_approved_amt;
                    $limit_program_type = config('b2c_common.30k_PROGRAM');
                    $program_type = config('b2c_common.30k_PROGRAM');
                    if(count($ownerScore)) {
                        $returnData = $this->calcProgramSegmentationRule($ownerScore, $appId, '30k_segmentation');
                        $new_segmentation = $returnData['risk_factor'];
                    }
                    $dataArr['high_credit_industry'] = $high_risk_industry;
                    $dataArr['30k_risk_factor'] = $new_segmentation;
                    $new_system_risk_factor = $this->programOverALlRiskFactorRule($dataArr);
                    $dataArr['30k_fico_score'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : null;
                    $dataArr['30k_bni_score'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : null;
                    $dataArr['30k_risk_factor'] = $new_segmentation;
                    $dataArr['limit_lt_lowlimit'] = $limit;
                    $dataArr['overall_risk_factor'] = $new_system_risk_factor;
                    $prepare30kData = Helpers::prepare30kProgrammeData($dataArr);
                    $programme30kResult = $this->ruleEngineDataPrepare($prepare30kData);
                    $arrAppData['decision'] = Helpers::decisionByRuleEngine($programme30kResult['status']);
                }
            }

            if(count($resultData) > 0) {
                $reason_codes = array_column($resultData, 'code');
                if(($decision == config('b2c_common.REFER') || $decision == config('b2c_common.DECLINE')) && count(array_intersect($reason_codes, config('b2c_common.HARD_DECLINE_PARAMS'))) == 0) {
                    if(in_array('R22', $reason_codes)) {
                        $limit_program_type = config('b2c_common.5k_PROGRAM');
                        $limit = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.5k_PROGRAM'));
                    } else if($high_risk_industry == 1 && count(array_intersect($reason_codes, config('b2c_common.BANK_STMT_PARAMS'))) > 0) {
                        $limit_program_type = config('b2c_common.5k_PROGRAM');
                        $limit = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.5k_PROGRAM'));
                    } else if($high_risk_industry == null) {
                        $limit_program_type = config('b2c_common.30k_PROGRAM');
                        $limit_30k = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.30k_PROGRAM'));
                        $newLimitArr = [$standard_limit, $limit_30k];
                        $new_max_program_type_limit = array_keys($newLimitArr, max($newLimitArr));
                        $limit = max($newLimitArr);
                        if(isset($new_max_program_type_limit[0]) && $new_max_program_type_limit[0] == config('b2c_common.STANDARD_PROGRAM_MAX')) {
                            $limit_program_type = config('b2c_common.STANDARD_PROGRAM');
                        }
                    }
                }
            }

            if($programme_30k == true && $decision != config('b2c_common.APPROVE')) {
                $program_type = config('b2c_common.30k_PROGRAM');
                $limit_program_type = config('b2c_common.30k_PROGRAM');
                if(count($ownerScore)) {
                    $returnData = $this->calcProgramSegmentationRule($ownerScore, $appId, '30k_segmentation');
                    $new_segmentation = $returnData['risk_factor'];
                }
                $dataArr['high_credit_industry'] = $high_risk_industry;
                $dataArr['30k_risk_factor'] = $new_segmentation;
                $new_system_risk_factor = $this->programOverALlRiskFactorRule($dataArr);
                $limit = $new_30k_approved_amt;
                $dataArr['30k_fico_score'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : null;
                $dataArr['30k_bni_score'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : null;
                $dataArr['30k_risk_factor'] = $new_segmentation;
                $dataArr['limit_lt_lowlimit'] = $limit;
                $dataArr['overall_risk_factor'] = $new_system_risk_factor;
                $prepare30kData = Helpers::prepare30kProgrammeData($dataArr);
                $programme30kResult = $this->ruleEngineDataPrepare($prepare30kData);
                $arrAppData['decision'] = Helpers::decisionByRuleEngine($programme30kResult['status']);
                //5k program rule
                if($programme30kResult['status'] != 'Approved') {
                    $program_type = config('b2c_common.5k_PROGRAM');
                    $limit_program_type = config('b2c_common.5k_PROGRAM');
                    $returnData = [];
                    $program30kReasons = $this->prepareAndSaveProgramReasons($programme30kResult, $appUserId, $appId, $curent_date, $loggedInData->id, $isBackend);
                    if(count($ownerScore)) {
                        $returnData = $this->calcProgramSegmentationRule($ownerScore, $appId, '5k_segmentation');
                        $new_segmentation = $returnData['risk_factor'];
                    }
                    $dataArr['segmentation'] = $new_segmentation;
                    $new_system_risk_factor = $this->program5kOverALlRiskFactorRule($dataArr);

                    //new approval limit calc
                    $new_approved_amt = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.5k_PROGRAM'));
                    $limit = $new_approved_amt;
                    $prepare30kData['5k_fico_score'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : null;
                    $prepare30kData['5k_bni_score'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : null;
                    $prepare30kData['5k_risk_factor'] = $new_segmentation;
                    $prepare30kData['limit_lt_lowlimit'] = $limit;
                    $prepare30kData['overall_risk_factor'] = $new_system_risk_factor;
                    $prepare5kData = Helpers::prepare5kProgrammeData($prepare30kData);
                    $programme5kResult = $this->ruleEngineDataPrepare($prepare5kData);
                    $arrAppData['decision'] = Helpers::decisionByRuleEngine($programme5kResult['status']);
                    $program5kReasons = $this->prepareAndSaveProgramReasons($programme5kResult, $appUserId, $appId, $curent_date, $loggedInData->id, $isBackend);

                    $finalReasonArr = array_merge($program5kReasons['programArr'], $program30kReasons['programArr'], $resultData);
                    $finalReasonLogArr = array_merge($program5kReasons['programLogArr'], $program30kReasons['programLogArr'], $resultDataNew);
                    if(count($finalReasonArr) > 0) {
                        $reasonsData = array_map('unserialize', array_unique(array_map('serialize', $finalReasonArr)));
                        $reasonsDataLog = array_map('unserialize', array_unique(array_map('serialize', $finalReasonLogArr)));
                        $initial_decision = '';
                        if($arrAppData['decision'] == config('b2c_common.APPROVE')) {
                            $initial_decision = 'Approve';
                        } else if($arrAppData['decision'] == config('b2c_common.REFER')) {
                            $initial_decision = 'Refer';
                        } else {
                            $initial_decision = 'Decline';
                        }
                        array_walk($reasonsDataLog, function(&$newArray) use ($initial_decision) {
                            $newArray['final_decision'] = $initial_decision;
                        });
                    }
                }
            }
        }
        
        if(count($reasonsData) > 0) {
            $this->application->saveDecisionReasonCode($reasonsData);
            $this->application->saveDecisionReasonCodeLog($reasonsDataLog);
        }
        
        //final interest rate calc
        $final_interest_rate = null;
        if($new_system_risk_factor > 0) {
            $sysRiskResult = $this->ruleEngineDataPrepare(['interest_rate' => true, 'risk_factor' => $new_system_risk_factor]);
            if(isset($sysRiskResult['status']) && $sysRiskResult['status'] > 0) {
                $final_interest_rate = $sysRiskResult['status'];
            }
        }
        
        $arrAppData['risk_factor'] = isset($new_segmentation) ? $new_segmentation : null;
        $arrAppData['cust_limit'] = isset($limit) ? $limit : null;
        $arrAppData['program_type'] = isset($program_type) ? $program_type : null;
        $arrAppData['decision_code'] = isset($result->code) ? $result->code : null;
        $arrAppData['decision_desc'] = isset($result->text) ? $result->text : null;
        $arrAppData['secured_exposer'] = isset($secured_exposer) ? $secured_exposer : null;
        $arrAppData['unsecured_exposer'] = isset($unsecured_exposer) ? $unsecured_exposer : null;
        $arrAppData['max_internal_exposer'] = isset($max_internal_exposer) ? $max_internal_exposer : null;
        $arrAppData['max_secured_exposure'] = isset($max_secured_exposure) ? $max_secured_exposure : null;
        $arrAppData['max_unsecured_exposure'] = isset($max_unsecured_exposure) ? $max_unsecured_exposure : null;
        $arrAppData['final_risk_factor'] = isset($new_system_risk_factor) ? $new_system_risk_factor : null;
        $arrAppData['limit_program_type'] = isset($limit_program_type) ? $limit_program_type : null;
        $this->application->updateApplication((int) $appId, $arrAppData);
        
        $where = ['app_user_id'=>$appUserId, 'app_id'=>$appId];
        $this->application->saveCreditLimit($where, ['cust_limit'=>$limit, 'risk_factor' => $new_segmentation, 'system_risk_factor' => $new_segmentation, 'interest_rate'=>$final_interest_rate]);
    }
    
    /**
     * function is used to set owners FICO/BNI/Age Of Trade Scores
     * @param type $owners
     * @param type $ownerScore
     * @return type
     */
    protected function setOwnerScores($owners, $ownerScore=[])
    {
        //owner fico
        $ownerScore[$owners['app_owner_id']]['fico'] = isset($owners['owner_credit_bureau_info']['fico_score']) ? (int) $owners['owner_credit_bureau_info']['fico_score'] : null;
        //owner bni score
        $ownerScore[$owners['app_owner_id']]['bni'] = isset($owners['owner_credit_bureau_info']['bni_score']) ? (int) $owners['owner_credit_bureau_info']['bni_score'] : null;
        //equifax trade details
        $ownerScore[$owners['app_owner_id']]['age_of_trade'] = !empty($owners['owner_credit_bureau_info']['age_of_oldest_trade']) ? (int) $owners['owner_credit_bureau_info']['age_of_oldest_trade'] : null;
        return $ownerScore;
    }
}
