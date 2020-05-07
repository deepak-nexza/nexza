<?php namespace App\Libraries\Pdf;
use App\Libraries\Pdftk\Pdftk;
use Storage;
use Auth;
/**
 * Class to execute PDFTK command line to generate PDF from a fillable
 * template. The tool PDFTK must be installed in the machine.
 *
 * @author test
 */
class Pdf
{
    protected $pdftk;

    public function __construct()
    {
        $this->pdftk = new Pdftk;
        $this->path = storage_path() . DIRECTORY_SEPARATOR . 'appDocuments' . DIRECTORY_SEPARATOR;
    }

    public function createPdfDocuments($pdfData, $template)
    {
        $data['company_name'] = $pdfData['appDetail']['com_name'];
        $data['doing_business'] = $pdfData['appDetail']['com_dba_name'];
        $data['company_type'] = \Helpers::getIndustryName((int) $pdfData['appDetail']['com_org_id']);
        $data['company_type_sub'] = \Helpers::getSubIndustryName((int) $pdfData['appDetail']['com_sub_org_id']);
        $data['phone_number'] = $pdfData['appDetail']['com_phone_no'];
        $data['inception_date'] = $pdfData['appDetail']['com_inception_date'];
        $data['legal_entity'] = (($pdfData['appDetail']['com_legal_structure'] == 1) ? trans('form.sole_prop') : (($pdfData['appDetail']['com_legal_structure'] == 2) ? trans('form.corporation') : ($pdfData['appDetail']['com_legal_structure'] == 3 ? trans('form.partnership') : ($pdfData['appDetail']['com_legal_structure'] == 4 ? trans('form.non_profit_corp') : ($pdfData['appDetail']['com_legal_structure'] == 5 ? trans('form.limited_liab') : ($pdfData['appDetail']['com_legal_structure'] ? trans('form.limit_part') : ''))))));
        $data['state_of_incorportion'] = ($pdfData['appDetail']['com_state_of_inc'] == 0) ? '' : \Helpers::getStateName((int) $pdfData['appDetail']['com_state_of_inc']);
        $data['tax_id'] = $pdfData['appDetail']['com_tax_id'];
        $data['business_home_based'] = (($pdfData['appDetail']['is_com_home_based'] == 1) ? trans('form.yes') : (($pdfData['appDetail']['is_com_home_based'] == 2) ? trans('form.no') : ''));
        $data['rent_own'] = (($pdfData['appDetail']['com_property_type'] == 1) ? trans('form.owned') : (($pdfData['appDetail']['com_property_type'] == 2) ? trans('form.leased') : ''));
        $data['landlord_name'] = $pdfData['appDetail']['com_landloard_name'];
        $data['landlord_phone'] = $pdfData['appDetail']['com_landloard_phone'];
        $data['owner_ever_been_convicted'] = (($pdfData['appDetail']['com_is_felony'] == 1) ? trans('form.yes') : (($pdfData['appDetail']['com_is_felony'] == 2) ? trans('form.no') : ''));
        $data['business_status'] = (($pdfData['appDetail']['com_business_status'] == 1) ? 'Pending Review' : (($pdfData['appDetail']['com_business_status'] == 2) ? 'Needs More Information' : ($pdfData['appDetail']['com_business_status'] == 3 ? 'Not Yet Filled Out' : ($pdfData['appDetail']['com_business_status'] == 4 ? 'Approved' : ''))));
        $data['business_notes'] = $pdfData['appDetail']['com_business_note'];
        $data['physical_address'] = $pdfData['appDetail']['com_addr'];
        $data['physical_address_city'] = ($pdfData['appDetail']['com_city_id'] == 0) ? '' : \Helpers::getCityName((int) $pdfData['appDetail']['com_city_id']);
        $data['physical_address_state'] = ($pdfData['appDetail']['com_state_id'] == 0) ? '' : \Helpers::getStateName((int) $pdfData['appDetail']['com_state_id']);
        $data['physical_address_zipcode'] = $pdfData['appDetail']['com_zipcode'];
        $data['billing_physical_address'] = $pdfData['appDetail']['com_billing_addr'];
        $data['billing_address_city'] = ($pdfData['appDetail']['com_billing_city_id'] == 0) ? '' : \Helpers::getCityName((int) $pdfData['appDetail']['com_billing_city_id']);
        $data['billing_address_state'] = ($pdfData['appDetail']['com_billing_state_id'] == 0) ? '' : \Helpers::getStateName((int) $pdfData['appDetail']['com_billing_state_id']);
        $data['billing_address_zipcode'] = $pdfData['appDetail']['com_billing_zipcode'];
        $k = 1;
        $additionalOwnerTpl1 = '';
        $additionalOwnerTpl2 = '';
        $j = 0;
        //$Creditconsent1 = '';
        //  $Creditconsent2 = '';
        $Creditconsent = '';
        $countOwner = count($pdfData['owner']);
        if (count($pdfData['owner']) > 0) {
            foreach ($pdfData['owner'] as $ownerPdf) {
                $data['owner' . $k . '_firstname'] = ucfirst($ownerPdf['first_name']);
                $data['owner' . $k . '_lastname'] = ucfirst($ownerPdf['last_name']);
                $data['owner' . $k . '_email'] = $ownerPdf['email'];
                $data['owner' . $k . '_officer_title'] = ($ownerPdf['job_title'] == 0) ? '' : \Helpers::getJobTitle((int) $ownerPdf['job_title']);
                $data['owner' . $k . '_homephone'] = $ownerPdf['phone_home'];
                $data['owner' . $k . '_cellphone'] = $ownerPdf['phone_cell'];
                $data['owner' . $k . '_dob'] = $ownerPdf['dob'];
                $data['owner' . $k . '_physical_address'] = $ownerPdf['address'];
                $data['owner' . $k . '_city'] = ($ownerPdf['c_addr_cityid'] == 0) ? '' : \Helpers::getCityName((int) $ownerPdf['c_addr_cityid']);
                $data['owner' . $k . '_state'] = ($ownerPdf['c_addr_state_id'] == 0) ? '' : \Helpers::getStateName((int) $ownerPdf['c_addr_state_id']);
                $data['owner' . $k . '_zipcode'] = $ownerPdf['owner_zipcode'];
                $data['owner' . $k . '_social_security'] = $ownerPdf['ssn'];
                $data['owner' . $k . '_drivers_licence'] = $ownerPdf['dl'];
                $data['owner' . $k . '_ownership_percentage'] = $ownerPdf['ownership_percent'] == 0 ? '' : $ownerPdf['ownership_percent'];
                $data['owner' . $k . '_ownerstatus'] = (($ownerPdf['owner_status'] == 1) ? 'Pending Review' : (($ownerPdf['owner_status'] == 2) ? 'Needs More Information' : ($ownerPdf['owner_status'] == 3 ? 'Not Yet Filled Out' : ($ownerPdf['owner_status'] == 4 ? 'Approved' : ''))));
                $data['owner' . $k . '_ownernotes'] = $ownerPdf['owner_notes'];

                if ($j === 2) {
                    $additionalOwnerTpl1 = 'additionalowner1';
                }
                if ($j === 4) {
                    $additionalOwnerTpl2 = 'additionalowner2';
                }

                $k++;
                $j++;
            }
        }
        if ($countOwner > 0 && $countOwner <= 2) {
            $Creditconsent = 'Creditconsent1';
        } elseif ($countOwner > 2 && $countOwner <= 4) {
            $Creditconsent = 'Creditconsent2';
        } elseif ($countOwner > 4) {
            $Creditconsent = 'Creditconsent3';
        }

        $data['gross_annual_sales'] = !empty($pdfData['appDetail']['annual_sale_amt']) ? "$" . number_format($pdfData['appDetail']['annual_sale_amt']) : null;
        $data['monthly_deposits'] = !empty($pdfData['appDetail']['monthly_deposit']) ? "$" . number_format($pdfData['appDetail']['monthly_deposit']) : null;
        $data['average_bank_balance'] = !empty($pdfData['appDetail']['avg_bank_balance']) ? "$" . number_format($pdfData['appDetail']['avg_bank_balance']) : null;
        $data['monthly_creditcard_volume'] = !empty($pdfData['appDetail']['monthly_cc_volume']) ? "$" . number_format($pdfData['appDetail']['monthly_cc_volume']) : null;
        $data['credit_card_processor'] = ($pdfData['appDetail']['cc_processor_id'] == 0) ? '' : \Helpers::getCcProcessorName((int) $pdfData['appDetail']['cc_processor_id']);
        $data['accepted_credit'] = !empty($pdfData['appDetail']['accepted_credit_amt']) ? "$" . number_format($pdfData['appDetail']['accepted_credit_amt']) : null;
        $data['separate_business'] = $pdfData['appDetail']['seprate_business_bank_account'];
        $data['creditcard_processing_volume1'] = !empty($pdfData['appDetail']['cc_last_month_amt']) ? "$" . number_format($pdfData['appDetail']['cc_last_month_amt']) : null;
        $data['creditcard_processing_tickets1'] = $pdfData['appDetail']['cc_last_month_ticket'];
        $data['creditcard_processing_volume2'] = !empty($pdfData['appDetail']['cc_1month_amt']) ? "$" . number_format($pdfData['appDetail']['cc_1month_amt']) : null;
        $data['creditcard_processing_tickets2'] = $pdfData['appDetail']['cc_1month_ticket'];
        $data['creditcard_processing_volume3'] = !empty($pdfData['appDetail']['cc_2month_amt']) ? "$" . number_format($pdfData['appDetail']['cc_2month_amt']) : null;
        $data['creditcard_processing_tickets3'] = $pdfData['appDetail']['cc_2month_ticket'];
        $data['creditcard_processing_volume4'] = !empty($pdfData['appDetail']['cc_3month_amt']) ? "$" . number_format($pdfData['appDetail']['cc_3month_amt']) : null;
        $data['creditcard_processing_tickets4'] = $pdfData['appDetail']['cc_3month_ticket'];
        $logedin_firstname = $pdfData['appDetail']['logedinUsername'];
        $logedin_lastname = $pdfData['appDetail']['logedinLastname'];


        $result = $this->createDirectoryStructure($pdfData['appDetail']['user_id'], $pdfData['appDetail']['application_id'], $template, $additionalOwnerTpl1, $additionalOwnerTpl2, $Creditconsent, $data, $logedin_firstname, $logedin_lastname);
    }

    public function createDirectoryStructure($user_id, $application_id, $template, $additionalOwnerTpl1, $additionalOwnerTpl2, $Creditconsent, $data, $logedin_firstname, $logedin_lastname)
    {
        Storage::makeDirectory($user_id . '/' . $application_id);
        //chmod($this->path.$user_id, 0777);
        $this->mergePdfDocuments($user_id, $application_id, $template, $additionalOwnerTpl1, $additionalOwnerTpl2, $Creditconsent, $data, $logedin_firstname, $logedin_lastname);
    }

    public function mergePdfDocuments($user_id, $application_id, $template, $additionalOwnerTpl1, $additionalOwnerTpl2, $Creditconsent, $data, $logedin_firstname, $logedin_lastname)
    {
        $outerRealPath = $this->path . $user_id;
        $innerFolderPath = $outerRealPath . DIRECTORY_SEPARATOR . $application_id;

        $result[0] = $this->pdftk
            ->setOutputFileWithPath($innerFolderPath . DIRECTORY_SEPARATOR, 'temp.pdf')
            ->setPartnerPdfTemplate($template)
            ->process($data);
        $pdf[] = $result[0]['output_file'];


        /** $result[1] = $this->pdftk
          ->setOutputFileWithPath($innerFolderPath . DIRECTORY_SEPARATOR, 'temp1.pdf')
          ->setPartnerPdfTemplate($Creditconsent)
          ->process($data);
          $pdf[] = $result[1]['output_file'];

         *
         */
        if ($additionalOwnerTpl1 != '') {
            $result[1] = $this->pdftk
                ->setOutputFileWithPath($innerFolderPath . DIRECTORY_SEPARATOR, 'temp1.pdf')
                ->setPartnerPdfTemplate($additionalOwnerTpl1)
                ->process($data);
            $pdf[] = $result[1]['output_file'];
        }

        if ($additionalOwnerTpl2 != '') {
            $result[2] = $this->pdftk
                ->setOutputFileWithPath($innerFolderPath . DIRECTORY_SEPARATOR, 'temp2.pdf')
                ->setPartnerPdfTemplate($additionalOwnerTpl2)
                ->process($data);
            $pdf[] = $result[2]['output_file'];
        }
        if ($Creditconsent != '') {
            $result[3] = $this->pdftk
                ->setOutputFileWithPath($innerFolderPath . DIRECTORY_SEPARATOR, 'temp3.pdf')
                ->setPartnerPdfTemplate($Creditconsent)
                ->process($data);
            $pdf[] = $result[3]['output_file'];
        }

        $finalOutput = $this->pdftk
            ->setJoinpdf($pdf, $innerFolderPath . DIRECTORY_SEPARATOR . $logedin_firstname . '-' . $logedin_lastname . "-" . $application_id . '.pdf');
    }
}