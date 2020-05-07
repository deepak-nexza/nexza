<?php

namespace App\Http\Controllers\Contracts\Traits;

use Helpers;
use Carbon\Carbon;

trait PrepareDataTrait {
    
    /**
     * prepare owner score data
     * 
     * @param array $ownerScoreData
     * @return array
     */
     public function prepareOwnerScoreData($ownerScoreData)
     {
         $fico_score = $bni_score = null;
         foreach($ownerScoreData as $score) {
            if(isset($score['description']) && ($score['description'] == 'FICO' || $score['description'] == 'BEACON')) {
                $fico_score = !empty($score['score_value']) ? $score['score_value'] : null;
            } else {
                $bni_score = !empty($score['score_value']) ? $score['score_value'] : null;
            }
        }
        $score_data['fico_score'] = $fico_score;
        $score_data['bni_score'] = $bni_score;
        return $score_data;
     }
     
    /**
     * prepare owner trade data
     * 
     * @param array $ownerTradeData
     * @return type array
     */
     public function prepareOwnerTradeData($ownerTradeData)
     {
         $cust_not_current = 0;
         $totalMonthsData = [];
         foreach($ownerTradeData as $trade) {
            if(isset($trade['open_date'])) {
                $currentDate = new Carbon();
                $open_date = new Carbon($trade['open_date']);
                $diff_months = $open_date->diffInMonths($currentDate);
                $totalMonthsData[] = $diff_months;
            }
            
            if($trade['payment_rate_code'] > 1 && $trade['payment_rate_code'] != 9) {
                $cust_not_current = 1;
            }
        }
        
        $returnData['cust_not_current'] = $cust_not_current;
        $returnData['age_of_trade'] = count($totalMonthsData) > 0 ? max($totalMonthsData) : null;
        return $returnData;
     }
     
     /**
      * prepare owner inquiry data
      * 
      * @param array $ownerinquiryData
      * @return int
      */
     public function prepareOwnerInquiryData($ownerinquiryData)
     {
        $inquiry_count = 0;
        foreach ($ownerinquiryData as $inquiry) {
            $date_inquiry = $inquiry['date_of_local_enquiry'];
            if (isset($date_inquiry)) {
                $month_diff = Helpers::calculateMonthsDiff($date_inquiry);
                if ($month_diff <= 12) {
                    $inquiry_count = $inquiry_count + 1;
                }
            }
        }
        return $inquiry_count;
    }
    
    /**
     * prepare all owner statement data
     * 
     * @param array $ownerStatementData
     * @return int
     */
     public function prepareOwnerStatementData($ownerStatementData)
     {
        $fraud = 2;
        foreach($ownerStatementData as $statement_date) {
            $statement = strtolower($statement_date['statement']);
            if(strpos($statement, 'warning')!== false) {
                $fraud = 1;
            }
        }
        return $fraud;
    }
    
    /**
     * prepare owner legal data
     * 
     * @param type $ownerLegalData
     * @return int
     */
     public function prepareOwnerLegalData($ownerLegalData)
     {
        $legal_suit = 2;
        foreach($ownerLegalData as $legal) {
            $code = trim($legal['code']);
            if($code == 'AJ') {
                $legal_suit = 1;
            }
        }
        return $legal_suit;
    }
    
    /**
     * prepare owner collection data
     * 
     * @param array $ownerCollectiondata
     * @return int
     */
     public function prepareOwnerCollectionData($ownerCollectiondata)
     {
        $collection_count = 0;
        foreach($ownerCollectiondata as $collection) {
            $date_assigned = $collection['assigned_date'];
            if(isset($date_assigned)) {
                $month_diff = Helpers::calculateMonthsDiff($date_assigned);
                if($month_diff <= 12) {
                    $collection_count = $collection_count + 1;
                }
            }
        }
        return $collection_count;
    }

    /**
     * prepare business score data
     * 
     * @param array $equiScoreData
     * @return array
     */
    public function prepareBusinessScoreData($equiScoreData)
     {
         $ci_score_index = $pi_score_index = null;
         foreach($equiScoreData as $score) {
            if(isset($score['score_type']) && $score['score_type'] == 'CIScore') {
                $ci_score_index = isset($score['score_index']) ? $score['score_index'] : null;
            }
            if(isset($score['score_type']) && $score['score_type'] == 'PaymentIndex') {
                $pi_score_index = isset($score['score_index']) ? $score['score_index'] : null;
            }
        }
        $scoreData['ci_score_index'] = $ci_score_index;
        $scoreData['pi_score_index'] = $pi_score_index;
        return $scoreData;
     }
     
     /**
      * 
      * @param array $ownerScoreData
      * @return array
      */
     public function prepareBusinessDerogData($businessDerogatory)
     {
         $returned_cheques = $judgements_count = $collection_amount = $legal_suits_amount = 0;
         foreach($businessDerogatory as $derogatory) {
            if($derogatory['dero_code'] == 'RET') {
                $returned_cheques = $returned_cheques + $derogatory['count'];
            } 
            if($derogatory['dero_code'] == 'JGM') {
                $judgements_count = $judgements_count + $derogatory['count'];
            }
            if($derogatory['dero_code'] == 'COL') {
                $collection_amount = $collection_amount + $derogatory['amount'];
            }
            if($derogatory['dero_code'] == 'ACT') {
                $legal_suits_amount = $legal_suits_amount + $derogatory['amount'];
            }
        }
        $returnData['returned_cheques'] = $returned_cheques;
        $returnData['judgements_count'] = $judgements_count;
        $returnData['collection_amount'] = $collection_amount;
        $returnData['legal_suits_amount'] = $legal_suits_amount;
        return $returnData;
     }
     
    /**
     * prepare business collection data
     * 
     * @param array $businessCollection
     * @return int
     */
     public function prepareBusinessCollectionData($businessCollection)
     {
        $collection_amount = 0;
        foreach($businessCollection as $collection) {
            $months_diff = Helpers::calculateMonthsDiff($collection['claim_date']);
            if($months_diff <= 24) {
                $collection_amount = $collection_amount + $collection['claim_amt'];
            }
        }
        $collectionData['collection_amount'] = $collection_amount;
        return $collectionData;
    }
    
    /**
     * prepare business legal data
     * 
     * @param array $bizLegalDetail
     * @return int
     */
     public function prepareBusinessLegalData($bizLegalDetail)
     {
        $judgements = $legal_suit = 0;
        foreach($bizLegalDetail as $legal) {
            $diff_months = Helpers::calculateMonthsDiff($legal['claim_date']);
            if($diff_months <= 24 && $legal['cn_legal_detail_desc'] == 'Legal Suits') {
                $legal_suit = $legal_suit + $legal['legal_amt'];
            }
            if($diff_months <= 24 && $legal['cn_legal_detail_desc'] == 'Judgments') {
                $judgements = $judgements + 1;
            }
        }
        $legalData['judgements_count'] = $judgements;
        $legalData['legal_suits_amount'] = $legal_suit;
        return $legalData;
    }
    
    /**
     * prepare business financial data
     * 
     * @param array $bizFinancialDetail
     * @return int
     */
     public function prepareBizFinancialData($bizFinancialDetail)
     {
        $derog_count = 0;
        $cust_not_correct = 2;
        foreach($bizFinancialDetail as $financial) {
            $payment_profile = !empty($financial['payment_profile']) ? str_split(substr($financial['payment_profile'], 0, 6)) : [];
            if(count($payment_profile) > 0) {
                foreach($payment_profile as $profile) {
                    if((int)$profile >= '3') {
                        $derog_count = $derog_count + 1;
                    }
                }
            }
            
            //cust not current
            if(isset($financial['rating'][1]) && $financial['rating'][1] > '1') {
                $cust_not_correct = 1;
            }
        }
        $financeData['derog_count'] = $derog_count;
        $financeData['cust_not_correct'] = $cust_not_correct;
        return $financeData;
     }
     
     /**
     * prepare owner trade payment 
     * 
     * @param array $ownerTradePayment
     * @return int
     */
     public function prepareOwnerTradePaymentData($ownerTradePayment)
     {
        $charge_off = $serious_derog = 2;
        foreach($ownerTradePayment as $payment) {
            if($payment['payment_rate_code'] == '9') {
                $charge_off = 1;
            }
            $date_reported = $payment['date_reported'];
            if(isset($date_reported)) {
                $month_diff = Helpers::calculateMonthsDiff($date_reported);
                if($month_diff <= 6) {
                    if($payment['payment_rate_code'] >= 4) {
                        $serious_derog = 1;
                    }
                }
            }
        }
        $paymentData['charge_off'] = $charge_off;
        $paymentData['serious_derog'] = $serious_derog;
        return $paymentData;
    }
    
    /**
     * prepare and save program reasons
     * 
     * @param array $programResult
     * @param int $appUserId
     * @param int $appId
     * @param date $curent_date
     * @param int $loggedIn_id
     * @param int $isBackend
     */
    public function prepareAndSaveProgramReasons($programResult, $appUserId, $appId, $curent_date, $loggedIn_id, $isBackend)
    {
        $programReasons['programArr'] = $programReasons['programLogArr'] = [];
        $programArr = $programLogArr = [];
        if(!empty($programResult['all_matching_decision'])) {
            foreach($programResult['all_matching_decision'] as $resons) {
                $program = [
                    'app_user_id' => $appUserId,
                    'app_id' => $appId,
                    'decision' => isset($resons->decision) ? $resons->decision : null,
                    'code' => isset($resons->code) ? $resons->code : null,
                    'reason' => isset($resons->text) ? $resons->text : null,
                    'is_auto_manual' => isset($isBackend) ? 2 : 1,
                    'created_by' => $loggedIn_id,
                    'updated_by' => $loggedIn_id,
                    'created_at' => $curent_date,
                    'updated_at' => $curent_date,
                ];
                $programLogArr[] = $program;
                unset($program['is_auto_manual']);
                $programArr[] = $program;
            }
        }
        $programReasons['programArr'] = $programArr;
        $programReasons['programLogArr'] = $programLogArr;
        return $programReasons;
    }
    
    /**
     * new segmentation calc for program
     * 
     * @param array $ownerScore
     * @param int $appId
     * @param string $segmentation_type
     * @return type
     */
    public function calcProgramSegmentationRule($ownerScore, $appId, $segmentation_type)
    {
        $riskFactorArr30k = [];
        $riskFactor = null;
        foreach($ownerScore as $key=>$fico) {
            $fico_score = isset($fico['fico']) ? $fico['fico'] : (isset($fico['beacon']) ? $fico['beacon'] : null);
            $result30k = $this->ruleEngineDataPrepare([$segmentation_type => true, 'bankruptcy_score' => $fico['bni'], 'fico' => $fico_score, 'oldest_trade' => $fico['age_of_trade']]);
            $riskFactorArr30k[$key] = $result30k['status'];
        }

        if(count($riskFactorArr30k) > 0) {
           //best fico
           $riskFactor = min($riskFactorArr30k);
           $best_fico_bni_data = Helpers::calcBestFicoBniData(['riskFactorArr' => $riskFactorArr30k, 'ownerScore' => $ownerScore]);
           if($best_fico_bni_data) {
               $arrScoreData['fico_score'] = isset($best_fico_bni_data['fico']) ? $best_fico_bni_data['fico'] : (isset($best_fico_bni_data['beacon']) ? $best_fico_bni_data['beacon'] : null);
               $arrScoreData['bni_score'] = isset($best_fico_bni_data['bni']) ? $best_fico_bni_data['bni'] : null;
               $this->application->updateApplication((int) $appId, $arrScoreData);
           }
        }
        $returnData['fico_score'] = $arrScoreData['fico_score'];
        $returnData['bni_score'] = $arrScoreData['bni_score'];
        $returnData['risk_factor'] = $riskFactor;
        return $returnData;
    }
    
    /**
     * over all risk factor rule calc for 30k
     * 
     * @param array $dataArr
     * @return int
     */
    public function programOverALlRiskFactorRule($dataArr)
    {
        $attributes = [
            '30k_over_all_risk_factor' => true,
            'dscr' => $dataArr['existing_dscr'],
            'equifax_pr' => $dataArr['equifax_pr'],
            'equifax_br' => $dataArr['equifax_br'],
            'months_in_biz' => $dataArr['months_in_biz'],
            'segmentation' => $dataArr['30k_risk_factor'],
            'nsf_three_months' => $dataArr['nsf_3_months'],
            'nsf_twelve_months' => $dataArr['nsf_12_months'],
            'high_risk_industry' => isset($dataArr['high_credit_industry']) ? $dataArr['high_credit_industry'] : -1
        ];
        $result = $this->ruleEngineDataPrepare($attributes);
        return $result['status'];
    }
    
    /**
     * over all risk factor rule calc for 5k
     * 
     * @param array $dataArr
     * @return int
     */
    public function program5kOverALlRiskFactorRule($dataArr)
    {
        $attributes = [
            '5k_over_all_risk_factor' => true,
            'equifax_pr' => $dataArr['equifax_pr'],
            'equifax_br' => $dataArr['equifax_br'],
            'segmentation' => $dataArr['segmentation'],
            'months_in_biz' => $dataArr['months_in_biz'],
        ];
        $result = $this->ruleEngineDataPrepare($attributes);
        return $result['status'];
    }
    
    /**
     * prepare 30k overall risk factor data
     * 
     * @param array $dataArr
     * @param array $bankStmtDataArr
     * @param int $high_risk_industry
     * return type array
     */
    public function prepare30kRuleData($dataArr, $bankStmtDataArr, $high_risk_industry)
    {
        $dataArr30K = [
            'equifax_br' => $dataArr['equifax_br_factor'],
            'equifax_pr' => $dataArr['equifax_pr_factor'],
            'months_in_biz' => $dataArr['months_in_biz'],
            'high_credit_industry' => isset($high_risk_industry) ? $high_risk_industry : -1,
            'existing_dscr' => $bankStmtDataArr['dscr'],
            'nsf_3_months' => $bankStmtDataArr['nsf_three_months'],
            'nsf_12_months' => $bankStmtDataArr['nsf_twelve_months'],
            'revenue_variance' => $bankStmtDataArr['revenue_variance'],
            'loan_credits' => $bankStmtDataArr['loan_credits'],
            'operating_cost' => $bankStmtDataArr['operating_cost'],
            'bank_stmt' => $bankStmtDataArr['no_of_bank_statements'],
            'other_income_percentage' => $bankStmtDataArr['other_income'],
            'is_missing_trans' => $bankStmtDataArr['missing_transaction'],
            'requested_amount' => isset($dataArr['requested_amount']) ? $dataArr['requested_amount'] : null,
        ];
        return $dataArr30K;
    }
    
    /**
     * get total unsecured amount
     * 
     * @param array $attr
     * @return mixed
     */
    public function getTotalExistingUnsecuredAmount($attr)
    {
        $unsecured_approved_amount = $this->application->getUnsecuredApprovedAmount(['app_id' => $attr['app_id'], 'app_user_id' => $attr['app_user_id']]);
        $unsecured_funded_amount = $this->application->getUnsecuredTotalFundedAmount(['app_id' => $attr['app_id'], 'app_user_id' => $attr['app_user_id']]);
        return (!empty($unsecured_approved_amount) ? $unsecured_approved_amount : 0 ) + (!empty($unsecured_funded_amount) ? $unsecured_funded_amount : 0);
    }
    
    /**
     * get total existing secured amount
     * 
     * @param array $attr
     * @return mixed
     */
    public function getTotalExistingScuredAmount($attr)
    {
        $secured_approved_amount = $this->application->getSecuredApprovedAmount(['app_id' => $attr['app_id'], 'app_user_id' => $attr['app_user_id']]);
        $secured_funded_amount = $this->application->getSecuredTotalFundedAmount(['app_id' => $attr['app_id'], 'app_user_id' => $attr['app_user_id']]);
        return (!empty($secured_approved_amount) ? $secured_approved_amount : 0 ) + (!empty($secured_funded_amount) ? $secured_funded_amount : 0);
    }
}
