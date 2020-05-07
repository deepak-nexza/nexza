<?php

namespace App\Http\Controllers\Contracts\Traits;

use Session;
use Helpers;
use Carbon\Carbon;
use App\Libraries\RuleEngineApi;

trait RuleEngineTrait {
    
    /**
     * Rule engine data prepare
     * 
     * @param array $appDetails
     * @return mixed
     */
    public function ruleEngineDataPrepare($data)
    {
        try {
            $response = [];
            $rules = self::ruleEngineObject();
            //$authorization = self::makeAuthRuleEngine($rules);
            $default_value = config('b2c_common.RULE_DEFAULT_VALUE');
            
            //qualifying question
            $question_1 = isset($data['quesAnsArr'][1]) ? (int) $data['quesAnsArr'][1] : $default_value;
            $question_2 = isset($data['quesAnsArr'][7]) ? (int) $data['quesAnsArr'][7] : $default_value;
            $question_3 = isset($data['quesAnsArr'][4]) ? (int) $data['quesAnsArr'][4] : $default_value;
            $question_4 = isset($data['quesAnsArr'][6]) ? (int) $data['quesAnsArr'][6] : $default_value;
            $question_5 = isset($data['quesAnsArr'][5]) ? (int) $data['quesAnsArr'][5] : $default_value;
            $question_6 = isset($data['quesAnsArr'][8]) ? (int) $data['quesAnsArr'][8] : $default_value;
            
            //business structure
            $biz_structure = isset($data['entityArr']['entitytyp']) && $data['entityArr']['entitytyp'] > 0 ? (int) $data['entityArr']['entitytyp'] : $default_value;
            $annual_sale = isset($data['gross_amount']) && $data['gross_amount'] > 0 ? (int) $data['gross_amount'] : $default_value;
            $loan_amt = isset($data['loan_amount']) && $data['loan_amount'] > 0 ? (int) $data['loan_amount'] : $default_value;
            $gross_amount = isset($data['gross_amount']) && $data['gross_amount'] > 0 ? (int) $data['gross_amount'] : $default_value;
            $business_years = isset($data['years_in_business']) ? (int) $data['years_in_business'] : $default_value;
            $sin_no = isset($data['sin']) ? (int) $data['sin'] : $default_value;
            $agree_terms = isset($data['terms_agree']) ? (int) $data['terms_agree'] : $default_value;
            
            //default for knockouts
            $table_name = config('ruleengine.KNOCKOUT_TABLE_ID');
            $dataArr= ['loc_not_biz_purpose' => $question_1, 'account_not_opening' => $question_2, 'outside_canada' => $question_3, 'mor_max_revenue' => $annual_sale, 'less_than_min_revenue' => $annual_sale, 'business_structure' => $biz_structure, 'industry' => false, 'mor_max_loc' => $loan_amt, 'less_min_loc' => $loan_amt, 'months_in_biz' => $business_years, 'owner_age' => true, 'partner_decline' => $default_value, 'terms_condition' => $agree_terms, 'sin' => $sin_no, 'industry_operations'=>$question_4, 'visit_branch'=>$question_5, 'credit_check'=>$question_6];
            
            $result = $this->ruleEngineApiCall($rules, $data, $table_name, $dataArr);
            return $result;
        } catch (Exception $ex) {
            return Helpers::ajaxResponse(false, null, Helpers::getExceptionMessage($ex));
        }
    }
    
    /**
     * Rule engine data prepare
     * 
     * @param array $appDetails
     * @return mixed
     */
    public function assistedRuleEngineDataPrepare($data)
    {
        try {
            $response = [];
            $rules = self::ruleEngineObject();
            //$authorization = self::makeAuthRuleEngine($rules);
            $default_value = config('b2c_common.RULE_DEFAULT_VALUE');
            
            //assisted qualifying question
            $question_1 = isset($data['quesAnsArr'][1]) ? (int) $data['quesAnsArr'][1] : $default_value;
            $question_2 = isset($data['quesAnsArr'][5]) ? (int) $data['quesAnsArr'][5] : $default_value;
            $question_3 = isset($data['quesAnsArr'][4]) ? (int) $data['quesAnsArr'][4] : $default_value;
            $question_4 = isset($data['quesAnsArr'][6]) ? (int) $data['quesAnsArr'][6] : $default_value;
            $question_5 = isset($data['quesAnsArr'][8]) ? (int) $data['quesAnsArr'][8] : $default_value;
            
            //business structure
            $annual_sale = isset($data['gross_amount']) && $data['gross_amount'] > 0 ? (int) $data['gross_amount'] : $default_value;
            $loan_amt = isset($data['requested_amount']) && $data['requested_amount'] > 0 ? (int) $data['requested_amount'] : $default_value;
            $unsecured_amount = isset($data['unsecured_amount']) && $data['unsecured_amount'] > 0 ? (int) $data['unsecured_amount'] : $default_value;
            $gross_amount = isset($data['annual_revenue']) && $data['annual_revenue'] > 0 ? (int) $data['annual_revenue'] : $default_value;
            $sin_no = isset($data['sin']) ? (int) $data['sin'] : $default_value;
            $agree_terms = isset($data['terms_agree']) ? (int) $data['terms_agree'] : $default_value;
            
            //default for knockouts
            $table_name = config('ruleengine.ASSISTED_KNOCKOUT_TABLE');
            $dataArr= ['request_for_biz_purpose' => $question_1, 
                    'chequing_acc_opening' => $question_2, 
                    'does_not_operate_outside_canada' => $question_3,
                    'does_not_operate_in_negative_industry'=>$question_4,
                    'agree_credit_check'=>$question_5,
                    'negative_industry' => false, 
                    'requested_amount' => $loan_amt, 
                    'unsecured_amount' => $unsecured_amount, 
                    'owner_consent' => $default_value, 
                    'terms_condition' => $agree_terms, 
                    'sin' => $sin_no, 
                    'annual_revenue' => $gross_amount
                ];

            $result = $this->assistedRuleEngineApiCall($rules, $data, $table_name, $dataArr);
            return $result;
        } catch (Exception $ex) {
            return Helpers::ajaxResponse(false, null, Helpers::getExceptionMessage($ex));
        }
    }
    
     /**
     * Make object of rule engine
     * 
     * @staticvar type $rule_engine_obj
     * @return mixed
     */
    public function ruleEngineObject() 
     {
        static $rule_engine_obj = null;

        if ($rule_engine_obj == null) {
            return $rule_engine_obj = new RuleEngineApi();
        }

        return $rule_engine_obj;
    }
    
    /**
     * Make auth for rule engine api
     * 
     * @param object $rules
     * @return mixed
     */
    public function makeAuthRuleEngine($rules)
    {
        try {
            if (!Session::has('rule_engine_expire')) {               
                $result = $rules->oauth();
            } else {
                $expire_time = Session::get('rule_engine_expire');
                $result = Session::get('rule_engine_data');
                $refresh_token = Session::get('refresh_token');
                $now = Carbon::now();
                $diff = ($expire_time < $now ) ? true : false;
                if ($diff === true) {
                    $result = $rules->oauth(true, $refresh_token);
                }
            }
            return $result->token_type . ' ' . $result->access_token;
        } catch (Exception $ex) {
             return Helpers::ajaxResponse(false, null, Helpers::getExceptionMessage($ex));
        }
    }
    
    /**
     * rule engine api calling
     * 
     * @param array $data
     */
    public function ruleEngineApiCall($rules, $data = [], $table_name, $dataArr) 
    {
        //table for owner dob on basis of province
        if (isset($data['onwer_age'])) {
            $table_name = config('ruleengine.OWNER_AGE_TABLE_ID');
            $dataArr = ['province' => $data['province'], 'age' => $data['onwer_age']];
        }

        //table for risk factor
        if (isset($data['risk_factor_calc']) && $data['risk_factor_calc'] === true) {
            $table_name = config('ruleengine.RISK_FACTOR_TABLE_ID');
            $dataArr = ['bankruptcy_score' => $data['bni_score'], 'fico' => $data['fico_score'], 'oldest_trade' => $data['age_of_trade']];
        }

        //table for equifax br
        if (isset($data['equifax_br_rate']) && $data['equifax_br_rate'] === true) {
            $table_name = config('ruleengine.EQUIFAX_BR_TABLE_ID');
            $dataArr = [
                'not_current' => $data['not_current'],
                'ret_cheques' => $data['ret_cheques'],
                'judgements' => $data['judgements'],
                'collection_amount' => $data['collection_amount'],
                'legal_suit' => $data['legal_suit'],
                'derog_count' => $data['derog_count'],
                'bankrupt' => $data['bankrupt'],
                'hit_strength' => $data['hit_strength']
            ];
        }

        //table for equifax pr
        if (isset($data['equifax_pr_rate']) && $data['equifax_pr_rate'] === true) {
            $table_name = config('ruleengine.EQUIFAX_PR_TABLE_ID');
            $dataArr = [
                'not_current' => $data['not_current'],
                'serious_derog' => $data['serious_derog'],
                'charge_off' => $data['charge_off'],
                'collection_filed' => $data['collection_filed'],
                'legal_suit' => $data['legal_suit'],
                'fraud' => $data['fraud'],
                'bankrupt' => $data['bankrupt'],
                'hit_strength' => $data['hit_strength'],
                'thin_file' => $data['thin_file'],
                'high_inquiries' => $data['high_inquiries']
            ];
        }

        //table for overall risk factor
        if (isset($data['overall_risk_factor']) && $data['overall_risk_factor'] === true) {
            $table_name = config('ruleengine.SYSTEM_RISK_FACTOR_TABLE_ID');
            $dataArr = [
                'equifax_pr' => $data['equifax_pr'],
                'equifax_br' => $data['equifax_br'],
                'segmentation' => $data['segmentation'],
                'dscr' => $data['dscr'],
                'nsf_three_months' => $data['nsf_three_months'],
                'nsf_twelve_months' => $data['nsf_twelve_months'],
                'months_in_biz' => $data['months_in_biz'],
            ];
        }

        //table for final decision
        if (isset($data['final_decision']) && $data['final_decision'] === true) {
            $table_name = config('ruleengine.DICISION_ENGINE_TABLE_ID');
            $dataArr = [
                'loc' => $data['line_of_credit'],
                'scoring_risk_factor' => $data['risk_factor'],
                'bank_statement' => $data['bank_stmt'],
                'loan_credits' => $data['loan_credits'],
                'legal_structure' => $data['legal_entity_id'],
                'dscr' => $data['existing_dscr'],
                'revenue_variance' => $data['revenue_variance'],
                'nsf_three_months' => $data['nsf_3_months'],
                'nsf_twelve_months' => $data['nsf_12_months'],
                'oth_inc_per_of_rev' => $data['other_income_percentage'],
                'equifax_pr_risk_factor' => $data['equifax_pr'],
                'equifax_br_risk_factor' => $data['equifax_br'],
                'widely_held' => $data['owner_count'],
                'iovation' => $data['iovation'],
                'legal_age_owners_perc' => $data['owner_percentage'],
                'esc_business' => $data['esc_business'],
                'primary_owner_esc' => $data['primary_owner_esc'],
                'limit_lt_lowlimit' => $data['limit_lt_lowlimit'],
                'hit_strength' => $data['hit_strength'],
                'months_in_biz' => $data['months_in_biz'],
                'consumer_alert' => $data['consumer_alert'],
                'is_missing_trans' => $data['is_missing_trans'],
                'operating_cost' => $data['operating_cost'],
                'sic_professional' => $data['sic_professional']
            ];
        }
        
        //table for 30k program
        if (isset($data['30k_programme']) && $data['30k_programme'] === true) {
            $table_name = config('ruleengine.30k_PROGRAMME_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['30k_programme']);
        }
        
        //table for 5k program
        if (isset($data['5k_programme']) && $data['5k_programme'] === true) {
            $table_name = config('ruleengine.5k_PROGRAMME_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['5k_programme']);
        }
        
        //table for 30k risk factor
        if (isset($data['30k_segmentation']) && $data['30k_segmentation'] === true) {
            $table_name = config('ruleengine.30k_SEGMENTATION_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['30k_segmentation']);
        }
        
        //table for 5k risk factor
        if (isset($data['5k_segmentation']) && $data['5k_segmentation'] === true) {
            $table_name = config('ruleengine.5k_SEGMENTATION_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['5k_segmentation']);
        }
        
        //table for overdraft risk factor
        if (isset($data['overdraft_segmentation']) && $data['overdraft_segmentation'] === true) {
            $table_name = config('ruleengine.OVERDRAFT_SEGMENTATION_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['overdraft_segmentation']);
        }
        
        //table for 30k system risk factor
        if (isset($data['30k_over_all_risk_factor']) && $data['30k_over_all_risk_factor'] === true) {
            $table_name = config('ruleengine.30k_SYSTEM_RISK_FACTOR_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['30k_over_all_risk_factor']);
        }
        
        //table for 5k system risk factor
        if (isset($data['5k_over_all_risk_factor']) && $data['5k_over_all_risk_factor'] === true) {
            $table_name = config('ruleengine.5k_SYSTEM_RISK_FACTOR_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['5k_over_all_risk_factor']);
        }

         //table for interest rate
        if (isset($data['interest_rate']) && $data['interest_rate'] === true) {
            $table_name = config('ruleengine.INTEREST_RATE_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['interest_rate']);
        }
        
        $decisionReason = [];
        //rule engine api call
        $credentials = base64_encode(config('ruleengine.CLIENT_ID').':'.config('ruleengine.CLIENT_SECRET'));
        $api_output = $rules->assistedDecisionsByTableId($credentials, config('ruleengine.APPLICATION_ID'), $table_name, $dataArr);
        $api_response = json_decode($api_output->getBody()->getContents());
        $response['status'] = isset($api_response->data->final_decision) ? $api_response->data->final_decision : 'Approve';
        if ((isset($data['final_decision']) && $data['final_decision'] == true) || (isset($data['risk_factor_calc']) && $data['risk_factor_calc'] == true) || (isset($data['equifax_br_rate']) && $data['equifax_br_rate'] == true) || (isset($data['equifax_pr_rate']) && $data['equifax_pr_rate'] == true) || (isset($data['30k_programme']) && $data['30k_programme'] === true) || (isset($data['5k_programme']) && $data['5k_programme'] === true) || (isset($data['30k_segmentation']) && $data['30k_segmentation'] === true) || (isset($data['5k_segmentation']) && $data['5k_segmentation'] === true) || (isset($data['overdraft_segmentation']) && $data['overdraft_segmentation'] === true)) {
            $result = json_decode($api_response->data->final_decision);
            $response['status'] = $result->decision;
            $response['code'] = isset($result->code) ? $result->code : null;
            $response['decision_desc'] = isset($result->text) ? $result->text : null;
            $response['all_matching_decision'] = isset($api_response->data->all_matching_decisions) ? $api_response->data->all_matching_decisions : [];
        }
        if (isset($response['status']) && $response['status'] == 'Decline') {
            $response['title'] = isset($api_response->data->title) ? $api_response->data->title : null;
        }
        return $response;
    }
    
    /**
     * assisted rule engine api calling
     * 
     * @param array $data
     */
    public function assistedRuleEngineApiCall($rules, $data = [], $table_name, $dataArr) 
    {
        //table for owner dob on basis of province
        if (isset($data['onwer_age'])) {
            $table_name = config('ruleengine.OWNER_AGE_TABLE_ID');
            $dataArr = ['province' => $data['province'], 'age' => $data['onwer_age']];
        }

        //table for risk factor
        if (isset($data['risk_factor_calc']) && $data['risk_factor_calc'] === true) {
            $table_name = config('ruleengine.RISK_FACTOR_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['risk_factor_calc']);
        }

        //table for equifax br
        if (isset($data['equifax_br_rate']) && $data['equifax_br_rate'] === true) {
            $table_name = config('ruleengine.EQUIFAX_BR_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['equifax_br_rate']);
        }

        //table for equifax pr
        if (isset($data['equifax_pr_rate']) && $data['equifax_pr_rate'] === true) {
            $table_name = config('ruleengine.EQUIFAX_PR_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['equifax_pr_rate']);
        }

        //table for overall risk factor
        if (isset($data['overall_risk_factor']) && $data['overall_risk_factor'] === true) {
            $table_name = config('ruleengine.ASSISTED_SYSTEM_RISK_FACTOR_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['overall_risk_factor']);
        }

        //table for final decision
        if (isset($data['final_decision']) && $data['final_decision'] === true) {
            $table_name = config('ruleengine.ASSISTED_DECISION_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['final_decision']);
        }
        
        //table for bank statement
        if (isset($data['bank_statement_rule']) && $data['bank_statement_rule'] === true) {
            $table_name = config('ruleengine.ASSISTED_BANK_STMT_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['bank_statement_rule']);
        }
        
         //table for interest rate
        if (isset($data['interest_rate']) && $data['interest_rate'] === true) {
            $table_name = config('ruleengine.INTEREST_RATE_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['interest_rate']);
        }
        
        //table for overdraft program
        if (isset($data['overdraft_program']) && $data['overdraft_program'] === true) {
            $table_name = config('ruleengine.OVERDRAFT_PROGRAMME_TABLE_ID');
            $dataArr = $data;
            unset($dataArr['overdraft_program']);
        }

        $decisionReason = [];
        //rule engine api call
        $credentials = base64_encode(config('ruleengine.CLIENT_ID').':'.config('ruleengine.CLIENT_SECRET'));
        $api_output = $rules->assistedDecisionsByTableId($credentials, config('ruleengine.APPLICATION_ID'), $table_name, $dataArr);
        $api_response = json_decode($api_output->getBody()->getContents());
        $response['status'] = isset($api_response->data->final_decision) ? $api_response->data->final_decision : 'Approve';
        if ((isset($data['final_decision']) && $data['final_decision'] == true) || (isset($data['risk_factor_calc']) && $data['risk_factor_calc'] == true) || (isset($data['bank_statement_rule']) && $data['bank_statement_rule'] == true) || (isset($data['equifax_br_rate']) && $data['equifax_br_rate'] == true) || (isset($data['equifax_pr_rate']) && $data['equifax_pr_rate'] == true) || (isset($data['overdraft_program']) && $data['overdraft_program'] === true)) {
            $result = json_decode($api_response->data->final_decision);
            $response['status'] = $result->decision;
            $response['code'] = isset($result->code) ? $result->code : null;
            $response['decision_desc'] = isset($result->text) ? $result->text : null;
            $response['all_matching_decision'] = isset($api_response->data->all_matching_decisions) ? $api_response->data->all_matching_decisions : [];
        }
        if (isset($response['status']) && $response['status'] == 'Decline') {
            $response['title'] = isset($api_response->data->title) ? $api_response->data->title : null;
        }
        return $response;
    }
}