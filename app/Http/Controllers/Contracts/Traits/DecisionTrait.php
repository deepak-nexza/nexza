<?php

namespace App\Http\Controllers\Contracts\Traits;

use Helpers;
use Carbon\Carbon;
use App\Http\Controllers\Contracts\Traits\AssistedDecisionTrait;
use App\Http\Controllers\Contracts\Traits\PrepareDataTrait;
use App\Http\Controllers\Contracts\Traits\V2\DecisionTrait as DecisionTraitV2;

trait DecisionTrait {
    
     use AssistedDecisionTrait, PrepareDataTrait, DecisionTraitV2 {
         DecisionTraitV2::decisionEngine as decisionEngineV2;
     }
    /**
     * decision engine and limit calculation
     * 
     * @param array $attributes
     */
    public function decisionEngine($attributes)
    {
        try{
        $app_id = $attributes['app_id'];
        $applicationDetails = $this->application->find($app_id, ['version', 'is_created_from', 'legal_entity_id']);
        if(!empty($applicationDetails) && $applicationDetails->is_created_from == 2) {
            $this->assistedDecisionEngine(['app_user_id' => $attributes['app_user_id'], 'app_id' => $app_id, 'is_backend' =>2]);
            return true;
        } else if(!empty($applicationDetails) && $applicationDetails->version == 2) {
            $this->assistedDecisionEngine(['app_user_id' => $attributes['app_user_id'], 'app_id' => $app_id, 'is_backend' =>2]);
            //$this->decisionEngineV2(['app_user_id' => $attributes['app_user_id'], 'app_id' => $app_id, 'is_backend' =>2]);
            return true;
        }
        $risk_factor = null;
        $riskFactorArr = [];
        $ownerScore = [];
        $equifaxPR = [];
        $keyOwners = [];
        $equifaxReasons = [];
        $equifaxPRReasons = [];
        $consumer_alert = 2;
        $personal_debt = 0;
        $is_owner_esc_verified = 2;
        $segmentationKeyOwners = [];
        $secured_exposer = $unsecured_exposer = 0;
        $app_user_id = $attributes['app_user_id'];
        $is_backend  = isset($attributes['is_backend']) ? $attributes['is_backend'] : null;
        $this->application->deleteDecisionReasonCode(['app_id' => $app_id, 'app_user_id' => $app_user_id]);
        $select = ['app_owner.app_user_id', 'app_owner.app_id', 'app_owner.app_owner_id', 'app_owner.own_percentage', 'app_owner.dob', 'app_owner.state_id', 'app_owner.is_guarantor', 'app_owner.res_data_id'];
        $relations = ['ownerEquifaxTrade', 'equifaxResponseData', 'equifaxCollectionData', 'equifaxBankruptcies', 'equifaxFraudWarnings', 'equifaxScoreData', 'equifaxInquiriesData', 'equifaxLegalDetails', 'equifaxStatements', 'equifaxSpecialServices', 'equifaxTradePaymentProfile', 'equifaxTradeNarrative'];
        $ownerData = $this->application->getAllConditionalOwnerData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id, 'order_by' => 'own_percentage'], $select, $relations)->toArray();
        $tot_owner = count($ownerData);
        if(count($ownerData) > 0) {
            //key owners for segmentation
            foreach($ownerData as $owner) {
                if($owner['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA_51_PERCENT')) {
                    $segmentationKeyOwners = [];
                    $segmentationKeyOwners[] = $owner;
                    break;
                } else if($owner['own_percentage'] >= config('b2c_common.MIN_ELIGIBILITY_OWNER_CRITERIA')) {
                    $segmentationKeyOwners[] = $owner;
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
                    $not_current = 2;
                    $thin_file = 0;
                    $serious_derog = 0;
                    $charge_off = 2;
                    $collection_count = 0;
                    $bankrupt = 2;
                    $inquiry_count = 0;
                    $legal_code = 2;
                    $fraud = 2;
                    $date_inquiry = null;
                    //equifax trade details
                    $equifaxTrade = isset($owners['owner_equifax_trade']) ? $owners['owner_equifax_trade'] : [];
                    $hit_strength = isset($owners['equifax_response_data'][0]['hit_code']) ? $owners['equifax_response_data'][0]['hit_code'] : '00';
                    $collectionArr = isset($owners['equifax_collection_data']) ? $owners['equifax_collection_data'] : [];
                    $liability_amt = isset($owners['equifax_bankruptcies'][0]['liability_amt']) ? $owners['equifax_bankruptcies'][0]['liability_amt'] : null;
                    $inquiriesData = isset($owners['equifax_inquiries_data']) ? $owners['equifax_inquiries_data'] : [];
                    $legalData = isset($owners['equifax_legal_details']) ? $owners['equifax_legal_details'] : [];
                    $statementData = isset($owners['equifax_statements']) ? $owners['equifax_statements'] : [];
                    $servicesData = isset($owners['equifax_special_services']) ? $owners['equifax_special_services'] : [];
                    $ownerTradePayment = isset($owners['equifax_trade_payment_profile']) ? $owners['equifax_trade_payment_profile'] : [];
                    $tradeNarrative = isset($owners['equifax_trade_narrative']) ? $owners['equifax_trade_narrative'] : [];
                    //age of trade calc
                    $totalMonthsData = [];
                    $active_trade = 0;
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

                        foreach($equifaxTrade as $trade) {
                            $active = 0;
                            if(isset($trade['open_date'])) {
                                $currentDate = new Carbon();
                                $open_date = new Carbon($trade['open_date']);
                                $diff_months = $open_date->diffInMonths($currentDate);
                                $totalMonthsData[] = $diff_months;
                            }
                            
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
                            
                            //secured unsecured exposer calc
                            if(strpos(strtoupper($trade['name']), 'HSBC') && isset($tradeNarrative[$trade['trade_id']]) && ($applicationDetails->legal_entity_id == 1 || $applicationDetails->legal_entity_id == 2)) {
                                $returnData = Helpers::sucuredUnsecuredExposerCalc($trade, $tradeNarrative[$trade['trade_id']], $secured_exposer, $unsecured_exposer);
                                $secured_exposer = $returnData['secured_exposer'];
                                $unsecured_exposer = $returnData['unsecured_exposer'];
                            }
                        }
                        //$personal_debt calc for limit
                        $personalDebt = Helpers::payementRateTermCalc($equifaxTrade, $tradeNarrative, $applicationDetails->legal_entity_id);
                        $personalDebtArr[] = $personalDebt * ($owners['own_percentage']/100);
                        $personal_debt = array_sum($personalDebtArr);
                    }

                    $age_of_trade = count($totalMonthsData) > 0 ? max($totalMonthsData) : 0;
                    
                    if(count($equifaxTrade) > 0 && count($tradeNarrative) == 0) {
                        $active_trade = 1;
                    }

                    if($age_of_trade < 12) {
                        $thin_file = 1;
                    } else if($age_of_trade >= 12 && $age_of_trade < 36 && $active_trade <= 1) {
                        $thin_file = 1;
                    } else if($age_of_trade >= 36 && $active_trade == 0) {
                        $thin_file = 1;
                    }

                    if(count($statementData) > 0 || count($servicesData) > 0) {
                        $consumer_alert = 1;
                    }
                    if(count($legalData) > 0) {
                        $legal_code = 1;
                    }
                    
                    if(count($inquiriesData) > 0) {
                        foreach($inquiriesData as $inquiry) {
                            $date_inquiry = $inquiry['date_of_local_enquiry'];
                            if(isset($date_inquiry)) {
                                $month_diff = Helpers::calculateMonthsDiff($date_inquiry);
                                if($month_diff <= 12) {
                                    $inquiry_count = $inquiry_count + 1;
                                }
                            }
                        }
                    }
                    
                    if(isset($liability_amt) && $liability_amt > 0) {
                        $bankrupt = 1;
                    }
                    if(count($equifaxTrade) > 0) {
                        foreach($equifaxTrade as $trade) {
                            if($trade['payment_rate_code'] > 1) {
                                $not_current = 1;
                            }
                        }
                    }
                    if(count($collectionArr) > 0) {
                        foreach($collectionArr as $collection) {
                            $date_assigned = $collection['assigned_date'];
                            if(isset($date_assigned)) {
                                $month_diff = Helpers::calculateMonthsDiff($date_assigned);
                                if($month_diff <= 12) {
                                    $collection_count = $collection_count + 1;
                                }
                            }
                        }
                    }
                    
                    if(count($statementData) > 0) {
                        foreach($statementData as $statement_date) {
                            $statement = strtolower($statement_date['statement']);
                            if(strpos($statement, 'warning')!== false) {
                                $fraud = 1;
                            }
                        }
                    }
                    
                    if(count($ownerTradePayment) > 0) {
                        foreach($ownerTradePayment as $payment) {
                            
                            if($payment['payment_rate_code'] == '9') {
                                $charge_off = 1;
                            }
                            $date_reported = $payment['date_reported'];
                            if(isset($date_reported)) {
                                $month_diff = Helpers::calculateMonthsDiff($date_reported);
                                if($month_diff <= 6) {
                                    if($payment['payment_rate_code'] >= 4) {
                                        $serious_derog = $serious_derog + 1;
                                    }
                                }
                            }
                        }
                    }

                    $dataEquiPrArr = [
                        'equifax_pr_rate' => true,
                        'not_current' => $not_current,
                        'serious_derog' => $serious_derog,
                        'charge_off' => $charge_off,
                        'collection_filed' => $collection_count,
                        'legal_suit' => $legal_code,
                        'fraud' => $fraud,
                        'bankrupt' => $bankrupt,
                        'hit_strength' => $hit_strength,
                        'thin_file' => $thin_file,
                        'high_inquiries' => $inquiry_count
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

            if(count($segmentationKeyOwners) > 0) {
                foreach($segmentationKeyOwners as $key => $owners) {
                    //owner fico score from equifax
                    $ownerScoreData = isset($owners['equifax_score_data']) ? $owners['equifax_score_data'] : [];
                    foreach($ownerScoreData as $score) {
                        if($score['description'] == 'FICO' || $score['description'] == 'BEACON') {
                            $ownerScore[$owners['app_owner_id']]['beacon'] = isset($score['score_value']) ? $score['score_value'] : null;
                        } else {
                            $ownerScore[$owners['app_owner_id']]['bni'] = isset($score['score_value']) ? $score['score_value'] : null;
                        }
                    }
                   
                    //equifax trade details
                    $equifaxTrade = isset($owners['owner_equifax_trade']) ? $owners['owner_equifax_trade'] : [];
                    $totalMonthsData = [];
                    if(count($equifaxTrade) > 0) {
                        foreach($equifaxTrade as $trade) {
                            if(isset($trade['open_date'])) {
                                $currentDate = new Carbon();
                                $open_date = new Carbon($trade['open_date']);
                                $diff_months = $open_date->diffInMonths($currentDate);
                                $totalMonthsData[] = $diff_months;
                            }
                        }
                    }
                    $ownerScore[$owners['app_owner_id']]['age_of_trade'] = count($totalMonthsData) > 0 ? max($totalMonthsData) : null;
                }
            }
        }

        $equifax_br = null;
        $equifax_pr = count($equifaxPR) > 0 ? max($equifaxPR) : null;
        //save equifax pr reasons code
        if (count($equifaxPRReasons) > 0) {
            $prResultData = Helpers::prepareEquifaxRiskFactorReasonData($equifaxPRReasons, $app_user_id, $app_id);
            $this->application->saveDecisionReasonCode($prResultData);
            $this->application->saveDecisionReasonCodeLog($prResultData);
        }
        $ret_checks = 0;
        $judgements = 0;
        $legal_suit = 0;
        $collection_amount = 0;
        $cust_not_correct = 2;
        $derog_count = 0;
        $equifaxBrVal = [];
        $businessDerogatory = Helpers::getEquifaxBussinessAllDarogatoryData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id])->toArray();
        $businessBanruptcies = Helpers::getEquifaxBussinessBankruptcyAllData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id])->toArray();
        $businessRefDetail = Helpers::getEquifaxBussinessAllRefDetailsData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id])->toArray();
        $businessCollection = Helpers::getEquifaxBussinessAllCollectionData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id], ['claim_amt', 'claim_date', 'reported_date', 'account_balance'])->toArray();
        $bizLegalDetail = Helpers::getEquifaxBussinessAllLegalData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id], ['claim_date','report_date', 'legal_amt', 'cn_legal_detail_desc'])->toArray();
        $bizFinancialDetail = Helpers::getEquifaxBussinessCnfinancialData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id], ['rating', 'payment_profile'])->toArray();
        $bizQuaterlyPayment = Helpers::getEquifaxBussinessAllPaymentTermQuaterlyData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id], ['past_due_period3'], ['orderBy' => 'ASC'])->toArray();

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
        //for biz legal detail
        if(count($bizLegalDetail) > 0) {
            foreach($bizLegalDetail as $legal) {
                $diff_months = Helpers::calculateMonthsDiff($legal['claim_date']);
                if($diff_months <= 24 && $legal['cn_legal_detail_desc'] == 'Legal Suits') {
                    $legal_suit = $legal_suit + $legal['legal_amt'];
                }
                if($diff_months <= 24 && $legal['cn_legal_detail_desc'] == 'Judgments') {
                    $judgements = $judgements + 1;
                }
            }
        }

        //for biz collection
        if(count($businessCollection) > 0) {
            foreach($businessCollection as $collection) {
                $months_diff = Helpers::calculateMonthsDiff($collection['claim_date']);
                if($months_diff <= 24) {
                    $collection_amount = $collection_amount + $collection['claim_amt'];
                }
            }
        }
        
        //for biz financial
        if(count($bizFinancialDetail) > 0) {
            foreach($bizFinancialDetail as $financial) {
                $payment_profile = !empty($financial['payment_profile']) ? str_split(substr($financial['payment_profile'], 0, 6)) : [];
                if(count($payment_profile) > 0) {
                    foreach($payment_profile as $profile) {
                        if((int)$profile >= '3') {
                            $derog_count = $derog_count + 1;
                        }
                    }
                }

                if(isset($financial['rating'][1]) && $financial['rating'][1] > '1') {
                    $cust_not_correct = 1;
                }
            }
        }
        //reference details
        if(count($businessRefDetail) > 0) {
            foreach($businessRefDetail as $ref_data) {
                if($ref_data['tot_past_due'] > 0) {
                    $cust_not_correct = 1;
                }
            }
        }
        
        $equifaxResponseData = Helpers::getEquifaxBusinessData(['app_user_id' => $app_user_id, 'app_id' => $app_id])->toArray();
        $hit_strength = '11';
        if(count($equifaxResponseData) > 0) {
            $hit_strength = isset($equifaxResponseData[0]['hit_indicator']) && $equifaxResponseData[0]['hit_indicator'] == 'NOHIT' ? '00' : '11';
        }
        
        $cur_quater = Helpers::getCurrentQuarter();
        $prev_year = date("Y",strtotime("-1 year"));
        $cur_year = date('Y');
        $year = [];
        $quarters = [];
        if(isset($cur_quater)) {
            if($cur_quater == 2) {
                $quarters = ['quarter1' => 1, 'quarter2' => 4];
                $year = ['cur_year' => $cur_year, 'prev_year' => $prev_year];
            } else if ($cur_quater == 1) {
                $quarters = ['quarter1' => 4, 'quarter2' => 3];
                $year = ['cur_year' => $prev_year, 'prev_year' => $prev_year];
            } else if ($cur_quater == 3) {
                $quarters = ['quarter1' => 2, 'quarter2' => 1];
                $year = ['cur_year' => $cur_year, 'prev_year' => $cur_year];
            } else {
                $quarters = ['quarter1' => 3, 'quarter2' => 2];
                $year = ['cur_year' => $cur_year, 'prev_year' => $cur_year];

            }
        }
        $business_bankrupt = count($businessBanruptcies) > 0 ? 1 : 2;
        if(count($businessDerogatory) > 0) {
            foreach($businessDerogatory as $derogatory) {
                if($derogatory['dero_code'] == 'RET') {
                    $ret_checks = $ret_checks + $derogatory['count'];
                } 
            }
        }

        //equifax br rule engine
        $dataEquiBrArr = [
            'equifax_br_rate' => true,
            'not_current' => $cust_not_correct,
            'ret_cheques' => $ret_checks,
            'judgements' => $judgements,
            'collection_amount' => $collection_amount,
            'legal_suit' => $legal_suit,
            'derog_count' => $derog_count,
            'bankrupt' => $business_bankrupt,
            'hit_strength' => $hit_strength,
        ];
        $result = $this->ruleEngineDataPrepare($dataEquiBrArr);
        if (!empty($result['all_matching_decision'])) {
            $equiBr = array_column($result['all_matching_decision'], 'decision');
            $equifaxBrVal = array_merge($equiBr, $equifaxBrVal);
            $brResultData = Helpers::prepareEquifaxRiskFactorReasonData($result['all_matching_decision'], $app_user_id, $app_id);
            $this->application->saveDecisionReasonCode($brResultData);
            $this->application->saveDecisionReasonCodeLog($brResultData);
        } else {
            $equifaxBrVal = [$result['status']];
        }
        $equifax_br = count($equifaxBrVal) > 0 ? max($equifaxBrVal) : null;
        if(count($ownerScore) > 0) {
            foreach($ownerScore as $key=>$fico) {
                $result = $this->ruleEngineDataPrepare(['risk_factor_calc' => true, 'bni_score' => isset($fico['bni']) ? $fico['bni'] : null, 'fico_score' => (int) isset($fico['beacon']) ? $fico['beacon'] : null, 'age_of_trade' => isset($fico['age_of_trade']) ? $fico['age_of_trade'] : null]);
                $riskFactorArr[$key] = $result['status'];
                $ownerArr = [];
                $ownerArr['fico_score'] = (int) isset($fico['beacon']) ? $fico['beacon'] : null;
                $ownerArr['bni_score'] = (int) isset($fico['bni']) ? $fico['bni'] : null;
                $ownerArr['age_of_trade'] = (int) isset($fico['age_of_trade']) ? $fico['age_of_trade'] : null;
                $this->application->saveOwnerInfo($ownerArr, $key);
                
            }
        }

        if(count($riskFactorArr) > 0) {
            //best fico
            $risk_factor = min($riskFactorArr);
            $best_fico_bni_data = Helpers::calcBestFicoBniData(['riskFactorArr' => $riskFactorArr, 'ownerScore' => $ownerScore]);
            if($best_fico_bni_data) {
                $arrScoreData['fico_score'] = isset($best_fico_bni_data['fico']) ? $best_fico_bni_data['fico'] : null;
                $arrScoreData['bni_score'] = isset($best_fico_bni_data['bni']) ? $best_fico_bni_data['bni'] : null;
            }
        }
        $arrScoreData['secured_exposer'] = $secured_exposer;
        $arrScoreData['unsecured_exposer'] = $unsecured_exposer;
        $this->application->updateApplication((int) $app_id, $arrScoreData);
        //call rule engine api for risk factor
        $data = ['app_user_id' => $app_user_id, 
            'app_id' => $app_id, 
            'owner_count' => $tot_owner,
            'risk_factor' => $risk_factor,
            'equifax_pr' => $equifax_pr,
            'equifax_br' => $equifax_br,
            'is_backend' => $is_backend,
            'consumer_alert' => $consumer_alert,
            'is_owner_esc_verified' => $is_owner_esc_verified,
            'personal_debt' => $personal_debt,
            'ownerScore' => $ownerScore
        ];

        $message = trans('activity_messages.decision_hit',['page'=> 'Decision Engine']);
        Helpers::trackApplicationActivity($message, $app_user_id, $app_id);
        $this->riskFactorCalculation($data, $hit_strength);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    /**
     * rule engine api for risk factor, interest rate and risk multiplier
     * 
     * @param array $attributes
     */
    public function riskFactorCalculation($attributes, $hit_strength)
    {
        $app_user_id      = $attributes['app_user_id'];
        $app_id           = $attributes['app_id'];
        $is_backend       = isset($attributes['is_backend']) ? $attributes['is_backend'] : null;
        $risk_factor      = $attributes['risk_factor'];
        
        $default_value = -1;
        $months_in_biz = null;
        $over_all_risk_factor = null;
        $personal_debt = $attributes['personal_debt'] * 12;
        $debtResult = $this->application->getDebtCapacity(['app_user_id'=>$app_user_id, 'app_id'=>$app_id])->toArray();
        $bizData = $this->application->getBizInformation((int) $app_user_id, (int) $app_id);
        if($bizData) {
            $date_established = isset($bizData->date_established) ? $bizData->date_established : null;
            $months_in_biz = Helpers::calculateMonthsDiff($date_established);
        }
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
        $dataArr = ['overall_risk_factor' => true, 'equifax_pr' => $attributes['equifax_pr'], 'equifax_br' => $attributes['equifax_br'], 'segmentation' => $risk_factor, 'dscr' => isset($existing_dscr) ? $existing_dscr : $default_value, 'nsf_three_months' => $nsf_last_3_month, 'nsf_twelve_months'=>$nsf_last_12_month, 'months_in_biz' => $months_in_biz];
        $result = $this->ruleEngineDataPrepare($dataArr);
        if(isset($result['status']) && $result['status'] > 0) {
            $over_all_risk_factor = $result['status'];
        }
        
        $interest_rate = null;
        $prime_interest_rate = $this->userRepo->getActivePrimeRate();
        if(!empty($prime_interest_rate)) {
            $interest_rate = $prime_interest_rate + 2;
        }

        $where = ['app_user_id'=>$app_user_id, 'app_id'=>$app_id];
        $arrData = [ 'app_user_id'=>$app_user_id, 
            'app_id'=>$app_id, 
            'risk_factor'=>$risk_factor,
            'system_risk_factor'=>$over_all_risk_factor,
            'equifax_pr'=>$attributes['equifax_pr'],
            'equifax_br'=>$attributes['equifax_br'],
            'personal_debt' => $personal_debt,
       ];
        //save credit limit data
        $this->application->saveCreditLimit($where, $arrData);

        //limit calculator
        $data = ['app_user_id' => $app_user_id, 
            'app_id' => $app_id, 
            'interest_rate' => $interest_rate, 
            'risk_factor' => $risk_factor,
            'over_all_risk_factor' => $over_all_risk_factor,
            'owner_count' => $attributes['owner_count'],
            'equifax_pr' => $attributes['equifax_pr'],
            'equifax_br' => $attributes['equifax_br'],
            'debtResult' => $debtResult,
            'is_backend' => $is_backend,
            'consumer_alert' => $attributes['consumer_alert'],
            'is_owner_esc_verified' => $attributes['is_owner_esc_verified'],
            'personal_debt' => $personal_debt,
            'existing_dscr' => $existing_dscr,
            'ownerScore' => $attributes['ownerScore']
        ];
        $this->limitCalculator($data, $hit_strength, $months_in_biz);
    }
    
    /**
     * limit calculation and final decision
     * 
     * @param array $attributes
     */
    public function limitCalculator($attributes, $hit_strength, $months_in_biz)
    {
        $temp_other_income = $max_internal_exposer = 0;
        $app_id         = $attributes['app_id'];
        $app_user_id    = $attributes['app_user_id'];
        $personal_debt  = $attributes['personal_debt'];
        $existing_dscr  = $attributes['existing_dscr'];
        $debtResult     = $attributes['debtResult'];
        $is_backend     = isset($attributes['is_backend']) ? $attributes['is_backend'] : null;
        $threshold_dscr = config('b2c_common.THRESHOLD_DSCR');
        $new_interest_rate  = ($attributes['interest_rate']/100);
        $interest_rate = $attributes['interest_rate'];
        $riskFactor = $codeArr = $referArr = $declineCodeArr = [];
        $amortization_years = config('b2c_common.AMORTIZATION_YEARS');
        $room_for_debt = null;
        
        $appData = $this->application->find($app_id);        
        $equifaxResponseData = Helpers::getEquifaxBusinessData(['app_user_id'=>$app_user_id, 'app_id'=>$app_id])->toArray();
        
        $ebitda         = isset($debtResult[0]['ebitda']) ? $debtResult[0]['ebitda'] : null;
        $revenue        = isset($debtResult[0]['revenue']) ? $debtResult[0]['revenue'] : null;
        $tot_existing_debt = isset($debtResult[0]['tot_existing_debt']) ? $debtResult[0]['tot_existing_debt'] : null;
        $temp_nsf_last_3_month = isset($debtResult[0]['nsf_last_3_month']) ? $debtResult[0]['nsf_last_3_month'] : null;
        $temp_nsf_last_12_month = isset($debtResult[0]['nsf_last_12_month']) ? $debtResult[0]['nsf_last_12_month'] : null;
        $temp_bank_stmt = isset($debtResult[0]['is_available_recent_stmt']) ? $debtResult[0]['is_available_recent_stmt'] : null;
        $is_manual_stmt = isset($debtResult[0]['is_manual_stmt']) ? $debtResult[0]['is_manual_stmt'] : null;
        $temp_sum_loan_credit = isset($debtResult[0]['sum_loan_credit']) ? $debtResult[0]['sum_loan_credit'] : null;
        $other_income = isset($debtResult[0]['other_income']) ? $debtResult[0]['other_income'] : null;
        $operating_cost = $revenue - $ebitda;
        $default_operating_val = config('b2c_common.DEFAULT_OPERATING_VAL_DECISION');
        if($revenue == null || $ebitda == null) {
            $operating_cost = $default_operating_val;
        }
        if($other_income > 0) {
            $temp_other_income = ($revenue > 0) ? (($other_income/$revenue)*100) : 0;
        }

        if($appData['annual_sale_amt'] > 0) {
            $revenue_variance = ((($appData['annual_sale_amt'] - $revenue)/$appData['annual_sale_amt']) * 100);
        } else {
            $revenue_variance = 0;
        }
        $getAllscannedPdf = \Helpers::getAllnonNativeDoc($app_id,2);
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
        $limit_value = min($limitArr);
        $dscr_based_limit = round(($limit_value*2), -3)/2;
        if($new_interest_rate == null) {		
            $dscr_based_limit = null;		
        }
        
        $max_internal_exposer = (config('b2c_common.MAX_SECURED_LIMIT') - ($appData['secured_exposer'] + $appData['unsecured_exposer']));
        $max_internal_exposer = ($max_internal_exposer > 0) ? $max_internal_exposer : 0;
        $max_unsecured_exposure = $max_internal_exposer - $appData['unsecured_exposer'];
        $max_unsecured_exposure = ($max_unsecured_exposure > 0) ? $max_unsecured_exposure : 0;
        $max_secured_exposure = $max_unsecured_exposure - $appData['secured_exposer'];
        $max_secured_exposure = ($max_secured_exposure > 0) ? $max_secured_exposure : 0;
        $security_asset_value = isset($appData['security_assessed_val']) ? $appData['security_assessed_val'] : config('b2c_common.SECURITY_ASSESSED_VALUE');
        $limit = Helpers::calcStandardLimit($dscr_based_limit, $max_internal_exposer, $security_asset_value, $appData['unsecured_exposer']);
        $where = ['app_user_id'=>$app_user_id, 'app_id'=>$app_id];
        $arrData = [ 'max_debt'=> $unsecured_max, 'debt_room' => $room_for_debt, 'risk_capacity' =>$pv_result, 'cust_limit'=>$limit];
        //save credit limit data
        $this->application->saveCreditLimit($where, $arrData);
        if(isset($debtResult[0]['id'])) {
            $this->application->saveTempIncomeLiability(['existing_dscr' => $existing_dscr], (int)$debtResult[0]['id']);
        }
        
        $default_value = -1;
        //rule engine api call for final decision
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
            'revenue_variance' => ( $revenue_variance > 0) ? $revenue_variance : 0,
            'bank_stmt' => ($getAllscannedPdf > 0 && $is_manual_stmt!=1) ? 2 :
            (isset($temp_bank_stmt)  ? $temp_bank_stmt : 2),
            'other_income_percentage' => isset($temp_other_income) ? $temp_other_income : $default_value,
            'iovation' => isset($appData['fraud_score']) ? $appData['fraud_score'] : 0,
            'owner_percentage' => $appData['legal_age_owners_perc'],
            'esc_business' => $esc_business_data,
            'primary_owner_esc' => isset($attributes['is_owner_esc_verified']) ? $attributes['is_owner_esc_verified'] : 2,
            'limit_lt_lowlimit' => isset($limit) ? $limit : $default_value,
            'hit_strength' => $hit_strength,
            'months_in_biz' => $months_in_biz,
            'is_missing_trans' => isset($debtResult[0]['is_missing_trans']) ? $debtResult[0]['is_missing_trans'] : $default_value,
            'consumer_alert' => $attributes['consumer_alert'],
            'operating_cost' => isset($operating_cost) ? $operating_cost : $default_operating_val,
            'sic_professional' => !empty($industry_cls) ? $industry_cls : $default_value
         ];

        $result = $this->ruleEngineDataPrepare($dataArr);
        if(isset($result['status'])) {
            $decision = Helpers::decisionByRuleEngine($result['status']);
        }
        $arrAppData['decision'] = $decision;
        $programme_30k = true;
        $program_type = config('b2c_common.STANDARD_PROGRAM');
        $new_system_risk_factor = $attributes['over_all_risk_factor'];
        $new_segmentation = isset($attributes['risk_factor']) ? (int) $attributes['risk_factor'] : $default_value;
        $reasonsData = $reasonsDataLog = [];
        $is_refer = false;
        if(isset($result['all_matching_decision'])) {
            $decisionResult = $result['all_matching_decision'];
            $loggedInData = \Auth::user();
            $curent_date = Helpers::getCurrentDateTime();
            if(count($decisionResult) > 0) {
                foreach($decisionResult as $key => $result) {
                    $resultData[] = [
                        'app_user_id' => $app_user_id,
                        'app_id' => $app_id,
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'created_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'updated_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'created_at' => $curent_date,
                        'updated_at' => $curent_date,
                    ];
                    $resultDataNew[] = [
                        'app_user_id' => $app_user_id,
                        'app_id' => $app_id,
                        'decision' => isset($result->decision) ? $result->decision : null,
                        'code' => isset($result->code) ? $result->code : null,
                        'reason' => isset($result->text) ? $result->text : null,
                        'is_auto_manual' => isset($is_backend) ? 2 : 1,
                        'created_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'updated_by' => isset($loggedInData->id) ? $loggedInData->id : null,
                        'created_at' => $curent_date,
                        'updated_at' => $curent_date,
                    ];
                    
                    if(!empty($result->program) && $result->program == 'FALSE') {
                        $programme_30k = false;
                        if($result->decision == 'Approved' || $result->decision == 'Decline') {
                            $is_refer = true;
                        }
                    }
                }
                $reasonsData = $resultData;
                $reasonsDataLog = $resultDataNew;
            } else {
                $arrReasonData = [];
                $arrReasonData['app_user_id'] =  $app_user_id;
                $arrReasonData['app_id'] =  $app_id;
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
            
            $returnData = [];
            $stated_revenue_20_percent = $appData['annual_sale_amt'] * 20/100;
            $new_segmentation = isset($attributes['risk_factor']) ? (int) $attributes['risk_factor'] : $default_value;
            $high_risk_industry = \Helpers::getIndustryName($appData['industry_id'])->is_high_credit_industry;
            $new_30k_approved_amt = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $appData['secured_exposer'], $appData['unsecured_exposer'], config('b2c_common.30k_PROGRAM'));
            $limit_program_type = config('b2c_common.STANDARD_PROGRAM');
            if($decision == config('b2c_common.APPROVE')) {
                $limitArr = [$limit, $new_30k_approved_amt];
                $max_program_type_limit = array_keys($limitArr, max($limitArr));
                if(isset($max_program_type_limit[0]) && $max_program_type_limit[0] == config('b2c_common.30K_PROGRAM_MAX_KEY')) {
                    $program_type = config('b2c_common.30k_PROGRAM');
                    $limit_program_type = config('b2c_common.30k_PROGRAM');
                    $ownerScore = $attributes['ownerScore'];
                    if(count($ownerScore)) {
                        $returnData = $this->calcProgramSegmentationRule($ownerScore, $app_id, '30k_segmentation');
                        $new_segmentation = $returnData['risk_factor'];
                    }
                    $dataArr['high_credit_industry'] = $high_risk_industry;
                    $dataArr['30k_risk_factor'] = $new_segmentation;
                    $new_system_risk_factor = $this->programOverALlRiskFactorRule($dataArr);
                    //new approval limit calc
                    $new_approved_amt = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $appData['secured_exposer'], $appData['unsecured_exposer'], config('b2c_common.30k_PROGRAM'));
                    $limit = $new_30k_approved_amt;
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
            
            //30k decision rule check
            if($programme_30k == true && $decision != config('b2c_common.APPROVE')) {
                $program_type = config('b2c_common.30k_PROGRAM');
                $limit_program_type = config('b2c_common.30k_PROGRAM');
                $ownerScore = $attributes['ownerScore'];
                
                if(count($ownerScore)) {
                    $returnData = $this->calcProgramSegmentationRule($ownerScore, $app_id, '30k_segmentation');
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
                    $program30kReasons = $this->prepareAndSaveProgramReasons($programme30kResult, $app_user_id, $app_id, $curent_date, $loggedInData->id, $is_backend);
                    if(count($ownerScore)) {
                        $returnData = $this->calcProgramSegmentationRule($ownerScore, $app_id, '5k_segmentation');
                        $new_segmentation = $returnData['risk_factor'];
                    }
                    $dataArr['segmentation'] = $new_segmentation;
                    $new_system_risk_factor = $this->program5kOverALlRiskFactorRule($dataArr);
                    //new approval limit calc
                    $new_approved_amt = Helpers::calcProgramApprovalAmount($stated_revenue_20_percent, $appData['loan_amount'], $appData['secured_exposer'], $appData['unsecured_exposer'], config('b2c_common.5k_PROGRAM'));
                    $limit = $new_approved_amt;
                    $prepare30kData['5k_fico_score'] = isset($returnData['fico_score']) ? $returnData['fico_score'] : null;
                    $prepare30kData['5k_bni_score'] = isset($returnData['bni_score']) ? $returnData['bni_score'] : null;
                    $prepare30kData['5k_risk_factor'] = $new_segmentation;
                    $prepare30kData['limit_lt_lowlimit'] = $limit;
                    $prepare30kData['overall_risk_factor'] = $new_system_risk_factor;
                    $prepare5kData = Helpers::prepare5kProgrammeData($prepare30kData);
                    $programme5kResult = $this->ruleEngineDataPrepare($prepare5kData);
                    $arrAppData['decision'] = Helpers::decisionByRuleEngine($programme5kResult['status']);
                    $program5kReasons = $this->prepareAndSaveProgramReasons($programme5kResult, $app_user_id, $app_id, $curent_date, $loggedInData->id, $is_backend);
                    $finalReasonArr = array_merge($program5kReasons['programArr'], $program30kReasons['programArr']);
                    $finalReasonLogArr = array_merge($program5kReasons['programLogArr'], $program30kReasons['programLogArr']);
                    if(count($finalReasonArr) > 0) {dd('eeeeee');
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
        
        $final_interest_rate = null;
        //final interest rate calc
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
        $arrAppData['final_risk_factor'] = isset($new_system_risk_factor) ? $new_system_risk_factor : null;
        $arrAppData['max_internal_exposer'] = isset($max_internal_exposer) ? $max_internal_exposer : null;
        $arrAppData['max_secured_exposure'] = isset($max_secured_exposure) ? $max_secured_exposure : null;
        $arrAppData['max_unsecured_exposure'] = isset($max_unsecured_exposure) ? $max_unsecured_exposure : null;
        $arrAppData['limit_program_type'] = isset($limit_program_type) ? $limit_program_type : null;
        $this->application->updateApplication((int) $app_id, $arrAppData);
        
        $where = ['app_user_id'=> $app_user_id, 'app_id'=> $app_id];
        $this->application->saveCreditLimit($where, ['cust_limit'=>$limit, 'risk_factor' => $new_segmentation, 'system_risk_factor' => $new_segmentation, 'interest_rate'=>$final_interest_rate]);
    }
}
