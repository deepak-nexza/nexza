<?php

namespace App\Http\Controllers\Contracts\Traits;

use Helpers;

trait AssistedDecisionTrait {
     
    /**
     * decision engine and limit calculation
     * 
     * @param array $attributes
     */
    public function assistedDecisionEngine($attributes)
    {
        $riskFactor = $is_credit_manual_skip = null;
        $riskFactorArr = [];
        $ownerScore = [];
        $equifaxPR = [];
        $keyOwners = [];
        $equifaxReasons = [];
        $equifaxPRReasons = [];
        $consumerAlert = 2;
        $personal_debt = 0;
        $is_owner_esc_verified = 2;
        $appId = $attributes['app_id'];
        $appUserId = $attributes['app_user_id'];
        $isBackend  = !empty($attributes['is_backend']) ? $attributes['is_backend'] : null;
        $this->application->deleteDecisionReasonCode(['app_id' => $appId, 'app_user_id' => $appUserId]);
        $this->application->deleteAmountProductBifurcation(['app_id' => $appId, 'app_user_id' => $appUserId]);
        $select = ['app_owner.app_user_id', 'app_owner.app_id', 'app_owner.app_owner_id', 'app_owner.own_percentage', 'app_owner.dob', 'app_owner.state_id', 'app_owner.is_credit_bureau_skip', 'app_owner.is_guarantor', 'app_owner.res_data_id'];
        $relations = ['ownerCreditBureauInfo', 'equifaxStatements', 'equifaxSpecialServices', 'ownerEquifaxTrade', 'equifaxTradeNarrative'];
        $ownerData = $this->application->getAllConditionalOwnerData(['app_user_id'=>$appUserId, 'app_id'=>$appId, 'order_by' => 'own_percentage'], $select, $relations)->toArray();
        $totalOwner = count($ownerData);
        $appData = $this->application->find($appId);
        
        //loan security info
        $loan_on_cash = ($appData['cash_security_amt'] * (config('b2c_common.CASH_LTV_PERCENT')/100));
        $loan_on_realestate = ($appData['realestate_security_amt'] * (config('b2c_common.REALESTATE_LTV_PERCENT')/100)) - $appData['property_debt'];
        $security_assessed_val = $loan_on_cash + $loan_on_realestate;
        $this->application->updateApplication((int) $appId, ['loan_on_cash' => $loan_on_cash, 'loan_on_realestate' => $loan_on_realestate, 'security_assessed_val' => $security_assessed_val]);
        $owner_no_hit = $skip_bureau_reasons = false;
        if(count($ownerData) > 0) {
            //key owners for segmentation
            foreach($ownerData as $owner) {
                if($owner['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA_51_PERCENT')) {
                    $ownerScore = [];
                    $ownerScore = $this->setGlobalOwnerScores($owner, $ownerScore);
                    if($owner['is_credit_bureau_skip'] == config('b2c_common.MANUAL_SKIP') && empty($owner['owner_credit_bureau_info'])) {
                        $skip_bureau_reasons = config('b2c_common.MANUAL_SKIP');
                    }
                    break;
                } else if($owner['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA')) {
                    $ownerScore = $this->setGlobalOwnerScores($owner, $ownerScore);
                    if($owner['is_credit_bureau_skip'] == config('b2c_common.MANUAL_SKIP') && empty($owner['owner_credit_bureau_info'])) {
                        $skip_bureau_reasons = config('b2c_common.MANUAL_SKIP');
                    }
                }
            }
            
            //key owners for equifax PR and BR
            foreach($ownerData as $ownerDetail) {
                if($ownerDetail['is_credit_bureau_skip'] == config('b2c_common.MANUAL_SKIP')) {
                    $is_credit_manual_skip = config('b2c_common.MANUAL_SKIP');
                }
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
                    } else {
                        $owner_no_hit = true;
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
                        'hit_strength' => ($is_credit_manual_skip == config('b2c_common.MANUAL_SKIP')) ? '11' : $hitStrength,
                        'thin_file' => $thinFile,
                        'high_inquiries' => $inquiryCount
                    ];
                    $result = $this->assistedRuleEngineDataPrepare($dataEquiPrArr);
                    if(!empty($result['all_matching_decision'])) {
                        $equiPr = array_column($result['all_matching_decision'], 'decision');
                        $equifaxPR = array_merge($equiPr, $equifaxPR);
                        $equifaxReasons = isset($result['all_matching_decision']) ? $result['all_matching_decision'] : [];
                        $equifaxPRReasons = array_merge($equifaxReasons, $equifaxPRReasons);
                    } else {
                        if($owners['is_credit_bureau_skip'] == config('b2c_common.MANUAL_SKIP') && empty($owners['owner_credit_bureau_info'])) {
                            $pr_risk_factor = 4; //static 4 because of skip credit bureau
                        } else {
                            $pr_risk_factor = $result['status'];
                        }
                        $equifaxPR = array_merge([$pr_risk_factor], $equifaxPR);
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
            'hit_strength' => ($appData['is_biz_bureau_skip'] == config('b2c_common.MANUAL_SKIP')) ? '11' : $hitStrength,
        ];
        $result = $this->assistedRuleEngineDataPrepare($dataEquiBrArr);
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
                $result = $this->assistedRuleEngineDataPrepare(['risk_factor_calc' => true, 'bankruptcy_score' => $fico['bni'], 'fico' => $fico['fico'], 'oldest_trade' => $fico['age_of_trade']]);
                $riskFactorArr[$key] = $result['status'];
                $ownerArr = [];
                $ownerArr['fico_score'] = $fico['fico'];
                $ownerArr['bni_score'] = $fico['bni'];
                $ownerArr['age_of_trade'] = $fico['age_of_trade'];
                $this->application->saveOwnerInfo($ownerArr, $key);
            }
        }

        if(count($riskFactorArr) > 0) {
            $riskFactor = min($riskFactorArr);   //best fico
            $best_fico_bni_data = Helpers::calcBestFicoBniData(['riskFactorArr' => $riskFactorArr, 'ownerScore' => $ownerScore]);
            if($best_fico_bni_data) {
                $arrScoreData['fico_score'] = isset($best_fico_bni_data['fico']) ? $best_fico_bni_data['fico'] : null;
                $arrScoreData['bni_score'] = isset($best_fico_bni_data['bni']) ? $best_fico_bni_data['bni'] : null;
                $this->application->updateApplication((int) $appId, $arrScoreData);
            }
        }

        //rule engine api call for risk factor
        $data = ['app_user_id' => $appUserId, 
            'app_id' => $appId, 
            'owner_count' => $totalOwner,
            'risk_factor' => $riskFactor,
            'equifax_pr' => $equifaxPr,
            'equifax_br' => $equifaxBr,
            'is_backend' => $isBackend,
            'ownerScore' => $ownerScore,
            'months_in_biz' => $monthsInBiz,
            'consumer_alert' => $consumerAlert,
            'is_credit_manual_skip' => $is_credit_manual_skip,
            'is_owner_esc_verified' => $is_owner_esc_verified,
            'personal_debt' => $personal_debt,
            'owner_no_hit' => $owner_no_hit,
            'skip_bureau_reasons' => $skip_bureau_reasons
        ];
        
        $message = trans('activity_messages.decision_hit',['page'=> 'Decision Engine']);
        Helpers::trackApplicationActivity($message, $appUserId, $appId);
        $this->globalRiskFactorCalculation($data, $hitStrength);
    }
    
    /**
     * rule engine api for risk factor, interest rate and risk multiplier
     * 
     * @param array $attributes
     */
    public function globalRiskFactorCalculation($attributes, $hitStrength)
    {
        $appUserId      = $attributes['app_user_id'];
        $appId          = $attributes['app_id'];
        $isBackend      = $attributes['is_backend'];
        $riskFactor     = $attributes['risk_factor'];
        $monthsInBiz    = $attributes['months_in_biz'];
        $personal_debt  = $attributes['personal_debt'] * 12;
        $default_value  = -1;
        $interest_rate  = null;
        $over_all_risk_factor = null;
        $is_bank_stmt_rule_req = false;
        $loanPurposeIds = $loanProdIds = [];
        $loan_purpose_ids = $loan_prod_ids = $default_value;
        $appData = $this->application->find($appId);
        $appPurposeData = $this->application->getApplicationPurposeData(['app_user_id' => $appUserId, 'app_id' => $appId], ['app_purpose_id', 'product', 'loan_purpose_id', 'amount', 'loan_security', 'mst_products.max_amount', 'interset_rate', 'mst_products.min_amount'])->toArray();
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
        if(count($appPurposeData) > 0) {
            foreach($appPurposeData as $purpose) {
                if($purpose['product'] != config('b2c_common.OVERDRAFT')) {
                    $is_bank_stmt_rule_req = true;
                }
                $loanPurposeIds[] = $purpose['loan_purpose_id'];
                $loanProdIds[] = $purpose['product'];
            }
        }
        
        if($monthsInBiz < 24) {
            $is_bank_stmt_rule_req = false;
        }
        
        if(count($loanPurposeIds) > 0) {
            $loan_purpose_ids = implode(',', $loanPurposeIds);
            $loan_prod_ids = implode(',', $loanProdIds);
        }

        $dataArr = ['overall_risk_factor' => true,
            'equifax_pr' => $attributes['equifax_pr'],
            'equifax_br' => $attributes['equifax_br'],
            'segmentation' => $riskFactor,
            'dscr' => isset($existing_dscr) ? $existing_dscr : $default_value,
            'nsf_three_months' => $nsf_last_3_month,
            'nsf_twelve_months' => $nsf_last_12_month,
            'months_in_biz' => $monthsInBiz,
            'product_requested' => $loan_prod_ids,
            'negative_industry' => isset($appData['is_industry_knocked_out']) ? $appData['is_industry_knocked_out'] : 2
        ];
        $result = $this->assistedRuleEngineDataPrepare($dataArr);
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
            'personal_debt' => $personal_debt
       ];
        //save credit limit data
        $this->application->saveCreditLimit($where, $arrData);

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
            'is_credit_manual_skip' => isset($attributes['is_credit_manual_skip']) ? $attributes['is_credit_manual_skip'] : null,
            'loan_purpose_ids' => $loan_purpose_ids,
            'loan_prod_ids' => $loan_prod_ids,
            'is_bank_stmt_rule_req' => $is_bank_stmt_rule_req,
            'is_owner_esc_verified' => $attributes['is_owner_esc_verified'],
            'personal_debt' => $personal_debt,
            'existing_dscr' => $existing_dscr,
            'ownerScore' => $attributes['ownerScore'],
            'owner_no_hit' => $attributes['owner_no_hit'],
            'skip_bureau_reasons' => $attributes['skip_bureau_reasons']
        ];
        $this->globalLimitCalculator($data, $hitStrength, $monthsInBiz, $appPurposeData);
    }
    
    /**
     * limit calculation and final decision
     * 
     * @param array $attributes
     */
    public function globalLimitCalculator($attributes, $hitStrength, $monthsInBiz, $appPurposeData)
    {
        $default_value = -1;
        $programOverdraftReasons['programArr'] = $programOverdraftReasons['programLogArr'] = [];
        $bankDecision = $program30kReasons['programArr'] = $program30kReasons['programLogArr'] = $program5kReasons['programArr'] = $program5kReasons['programLogArr'] = [];
        $temp_other_income = $future_value = 0;
        $ownerScore     = $attributes['ownerScore'];
        $appId          = $attributes['app_id'];
        $appUserId      = $attributes['app_user_id'];
        $isBackend      = $attributes['is_backend'];
        $personal_debt  = $attributes['personal_debt'];
        $existing_dscr  = $attributes['existing_dscr'];
        $interest_rate  = $attributes['interest_rate'];
        $threshold_dscr = config('b2c_common.THRESHOLD_DSCR');
        $new_interest_rate = ($attributes['interest_rate']/100);
        $amortization_years = config('b2c_common.AMORTIZATION_YEARS');
        $default_operating_val = config('b2c_common.DEFAULT_OPERATING_VAL_DECISION');
        $decision = $room_for_debt = $unsecured_max = $operating_cost = $bank_decision_res = null;
        $debtResult = $attributes['debtResult'];
        $appData = $this->application->find($appId);
        $getAllscannedPdf = \Helpers::getAllnonNativeDoc($appId,2);
        
        $ebitda             = isset($debtResult[0]['ebitda']) ? $debtResult[0]['ebitda'] : null;
        $revenue            = isset($debtResult[0]['revenue']) ? $debtResult[0]['revenue'] : null;
        $other_income       = isset($debtResult[0]['other_income']) ? $debtResult[0]['other_income'] : null;
        $sum_loan_credit    = isset($debtResult[0]['sum_loan_credit']) ? $debtResult[0]['sum_loan_credit'] : null;
        $tot_existing_debt  = isset($debtResult[0]['tot_existing_debt']) ? $debtResult[0]['tot_existing_debt'] : null;
        $temp_nsf_last_3_month  = isset($debtResult[0]['nsf_last_3_month']) ? $debtResult[0]['nsf_last_3_month'] : null;
        $temp_nsf_last_12_month = isset($debtResult[0]['nsf_last_12_month']) ? $debtResult[0]['nsf_last_12_month'] : null;
        $temp_bank_stmt = isset($debtResult[0]['is_available_recent_stmt']) ? $debtResult[0]['is_available_recent_stmt'] : null;
        $is_manual_stmt = isset($debtResult[0]['is_manual_stmt']) ? $debtResult[0]['is_manual_stmt'] : null;
        $getAllscannedPdf = \Helpers::getAllnonNativeDoc($appId,2);
        $operating_cost = $revenue - $ebitda;
        if($revenue == null || $ebitda == null) {
            $operating_cost = $default_operating_val;
        }
        if($other_income > 0) {
            $temp_other_income = ($revenue > 0) ? (($other_income/$revenue)*100) : 0;
        }
        $revenue_variance = ($appData['annual_sale_amt'] > 0) ? ((($appData['annual_sale_amt'] - $revenue)/$appData['annual_sale_amt'])*100) : 0;
        $available_debt = $ebitda/$threshold_dscr;
        $unsecured_max = config('b2c_common.UNSECURED_MAX');
        $total_debt = $personal_debt + $tot_existing_debt;
        $room_for_debt = ($available_debt - $total_debt);
        $room_for_debt = isset($room_for_debt) && $room_for_debt >= 0 ? $room_for_debt : 0;
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
        //total existing unsecured amount
        $totalExistingUnsecuredAmt = $this->getTotalExistingUnsecuredAmount(['app_id' => $appId, 'app_user_id' => $appUserId]);
        //total existing secured amount
        $totalExistingSecuredAmt = $this->getTotalExistingScuredAmount(['app_id' => $appId, 'app_user_id' => $appUserId]);
        //exposure calc
        $per_secured_exposer = $secured_exposer;
        $per_unsecured_exposer = $unsecured_exposer;
        $secured_exposer = $secured_exposer + $appData['secured_credit'] + $totalExistingSecuredAmt;
        $unsecured_exposer = $unsecured_exposer + $appData['unsecured_credit'] + $totalExistingUnsecuredAmt;
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
        //rule engine api call for final decision
        $esc_business_data = isset($appData['esc_res_data_id']) ? 1 : 2;
        
        $loggedInData = \Auth::user();
        $curent_date = Helpers::getCurrentDateTime();
        //bank statement table rule engine calling
        
        $reasonAttributes['app_id'] = $appId;
        $reasonAttributes['isBackend'] = $isBackend;
        $reasonAttributes['app_user_id'] = $appUserId;
        $reasonAttributes['curent_date'] = $curent_date;
        $reasonAttributes['loggedInData'] = $loggedInData;
        
        $bankStmtDataArr = [
            'bank_statement_rule' => true,
            'no_of_bank_statements' => ($getAllscannedPdf > 0 && $is_manual_stmt!=1) ? 2 :(isset($temp_bank_stmt)  ? $temp_bank_stmt : 2),
            'loan_credits' => isset($sum_loan_credit) ? $sum_loan_credit : $default_value,
            'dscr' => isset($existing_dscr) ? $existing_dscr : $default_value, 
            'revenue_variance' => ($revenue_variance > 0) ? $revenue_variance : 0,
            'nsf_three_months' => isset($temp_nsf_last_3_month) ? $temp_nsf_last_3_month : $default_value,
            'nsf_twelve_months' => isset($temp_nsf_last_12_month) ? $temp_nsf_last_12_month : $default_value,
            'other_income' => isset($temp_other_income) ? $temp_other_income : $default_value,
            'missing_transaction' => isset($debtResult[0]['is_missing_trans']) ? $debtResult[0]['is_missing_trans'] : $default_value,
            'operating_cost' => isset($operating_cost) ? $operating_cost : $default_operating_val
        ];
        $high_risk_industry = \Helpers::getIndustryName($appData['industry_id'])->is_high_credit_industry;
        if($attributes['is_bank_stmt_rule_req'] == true) {
            $bankResult = $this->assistedRuleEngineDataPrepare($bankStmtDataArr);
            $bankDecision = $this->saveBankStatementReasons($bankResult, $reasonAttributes);
            if(isset($bankDecision['status'])) {
                $bank_decision_res = Helpers::decisionByRuleEngine($bankDecision['status']);
            }
        }
        $industry_cls = \Helpers::getIndustryName($appData['industry_id'])->class;
        $dataArr = [
            'final_decision' => true,
            'months_in_biz' => $monthsInBiz,
            'requested_amount' => (int) $appData['loan_amount'],
            'unsecured_amount' => isset($appData['sys_calc_unsecured_amt']) ? $appData['sys_calc_unsecured_amt'] : $default_value,
            'segmentation' => isset($attributes['risk_factor']) ? (int) $attributes['risk_factor'] : $default_value,
            'biz_structure' => $appData['legal_entity_id'],
            'legal_age_owners_perc' => $appData['legal_age_owners_perc'],
            'equifax_br_factor' => isset($attributes['equifax_br']) ? (int) $attributes['equifax_br'] : $default_value,
            'equifax_pr_factor' => isset($attributes['equifax_pr']) ? (int) $attributes['equifax_pr'] : $default_value,
            'widely_held' => $attributes['owner_count'],
            'business_esc' => $esc_business_data,
            'primary_owner_esc' => isset($attributes['is_owner_esc_verified']) ? $attributes['is_owner_esc_verified'] : 2,
            'sys_cal_amt' => isset($limit) ? $limit : $default_value,
            'hit_strength' => $hitStrength,
            'consumer_alert' => $attributes['consumer_alert'],
            'annual_revenue' => isset($appData['annual_sale_amt']) ? $appData['annual_sale_amt'] : $default_value,
            'requested_purpose' => isset($attributes['loan_purpose_ids']) ? $attributes['loan_purpose_ids'] : $default_value,
            'requested_product' => isset($attributes['loan_prod_ids']) ? $attributes['loan_prod_ids'] : $default_value,
            'business_bureau_skipped' => ($hitStrength == '00' && $appData['is_biz_bureau_skip'] == config('b2c_common.MANUAL_SKIP')) ? 2 : $default_value,
            'personal_bureau_skipped' => (isset($attributes['owner_no_hit']) && $attributes['owner_no_hit'] == true && $attributes['is_credit_manual_skip'] == config('b2c_common.MANUAL_SKIP')) ? 2 : $default_value,
            'negative_industry' => isset($appData['is_industry_knocked_out']) ? $appData['is_industry_knocked_out'] : 2,
            'bank_statement_decision' => isset($bankDecision['status']) ? $bankDecision['status'] : 'Approve',
            'sic_professional' => !empty($industry_cls) ? $industry_cls : $default_value,
            'iovation' => isset($appData['fraud_score']) ? $appData['fraud_score'] : 0,
            'bank_statement' => ($getAllscannedPdf > 0) ? 2 : (isset($temp_bank_stmt)  ? $temp_bank_stmt : 2),
            'is_security_added' => isset($appData['is_security_added']) ? $appData['is_security_added'] : $default_value,
            'dscr' => isset($existing_dscr) ? $existing_dscr : $default_value,
            'high_credit_industry' => isset($high_risk_industry) ? $high_risk_industry : -1,
            'fico_score' => ($attributes['skip_bureau_reasons'] == true) ? -2 : (isset($appData['fico_score']) ? $appData['fico_score'] : -2),
            'bni_score' => ($attributes['skip_bureau_reasons'] == true) ? -2 : (isset($appData['bni_score']) ? $appData['bni_score'] : -2),
        ];

        $result = $this->assistedRuleEngineDataPrepare($dataArr);
        if(isset($result['status'])) {
            $decision = Helpers::decisionByRuleEngine($result['status']);
        }

        $arrAppData['decision'] = $decision;
        $returnData = [];
        $returnArr = $this->prepareDecisionReasons($result, $reasonAttributes, $arrAppData, $bankDecision);

        $standard_limit = $limit;
        $is_refer = $returnArr['is_refer'];
        $arrAppData = $returnArr['arrAppData'];
        $programme_30k = $returnArr['programme_30k'];
        $program_type = config('b2c_common.STANDARD_PROGRAM');
        $reasonsData = $resultData = $returnArr['reasonsData'];
        $limit_program_type = config('b2c_common.STANDARD_PROGRAM');
        $new_system_risk_factor = $attributes['over_all_risk_factor'];
        $reasonsDataLog = $resultDataNew = $returnArr['reasonsDataLog'];
        $new_segmentation = isset($attributes['risk_factor']) ? (int) $attributes['risk_factor'] : $default_value;
        
        if($programme_30k == false && $is_refer == true) {
            $arrAppData['decision'] = config('b2c_common.REFER');
        }
        $stated_revenue_20_percent = $appData['annual_sale_amt'] * 20/100;
        $new_30k_approved_amt = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.30k_PROGRAM'));
        $dataArr30K = $this->prepare30kRuleData($dataArr, $bankStmtDataArr, $high_risk_industry);

        if($standard_limit <= config('b2c_common.60_K') && $appData['is_security_added'] == null) {
            $decision_program_type = config('b2c_common.STANDARD_PROGRAM_UNDER_60K');
        } else if($standard_limit > config('b2c_common.60_K') && $standard_limit <= config('b2c_common.120_K') && $appData['is_security_added'] == null) {
            $decision_program_type = config('b2c_common.STANDARD_PROGRAM_60K_120K');
        } else if($standard_limit > config('b2c_common.120_K') && $standard_limit <= config('b2c_common.250_K') && $appData['is_security_added'] == null) {
            $decision_program_type = config('b2c_common.250K_UNSECURED_PROGRAM');
        } else if(($standard_limit > config('b2c_common.250_K')) || ($appData['is_security_added'] == 1)) {
            $decision_program_type = config('b2c_common.SECURED_PROGRAM');
        }

        if($decision == config('b2c_common.APPROVE')) {
            $newLimitArr = [$new_30k_approved_amt, $standard_limit];
            $max_program_type_limit = array_keys($newLimitArr, max($newLimitArr));
            if(isset($max_program_type_limit[0]) && $max_program_type_limit[0] == config('b2c_common.30K_PROGRAM_MAX_KEY')) {
                $limit = $new_30k_approved_amt;
                $program_type = config('b2c_common.30k_PROGRAM');
                $limit_program_type = config('b2c_common.30k_PROGRAM');
                $decision_program_type = config('b2c_common.30K_NON_START_UP_PROGRAM');
                if(count($ownerScore)) {
                    $returnData = $this->calcProgramSegmentationRule($ownerScore, $appId, '30k_segmentation');
                    $new_segmentation = $returnData['risk_factor'];
                }
                $dataArr30K['30k_risk_factor'] = $new_segmentation;
                $new_system_risk_factor = $this->programOverALlRiskFactorRule($dataArr30K);
                $dataArr30K['limit_lt_lowlimit'] = $limit;
                $dataArr30K['overall_risk_factor'] = $new_system_risk_factor;
                $dataArr30K['30k_bni_score'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : -2;
                $dataArr30K['30k_fico_score'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : -2;
                $prepare30kData = Helpers::prepare30kProgrammeData($dataArr30K);
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
                    $newLimitArrData = [$standard_limit, $limit_30k];
                    $new_max_program_type_limit = array_keys($newLimitArrData, max($newLimitArrData));
                    $limit = max($newLimitArrData);
                    if(isset($new_max_program_type_limit[0]) && $new_max_program_type_limit[0] == config('b2c_common.STANDARD_PROGRAM_MAX')) {
                        $limit_program_type = config('b2c_common.STANDARD_PROGRAM');
                    }
                }
            }
        }

        if($programme_30k == true && $decision != config('b2c_common.APPROVE')) {
            $program_type = config('b2c_common.30k_PROGRAM');
            $limit_program_type = config('b2c_common.30k_PROGRAM');
            $decision_program_type = config('b2c_common.30K_NON_START_UP_PROGRAM');
            if(count($ownerScore)) {
                $returnData = $this->calcProgramSegmentationRule($ownerScore, $appId, '30k_segmentation');
                $new_segmentation = $returnData['risk_factor'];
            }
            $dataArr30K['30k_risk_factor'] = $new_segmentation;
            $new_system_risk_factor = $this->programOverALlRiskFactorRule($dataArr30K);
            $limit = $new_30k_approved_amt;
            $dataArr30K['limit_lt_lowlimit'] = $limit;
            $dataArr30K['overall_risk_factor'] = $new_system_risk_factor;
            $dataArr30K['30k_bni_score'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : -2;
            $dataArr30K['30k_fico_score'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : -2;
            $prepare30kData = Helpers::prepare30kProgrammeData($dataArr30K);
            $programme30kResult = $this->ruleEngineDataPrepare($prepare30kData);
            $arrAppData['decision'] = Helpers::decisionByRuleEngine($programme30kResult['status']);
            
            //5k program rule trigger
            if($programme30kResult['status'] != 'Approved') {
                $returnData = [];
                $program_type = config('b2c_common.5k_PROGRAM');
                $limit_program_type = config('b2c_common.5k_PROGRAM');
                $decision_program_type = config('b2c_common.UNDER_30K_START_UP_PROGRAM');
                if($monthsInBiz >= 24) {
                    $decision_program_type = config('b2c_common.5K_MASTER_CARD_PROGRAM');
                }
                $program30kReasons = $this->prepareAndSaveProgramReasons($programme30kResult, $appUserId, $appId, $curent_date, $loggedInData->id, $isBackend);
                if(count($ownerScore)) {
                    $returnData = $this->calcProgramSegmentationRule($ownerScore, $appId, '5k_segmentation');
                    $new_segmentation = $returnData['risk_factor'];
                }
                $dataArr30K['segmentation'] = $new_segmentation;
                $new_system_risk_factor = $this->program5kOverALlRiskFactorRule($dataArr30K);
                //new approval limit calc
                $new_approved_amt = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.5k_PROGRAM'));
                $limit = $new_approved_amt;
                $prepare30kData['5k_fico_score'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : -2;
                $prepare30kData['5k_bni_score'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : -2;
                $prepare30kData['5k_risk_factor'] = $new_segmentation;
                $prepare30kData['limit_lt_lowlimit'] = $limit;
                $prepare30kData['overall_risk_factor'] = $new_system_risk_factor;
                $prepare5kData = Helpers::prepare5kProgrammeData($prepare30kData);
                $programme5kResult = $this->ruleEngineDataPrepare($prepare5kData);
                $arrAppData['decision'] = Helpers::decisionByRuleEngine($programme5kResult['status']);
                
                //overdraft program rule call
                if($programme5kResult['status'] != 'Approved') {
                    $returnData = $overdraftData = [];
                    $decision_program_type = config('b2c_common.UNDER_5K_PROGRAM');
                    $limit_program_type = $program_type = config('b2c_common.OVERDRAFT_PROGRAM');
                    $program5kReasons = $this->prepareAndSaveProgramReasons($programme5kResult, $appUserId, $appId, $curent_date, $loggedInData->id, $isBackend);
                    if(count($ownerScore)) {
                        $returnData = $this->calcProgramSegmentationRule($ownerScore, $appId, 'overdraft_segmentation');
                        $new_segmentation = $returnData['risk_factor'];
                    }
                    $dataArr30K['segmentation'] = $new_segmentation;
                    $new_system_risk_factor = $this->program5kOverALlRiskFactorRule($dataArr30K);
                    $overdraftData['overdraft_program'] = true;
                    $overdraftData['bni'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : -2;
                    $overdraftData['beacon'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : -2;
                    $overdraftProgram = $this->assistedRuleEngineDataPrepare($overdraftData);
                    $programOverdraftReasons = $this->prepareAndSaveProgramReasons($overdraftProgram, $appUserId, $appId, $curent_date, $loggedInData->id, $isBackend);
                    $arrAppData['decision'] = Helpers::decisionByRuleEngine($overdraftProgram['status']);
                    $overdraft_limit = isset($overdraftProgram['all_matching_decision'][0]->amt) ? $overdraftProgram['all_matching_decision'][0]->amt : 0;
                    $limit = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $secured_exposer, $unsecured_exposer, config('b2c_common.OVERDRAFT_PROGRAM'), $overdraft_limit);
                    if($limit > $appData['loan_amount']) {
                        $limit = Helpers::roudingToNearestAmt($appData['loan_amount'], $roundown = true);
                    }
                    if($arrAppData['decision'] == config('b2c_common.APPROVED') && $limit == 0) {
                        $arrAppData['decision'] = config('b2c_common.DECLINE');
                    }
                }
            }
        }

        $finalReasonArr = [];
        if(count($resultData) > 0) {
            $finalReasonArr = array_merge($resultData, $program30kReasons['programArr'], $program5kReasons['programArr'], $programOverdraftReasons['programArr']);
            $finalReasonLogArr = array_merge($resultDataNew, $program30kReasons['programLogArr'], $program5kReasons['programLogArr'], $programOverdraftReasons['programLogArr']);
        }

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

        //save all decision reasons
        if(count($reasonsData) > 0) {
            $this->application->saveDecisionReasonCode($reasonsData);
            $this->application->saveDecisionReasonCodeLog($reasonsDataLog);
        }
        
        //amount product bifurcation
        $max_unsecured_offer = config('b2c_common.MAX_UNSECURED_LIMIT') - $unsecured_exposer;
        $max_secured_offer = config('b2c_common.MAX_SECURED_LIMIT') - $secured_exposer;
        
        if($attributes['owner_no_hit'] == true) {
            $standard_limit = $limit = 0;
            $limit_program_type = config('b2c_common.STANDARD_PROGRAM');
        } else {
            $this->amountProductBifercation($appPurposeData, $appData, $max_unsecured_offer, $max_secured_offer, $limit, $program_type, $new_system_risk_factor, $max_internal_exposer);
        }
        
        $arrAppData['cust_limit'] = isset($limit) ? $limit : null;
        $arrAppData['program_type'] = isset($decision_program_type) ? $decision_program_type : null;
        $total_exposer = ($appData['total'] - ($appData['secured_exposer'] + $appData['unsecured_exposer']));
        if($per_secured_exposer > 0) {
            if($appData['per_secured_credit'] > 0 && $appData['app_status'] != config('b2c_common.APP_APPLICATION_COMPLETED')) {
                $total_exposer = $total_exposer - $appData['per_secured_credit'];
            }
            $total_exposer = $total_exposer + $per_secured_exposer;
        }        
        if($per_unsecured_exposer > 0) {
            if($appData['per_unsecured_credit'] > 0 && $appData['app_status'] != config('b2c_common.APP_APPLICATION_COMPLETED')) {
                $total_exposer = $total_exposer - $appData['per_unsecured_credit'];
            }
            $total_exposer = $total_exposer + $per_unsecured_exposer;
        }
        
        //check duplicate owner within other customers
        $select = ['app_owner.app_owner_id', 'app_owner.first_name', 'app_owner.last_name', 'app_owner.dob'];
        $ownerData = $this->application->getAllConditionalOwnerData(['app_user_id'=>$appUserId, 'app_id'=>$appId], $select)->toArray();
        $dupOwner = false;
        if(count($ownerData) > 0) {
            foreach($ownerData as $owner) {
                $selectData = ['app_owner_id'];
                $whereData = ['app_status' => config('b2c_common.APP_APPLICATION_COMPLETED'), 'not_app_user_id' => $appUserId, 'first_name' => $owner['first_name'], 'last_name' => $owner['last_name'], 'dob' => $owner['dob']];
                $dupOwnerData = $this->application->getAllConditionalOwnerData($whereData, $selectData)->toArray();
                if(count($dupOwnerData) > 0) {
                    $dupOwner = true;
                    $this->application->saveOwnerInfo(['is_dup_owner' => 1], $owner['app_owner_id']);
                } else {
                    $this->application->saveOwnerInfo(['is_dup_owner' => null], $owner['app_owner_id']);
                }
            }
        }
        //save duplicate owner reason code
        if($dupOwner == true) {
            $arrAppData['is_hard_automation_stop'] = config('b2c_common.YES');
            $this->prepareSaveDecisionReasonData(['app_user_id' => $appUserId, 'app_id' => $appId, 'reasons' => config('b2c_common.STATIC_DECISION_REASON_CODE.DUPLICATE_OWNER')]);
        }
        $arrAppData['cust_limit'] = isset($limit) ? $limit : null;
        $arrAppData['total'] = isset($total_exposer) ? $total_exposer : null;
        $arrAppData['decision_code'] = isset($result->code) ? $result->code : null;
        $arrAppData['decision_desc'] = isset($result->text) ? $result->text : null;
        $arrAppData['risk_factor'] = isset($new_segmentation) ? $new_segmentation : null;
        $arrAppData['secured_exposer'] = isset($per_secured_exposer) ? $per_secured_exposer : null;
        $arrAppData['unsecured_exposer'] = isset($per_unsecured_exposer) ? $per_unsecured_exposer : null;
        $arrAppData['limit_program_type'] = isset($limit_program_type) ? $limit_program_type : null;
        $arrAppData['max_internal_exposer'] = isset($max_internal_exposer) ? $max_internal_exposer : null;
        $arrAppData['max_secured_exposure'] = isset($max_secured_exposure) ? $max_secured_exposure : null;
        $arrAppData['final_risk_factor'] = isset($new_system_risk_factor) ? $new_system_risk_factor : null;
        $arrAppData['bank_stmt_decision_res'] = isset($bank_decision_res) ? $bank_decision_res : null;
        $arrAppData['max_unsecured_exposure'] = isset($max_unsecured_exposure) ? $max_unsecured_exposure : null;
        $this->application->updateApplication((int) $appId, $arrAppData);
        
        $where = ['app_user_id'=>$appUserId, 'app_id'=>$appId];
        $limitArr['risk_factor'] = $new_segmentation;
        $limitArr['max_secured_offer'] = $max_secured_offer;
        $limitArr['max_unsecured_offer'] = $max_unsecured_offer;
        $limitArr['cust_limit'] = $limit;
        $this->application->saveCreditLimit($where, $limitArr);
    }
    
    /**
     * function is used to set owners FICO/BNI/Age Of Trade Scores
     * @param type $owners
     * @param type $ownerScore
     * @return type
     */
    protected function setGlobalOwnerScores($owners, $ownerScore=[])
    {
        //owner fico
        $ownerScore[$owners['app_owner_id']]['fico'] = isset($owners['owner_credit_bureau_info']['fico_score']) ? (int) $owners['owner_credit_bureau_info']['fico_score'] : null;
        //owner bni score
        $ownerScore[$owners['app_owner_id']]['bni'] = isset($owners['owner_credit_bureau_info']['bni_score']) ? (int) $owners['owner_credit_bureau_info']['bni_score'] : null;
        //equifax trade details
        $ownerScore[$owners['app_owner_id']]['age_of_trade'] = !empty($owners['owner_credit_bureau_info']['age_of_oldest_trade']) ? (int) $owners['owner_credit_bureau_info']['age_of_oldest_trade'] : null;
        return $ownerScore;
    }
    
    /**
     * save bank statement reasons data
     * 
     * @param array $bankResult
     * @param array $bankAttributes
     */
    public function saveBankStatementReasons($bankResult, $bankAttributes)
    {
        $bankDecision['status'] = $bankResult['status'];
        $bankResultData = $bankResultDataNew = [];
        if(isset($bankResult['all_matching_decision'])) {
            $bankStmtResult = $bankResult['all_matching_decision'];
            if(count($bankStmtResult) > 0) {
                foreach($bankStmtResult as $result) {
                    $bankResultData[] = [
                        'app_user_id' => $bankAttributes['app_user_id'],
                        'app_id' => $bankAttributes['app_id'],
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'created_by' => isset($bankAttributes['loggedInData']->id) ? $bankAttributes['loggedInData']->id : null,
                        'updated_by' => isset($bankAttributes['loggedInData']->id) ? $bankAttributes['loggedInData']->id : null,
                        'created_at' => $bankAttributes['curent_date'],
                        'updated_at' => $bankAttributes['curent_date'],
                    ];
                    $bankResultDataNew[] = [
                        'app_user_id' => $bankAttributes['app_user_id'],
                        'app_id' => $bankAttributes['app_id'],
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'is_auto_manual' => isset($bankAttributes['isBackend']) ? 2 : 1,
                        'created_by' => isset($bankAttributes['loggedInData']->id) ? $bankAttributes['loggedInData']->id : null,
                        'updated_by' => isset($bankAttributes['loggedInData']->id) ? $bankAttributes['loggedInData']->id : null,
                        'created_at' => $bankAttributes['curent_date'],
                        'updated_at' => $bankAttributes['curent_date'],
                    ];
                }
            }
        }
        $bankDecision['bankResultData'] = $bankResultData;
        $bankDecision['bankResultDataLog'] = $bankResultDataNew;
        return $bankDecision;
    }
    
    /**
     * prepare decision reasons
     * 
     * @param array $result
     * @param array $decisionAttributes
     */
    public function prepareDecisionReasons($decisionReasons, $decisionAttributes, $arrAppData, $bankDecision)
    {
        $is_refer = true;
        $programme_30k = true;
        $reasonsData = $reasonsDataLog = [];
        $appData = $this->application->find($decisionAttributes['app_id']);
        $curent_date = $decisionAttributes['curent_date'];
        if(isset($decisionReasons['all_matching_decision'])) {
            $decisionResult = $decisionReasons['all_matching_decision'];
            if(count($decisionResult) > 0) {
                foreach($decisionResult as $key=>$result) {
                    $resultData[] = [
                        'app_user_id' => $decisionAttributes['app_user_id'],
                        'app_id' => $decisionAttributes['app_id'],
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'created_by' => isset($decisionAttributes['loggedInData']->id) ? $decisionAttributes['loggedInData']->id : null,
                        'updated_by' => isset($decisionAttributes['loggedInData']->id) ? $decisionAttributes['loggedInData']->id : null,
                        'created_at' => $curent_date,
                        'updated_at' => $curent_date,
                    ];
                    $resultDataNew[] = [
                        'app_user_id' => $decisionAttributes['app_user_id'],
                        'app_id' => $decisionAttributes['app_id'],
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'is_auto_manual' => isset($decisionAttributes['isBackend']) ? 2 : 1,
                        'created_by' => isset($decisionAttributes['loggedInData']->id) ? $decisionAttributes['loggedInData']->id : null,
                        'updated_by' => isset($decisionAttributes['loggedInData']->id) ? $decisionAttributes['loggedInData']->id : null,
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

                //merge decision and bank stmt reasons
                if(isset($bankDecision['bankResultData']) && count($bankDecision['bankResultData']) > 0) {
                    $resultData = array_merge($resultData, $bankDecision['bankResultData']);
                    $resultDataNew = array_merge($resultDataNew, $bankDecision['bankResultDataLog']);
                }

                $initial_decision = null;
                if(count($resultDataNew) > 0) {
                    if($arrAppData['decision'] == config('b2c_common.APPROVE')) {
                        $initial_decision = 'Approve';
                    } else if($arrAppData['decision'] == config('b2c_common.REFER')) {
                        $initial_decision = 'Refer';
                    } else {
                        $initial_decision = 'Decline';
                    }
                    array_walk($resultDataNew, function(&$newArray) use ($initial_decision) {
                        $newArray['final_decision'] = $initial_decision;
                    });
                }
                $reasonsData = $resultData;		
                $reasonsDataLog = $resultDataNew;
            } else {
                $resultDataNew = $resultData = [];
                $resultDataNew[0]['app_user_id'] = $resultData[0]['app_user_id'] =  $decisionAttributes['app_user_id'];
                $resultDataNew[0]['app_id'] = $resultData[0]['app_id'] =  $decisionAttributes['app_id'];
                $resultDataNew[0]['decision'] = $resultData[0]['decision'] =  isset($result['status']) ? $result['status'] : null;
                $resultDataNew[0]['code'] = $resultData[0]['code'] =  isset($result['code']) ? $result['code'] : null;
                $resultDataNew[0]['reason'] = $resultData[0]['reason'] =  isset($result['decision_desc']) ? $result['decision_desc'] : null;
                $resultDataNew[0]['created_by'] = $resultData[0]['created_by'] =  isset($decisionAttributes['loggedInData']->id) ? $decisionAttributes['loggedInData']->id : null;
                $resultDataNew[0]['updated_by'] = $resultData[0]['updated_by'] =  isset($decisionAttributes['loggedInData']->id) ? $decisionAttributes['loggedInData']->id : null;
                $resultDataNew[0]['created_at'] = $resultData[0]['created_at'] =  $curent_date;
                $resultDataNew[0]['updated_at'] = $resultData[0]['updated_at'] =  $curent_date;
                $resultDataNew[0]['is_auto_manual'] = isset($decisionAttributes['isBackend']) ? 2 : 1;
                //merge decision and bank stmt reasons
                if(isset($bankDecision['bankResultData']) && count($bankDecision['bankResultData']) > 0) {
                    $resultData = array_merge($resultData, $bankDecision['bankResultData']);
                    $resultDataNew = array_merge($resultDataNew, $bankDecision['bankResultDataLog']);
                }
                $reasonsData = $resultData;		
                $reasonsDataLog = $resultDataNew;
            }
        }
        return ['arrAppData' => $arrAppData, 'programme_30k' => $programme_30k, 'is_refer' => $is_refer, 'reasonsData' => $reasonsData, 'reasonsDataLog' => $reasonsDataLog];
    }
    
    /**
     * amount product bifurcation on proirity basis
     * 
     * @param array $appPurposeData
     * @param array $appData
     * @param float $max_unsecured_offer
     * @param float $max_secured_offer
     * @param int $program_type
     * @param int $new_system_risk_factor
     */
    public function amountProductBifercation($appPurposeData, $appData, $max_unsecured_offer, $max_secured_offer, $limit, $program_type, $new_system_risk_factor, $max_internal_exposer)
    {
        $returnDara = [];
        $app_id = $appData['app_id'];
        $is_next_prod_amt_calc = true;
        $created_by = \Auth::user()->id;
        $app_user_id = $appData['app_user_id'];
        $loan_on_cash = $appData['loan_on_cash'];
        $created_at = Helpers::getCurrentDateTime();
        $loan_on_realestate = $appData['loan_on_realestate'];
        $security_assesed_val = $appData['security_assessed_val'];
        $term_loan_max = $term_loan_prod_amt = $loc_max = $loc_prod_amt = $cc_max = $cc_prod_amt = $overdraft_max = $overdraft_prod_amt = 0;
        
        $interestData = $this->application->getAllInterestRate(['is_active' => config('b2c_common.ACTIVE')])->toArray();
        if(count($interestData) > 0) {
            $interestData = array_reduce($interestData, function ($output, $element) {
                $output[$element['product_id']][$element['loan_security_id']][$element['interest_type']][$element['risk_rating']][] = $element;
                return $output;
            });
        }
        
        $appPurposeData = array_reduce($appPurposeData, function ($output, $element) {
            $output[$element['product']][] = $element;
            return $output;
        });

        $termLoanData = isset($appPurposeData[config('b2c_common.TERM_LOAN_PROD')]) ? $appPurposeData[config('b2c_common.TERM_LOAN_PROD')] : [];
        $overDraftData = isset($appPurposeData[config('b2c_common.OVERDRAFT_PROD')]) ? $appPurposeData[config('b2c_common.OVERDRAFT_PROD')] : [];
        $creditCardData = isset($appPurposeData[config('b2c_common.CREDIT_CARD_PROD')]) ? $appPurposeData[config('b2c_common.CREDIT_CARD_PROD')] : [];
        $locLoanData = isset($appPurposeData[config('b2c_common.LINE_OF_CREDIT_PROD')]) ? $appPurposeData[config('b2c_common.LINE_OF_CREDIT_PROD')] : [];

        if($program_type == config('b2c_common.STANDARD_PROGRAM') || $program_type == config('b2c_common.30k_PROGRAM')) {
            //for term loan product amount bifurcation
            if(count($termLoanData) > 0) {
                $total_term_loan_amt = array_sum(array_column($termLoanData, 'amount'));
                //only unsecured term loan security
                if(count(array_intersect(array_column($termLoanData, 'loan_security'), config('b2c_common.SECURED_SECURITY'))) == 0) {
                    $term_loan_security_type = config('b2c_common.UNSECURED_LOAN_SEC');
                    $term_loan_max = min($total_term_loan_amt, $termLoanData[0]['max_amount'], $max_unsecured_offer, $max_internal_exposer);
                } else {
                    // term loan with cash and realestate or both
                    if((count(array_intersect(config('b2c_common.SECURED_WITH_CASH_REALESTATE_GRP'), array_column($termLoanData, 'loan_security'))) >= 2) || (in_array(config('b2c_common.SECURED_WITH_CASH_REALESTATE'), array_column($termLoanData, 'loan_security')))) {
                        $security_max = $security_assesed_val;
                        $term_loan_security_type = config('b2c_common.SECURED_WITH_CASH_REALESTATE');
                    } else if(count(array_intersect([config('b2c_common.SECURED_WITH_CASH_SEC')], array_column($termLoanData, 'loan_security'))) == 1) {
                        $security_max = $loan_on_cash;
                        $term_loan_security_type = config('b2c_common.SECURED_WITH_CASH_SEC');
                    } else if(count(array_intersect([config('b2c_common.SECURED_WITH_REALESTATE_SEC')], array_column($termLoanData, 'loan_security'))) == 1) {
                        $security_max = $loan_on_realestate;
                        $term_loan_security_type = config('b2c_common.SECURED_WITH_REALESTATE_SEC');
                    }
                    $term_loan_max = min($total_term_loan_amt, $termLoanData[0]['max_amount'], $security_max, $max_secured_offer, $security_assesed_val, $max_internal_exposer);
                }

                if($term_loan_max >= $limit) {
                    $is_next_prod_amt_calc = false;
                    $term_loan_prod_amt = $limit;
                } else if($term_loan_max < $limit) {
                    $term_loan_prod_amt = $term_loan_max;
                    $limit = $limit - $term_loan_prod_amt;
                    $max_internal_exposer = $max_internal_exposer - $term_loan_prod_amt;

                    //only unsecured term loan security (revise parameters)
                    if(count(array_intersect(array_column($termLoanData, 'loan_security'), config('b2c_common.SECURED_SECURITY'))) == 0) {
                        $max_unsecured_offer = $max_unsecured_offer - $term_loan_prod_amt;
                    } else {
                        //revise params for secured loan
                        $max_secured_offer = $max_secured_offer - $term_loan_prod_amt;
                        if((count(array_intersect(config('b2c_common.SECURED_WITH_CASH_REALESTATE_GRP'), array_column($termLoanData, 'loan_security'))) >= 2) || (in_array(config('b2c_common.SECURED_WITH_CASH_REALESTATE'), array_column($termLoanData, 'loan_security')))) {
                            $security_assesed_val = $security_assesed_val - $term_loan_prod_amt;
                        } else if(count(array_intersect([config('b2c_common.SECURED_WITH_CASH_SEC')], array_column($termLoanData, 'loan_security'))) == 1) {
                            $loan_on_cash = $loan_on_cash - $term_loan_prod_amt;
                            $security_assesed_val = $loan_on_cash + $loan_on_realestate;
                        } else if(count(array_intersect([config('b2c_common.SECURED_WITH_REALESTATE_SEC')], array_column($termLoanData, 'loan_security'))) == 1) {
                            $loan_on_realestate = $loan_on_realestate - $term_loan_prod_amt;
                            $security_assesed_val = $loan_on_cash + $loan_on_realestate;
                        }
                    }
                }

                if($term_loan_prod_amt > $termLoanData[0]['max_amount']) {
                    $term_loan_prod_amt = $termLoanData[0]['max_amount'];
                } elseif($term_loan_prod_amt < $termLoanData[0]['min_amount']) {
                    $term_loan_prod_amt = 0;
                }
                $term_loan_prod_amt = Helpers::roudingToNearestAmt($term_loan_prod_amt);
                $interest_rate_type = (in_array(config('b2c_common.FIXED_INTEREST_RATE'), array_column($termLoanData, 'interset_rate')) && in_array(config('b2c_common.VARIABLE_INTEREST_RATE'), array_column($termLoanData, 'interset_rate'))) ? config('b2c_common.VARIABLE_INTEREST_RATE') : $termLoanData[0]['interset_rate'];
                $interest_rate = isset($interestData[config('b2c_common.TERM_LOAN_PROD')][$term_loan_security_type][$interest_rate_type][$new_system_risk_factor][0]['id']) ? $interestData[config('b2c_common.TERM_LOAN_PROD')][$term_loan_security_type][$termLoanData[0]['interset_rate']][$new_system_risk_factor][0]['id'] : null;
                if($term_loan_prod_amt > 0) {
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['app_id'] = $app_id;
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['app_user_id'] = $app_user_id;
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['interest_rate_id'] = $interest_rate;
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['total_amount'] = $term_loan_prod_amt;
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['loan_security_type'] = $term_loan_security_type;
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['product_id'] = config('b2c_common.TERM_LOAN_PROD');
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['created_at'] = $returnDara[config('b2c_common.TERM_LOAN_PROD')]['updated_at'] = $created_at;
                    $returnDara[config('b2c_common.TERM_LOAN_PROD')]['created_by'] = $returnDara[config('b2c_common.TERM_LOAN_PROD')]['updated_by'] = $created_by;
                }
            }

            //for line of credit product amount bifurcation
            if($is_next_prod_amt_calc == true) {
                if(count($locLoanData) > 0) {
                    $total_loc_amt = array_sum(array_column($locLoanData, 'amount'));
                    //only unsecured loc security
                    if(count(array_intersect(array_column($locLoanData, 'loan_security'), config('b2c_common.SECURED_SECURITY'))) == 0) {
                        $loc_max = min($total_loc_amt, $locLoanData[0]['max_amount'], $max_unsecured_offer, $max_internal_exposer);
                        $loc_loan_security_type = config('b2c_common.UNSECURED_LOAN_SEC');
                    } else {
                        // loc with cash and realestate or both
                        if((count(array_intersect(config('b2c_common.SECURED_WITH_CASH_REALESTATE_GRP'), array_column($locLoanData, 'loan_security'))) >= 2) || (in_array(config('b2c_common.SECURED_WITH_CASH_REALESTATE'), array_column($locLoanData, 'loan_security')))) {
                            $security_max_loc = $security_assesed_val;
                            $loc_loan_security_type = config('b2c_common.SECURED_WITH_CASH_REALESTATE');
                        } else if(count(array_intersect([config('b2c_common.SECURED_WITH_CASH_SEC')], array_column($locLoanData, 'loan_security'))) == 1) {
                            $security_max_loc = $loan_on_cash;
                            $loc_loan_security_type = config('b2c_common.SECURED_WITH_CASH_SEC');
                        } else if(count(array_intersect([config('b2c_common.SECURED_WITH_REALESTATE_SEC')], array_column($locLoanData, 'loan_security'))) == 1) {
                            $security_max_loc = $loan_on_realestate;
                            $loc_loan_security_type = config('b2c_common.SECURED_WITH_REALESTATE_SEC');
                        }
                        $loc_max = min($total_loc_amt, $locLoanData[0]['max_amount'], $security_max_loc, $max_secured_offer, $security_assesed_val, $max_internal_exposer);
                    }

                    //loc max is grerater than limit break the journey
                    if($loc_max >= $limit) {
                        $is_next_prod_amt_calc = false;
                        $loc_prod_amt = $limit;
                    } else if($loc_max < $limit){
                        $loc_prod_amt = $loc_max;
                        $limit = $limit - $loc_prod_amt;
                        $max_internal_exposer = $max_internal_exposer - $loc_prod_amt;
                        //only unsecured loc security (revise parameters)
                        if(count(array_intersect(array_column($locLoanData, 'loan_security'), config('b2c_common.SECURED_SECURITY'))) == 0) {
                            $max_unsecured_offer = $max_unsecured_offer - $loc_prod_amt;
                        } else {
                            //revise params for secured loan
                            $max_secured_offer = $max_secured_offer - $loc_prod_amt;
                            if((count(array_intersect(config('b2c_common.SECURED_WITH_CASH_REALESTATE_GRP'), array_column($locLoanData, 'loan_security'))) >= 2) || (in_array(config('b2c_common.SECURED_WITH_CASH_REALESTATE'), array_column($locLoanData, 'loan_security')))) {
                                $security_assesed_val = $security_assesed_val - $loc_prod_amt;
                            } else if(count(array_intersect([config('b2c_common.SECURED_WITH_CASH_SEC')], array_column($locLoanData, 'loan_security'))) == 1) {
                                $loan_on_cash = $loan_on_cash - $loc_prod_amt;
                                $security_assesed_val = $loan_on_cash + $loan_on_realestate;
                            } else if(count(array_intersect([config('b2c_common.SECURED_WITH_REALESTATE_SEC')], array_column($locLoanData, 'loan_security'))) == 1) {
                                $loan_on_realestate = $loan_on_realestate - $loc_prod_amt;
                                $security_assesed_val = $loan_on_cash + $loan_on_realestate;
                            }
                        }
                    }

                    if($loc_prod_amt > $locLoanData[0]['max_amount']) {
                        $loc_prod_amt = $locLoanData[0]['max_amount'];
                    } elseif($loc_prod_amt < $locLoanData[0]['min_amount']) {
                        $loc_prod_amt = 0;
                    }
                    $loc_prod_amt = Helpers::roudingToNearestAmt($loc_prod_amt);
                    $interest_rate = isset($interestData[config('b2c_common.LINE_OF_CREDIT_PROD')][$loc_loan_security_type][$locLoanData[0]['interset_rate']][$new_system_risk_factor][0]['id']) ? $interestData[config('b2c_common.LINE_OF_CREDIT_PROD')][$loc_loan_security_type][$locLoanData[0]['interset_rate']][$new_system_risk_factor][0]['id'] : null;
                    if($loc_prod_amt > 0) {
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['app_id'] = $app_id;
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['app_user_id'] = $app_user_id;
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['total_amount'] = $loc_prod_amt;
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['interest_rate_id'] = $interest_rate;
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['loan_security_type'] = $loc_loan_security_type;
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['product_id'] = config('b2c_common.LINE_OF_CREDIT_PROD');
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['created_at'] = $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['updated_at'] = $created_at;
                        $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['created_by'] = $returnDara[config('b2c_common.LINE_OF_CREDIT_PROD')]['updated_by'] = $created_by;
                    }
                }
            }

            //for credit card product amount bifurcation
            if($is_next_prod_amt_calc == true) {
                if(count($creditCardData) > 0) {
                    $total_cc_amt = array_sum(array_column($creditCardData, 'amount'));
                    $cc_max = min($total_cc_amt, $creditCardData[0]['max_amount'], $max_unsecured_offer, $max_internal_exposer);

                    if($cc_max >= $limit) {
                        $is_next_prod_amt_calc = false;
                        $cc_prod_amt = $limit;
                    } else if($cc_max < $limit){
                        $cc_prod_amt = $cc_max;
                        $limit = $limit - $cc_prod_amt;
                        $max_internal_exposer = $max_internal_exposer - $cc_prod_amt;
                    }
                    if($cc_prod_amt > $creditCardData[0]['max_amount']) {
                        $cc_prod_amt = $creditCardData[0]['max_amount'];
                    } elseif($cc_prod_amt < $creditCardData[0]['min_amount']) {
                        $cc_prod_amt = 0;
                    }
                    $cc_prod_amt = Helpers::roudingToNearestAmt($cc_prod_amt);
                    $max_unsecured_offer = $max_unsecured_offer - $cc_prod_amt;
                    $interest_rate = isset($interestData[config('b2c_common.CREDIT_CARD_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][$creditCardData[0]['interset_rate']][$new_system_risk_factor][0]['id']) ? $interestData[config('b2c_common.CREDIT_CARD_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][$creditCardData[0]['interset_rate']][$new_system_risk_factor][0]['id'] : null;
                    if($cc_prod_amt > 0) {
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['app_id'] = $app_id;
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['app_user_id'] = $app_user_id;
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['total_amount'] = $cc_prod_amt;
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['interest_rate_id'] = $interest_rate;
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['product_id'] = config('b2c_common.CREDIT_CARD_PROD');
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['loan_security_type'] = config('b2c_common.UNSECURED_LOAN_SEC');
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['created_at'] = $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['updated_at'] = $created_at;
                        $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['created_by'] = $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['updated_by'] = $created_by;
                    }
                }
            }

            //for overdraft product amount bifurcation
            if($is_next_prod_amt_calc == true) {
                if(count($overDraftData) > 0) {
                    $total_overdraft_amt = array_sum(array_column($overDraftData, 'amount'));
                    $overdraft_max = min($total_overdraft_amt, $overDraftData[0]['max_amount'], $max_unsecured_offer, $max_internal_exposer);

                    if($overdraft_max >= $limit) {
                        $is_next_prod_amt_calc = false;
                        $overdraft_prod_amt = $limit;
                    } else if($overdraft_max < $limit){
                        $overdraft_prod_amt = $overdraft_max;
                    }
                    if($overdraft_prod_amt > $overDraftData[0]['max_amount']) {
                        $overdraft_prod_amt = $overDraftData[0]['max_amount'];
                    } elseif($overdraft_prod_amt < $overDraftData[0]['min_amount']) {
                        $overdraft_prod_amt = 0;
                    }
                    $overdraft_prod_amt = Helpers::roudingToNearestAmt($overdraft_prod_amt);
                    $interest_rate = isset($interestData[config('b2c_common.OVERDRAFT_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][$overDraftData[0]['interset_rate']][$new_system_risk_factor][0]['id']) ? $interestData[config('b2c_common.OVERDRAFT_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][$overDraftData[0]['interset_rate']][$new_system_risk_factor][0]['id'] : null;
                    if($overdraft_prod_amt > 0) {
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['app_id'] = $app_id;
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['app_user_id'] = $app_user_id;
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['interest_rate_id'] = $interest_rate;
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['total_amount'] = $overdraft_prod_amt;
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['product_id'] = config('b2c_common.OVERDRAFT_PROD');
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['loan_security_type'] = config('b2c_common.UNSECURED_LOAN_SEC');
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['created_at'] = $returnDara[config('b2c_common.OVERDRAFT_PROD')]['updated_at'] = $created_at;
                        $returnDara[config('b2c_common.OVERDRAFT_PROD')]['created_by'] = $returnDara[config('b2c_common.OVERDRAFT_PROD')]['updated_by'] = $created_by;
                    }                    
                }
            }
        } else if($program_type == config('b2c_common.5k_PROGRAM')) {
            $cc_amount = $limit;
            $ccProduct = $this->application->getProductsData(config('b2c_common.CREDIT_CARD_PROD'));
            if($cc_amount > $ccProduct['max_amount']) {
                $cc_amount = $ccProduct['max_amount'];
            } elseif($cc_amount < $ccProduct['min_amount']) {
                $cc_amount = 0;
            }
            $cc_amount = Helpers::roudingToNearestAmt($cc_amount);
            $interest_rate = isset($interestData[config('b2c_common.CREDIT_CARD_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][config('b2c_common.CREDIT_CARD_INTEREST_RATE')][$new_system_risk_factor][0]['id']) ? $interestData[config('b2c_common.CREDIT_CARD_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][config('b2c_common.CREDIT_CARD_INTEREST_RATE')][$new_system_risk_factor][0]['id'] : null;
            if($cc_amount > 0) {
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['app_id'] = $app_id;
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['total_amount'] = $cc_amount;
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['app_user_id'] = $app_user_id;
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['interest_rate_id'] = $interest_rate;
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['product_id'] = config('b2c_common.CREDIT_CARD_PROD');
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['loan_security_type'] = config('b2c_common.UNSECURED_LOAN_SEC');
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['created_at'] = $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['updated_at'] = $created_at;
                $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['created_by'] = $returnDara[config('b2c_common.CREDIT_CARD_PROD')]['updated_by'] = $created_by;
            }
        } else if($program_type == config('b2c_common.OVERDRAFT_PROGRAM')) {
            $overdraft_amount = $limit;
            $overdraftProduct = $this->application->getProductsData(config('b2c_common.OVERDRAFT_PROD'));
            if($overdraft_amount > $overdraftProduct['max_amount']) {
                $overdraft_amount = $overdraftProduct['max_amount'];
            } elseif($overdraft_amount < $overdraftProduct['min_amount']) {
                $overdraft_amount = 0;
            }
            $overdraft_amount = Helpers::roudingToNearestAmt($overdraft_amount);
            $interest_rate = isset($interestData[config('b2c_common.OVERDRAFT_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][config('b2c_common.VARIABLE_INTEREST_RATE')][$new_system_risk_factor][0]['id']) ? $interestData[config('b2c_common.OVERDRAFT_PROD')][config('b2c_common.UNSECURED_LOAN_SEC')][config('b2c_common.VARIABLE_INTEREST_RATE')][$new_system_risk_factor][0]['id'] : null;
            if($overdraft_amount > 0) {
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['app_id'] = $app_id;
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['total_amount'] = $overdraft_amount;
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['app_user_id'] = $app_user_id;
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['interest_rate_id'] = $interest_rate;
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['product_id'] = config('b2c_common.OVERDRAFT_PROD');
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['loan_security_type'] = config('b2c_common.UNSECURED_LOAN_SEC');
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['created_at'] = $returnDara[config('b2c_common.OVERDRAFT_PROD')]['updated_at'] = $created_at;
                $returnDara[config('b2c_common.OVERDRAFT_PROD')]['created_by'] = $returnDara[config('b2c_common.OVERDRAFT_PROD')]['updated_by'] = $created_by;
            }
        }
        //save final amount product bifurcation
        if(count($returnDara) > 0) {
            $this->application->saveAmountProductBifurcation($returnDara);
        }
    }
}
