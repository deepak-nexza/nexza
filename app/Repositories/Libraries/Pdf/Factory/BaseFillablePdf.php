<?php

namespace App\Libraries\Pdf\Factory;

use Helpers;
use Storage;
use Exception;
use App\Libraries\Pdftk\Pdftk;

abstract class BaseFillablePdf
{

    /**
     * PDFtk Library
     *
     * @var App\Libraries\Pdftk\Pdftk
     */
    protected $pdftk;

    /**
     * Save document Path
     *
     * @var type
     */
    protected $path;

    /**
     * Identifier to create single PDF or Multiple
     *
     * @var boolean
     */
    protected $mergePdfs = false;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->pdftk = new Pdftk;
        $this->path = storage_path('appDocuments') . DIRECTORY_SEPARATOR;
    }

    /**
     * Set merge pdf identifier
     *
     * @param boolean $value
     * @return __CLASS__
     */
    public function setMergeFlag($value = false)
    {
        $this->mergePdfs = $value;

        return $this;
    }

    /**
     * Abstract method for createPdfDocument
     */
    abstract public function createPdfDocument(array $pdfData, $template);

    /**
     * Creating Directory Structure
     *
     * @param integer $user_id
     * @param integer $application_id
     * @return boolean
     * @throws Exception
     */
    protected function createDirectoryStructure($user_id, $application_id)
    {
        try {
            Storage::makeDirectory($user_id . '/' . $application_id);
            return true;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Sets filename
     *
     * @param string $applicant_fname
     * @param string $applicant_lname
     * @param integer $app_id
     * @return string
     */
    protected function setFileName($applicant_fname, $applicant_lname, $app_id)
    {
        return Helpers::setPdfFilename(
            $applicant_fname,
            $applicant_lname,
            $app_id
        );
    }

    /**
     * Creates a single PDF
     *
     * @param integer $user_id
     * @param integer $application_id
     * @param string $template
     * @param array $data
     * @param string $applicant_name
     * @return array
     * @throws Exception
     */
    protected function createPdf($user_id, $application_id, $template, $data, $applicant_fname, $applicant_lname)
    {
        try {
            $outerRealPath = $this->path . $user_id;
            $innerFolderPath = $outerRealPath . DIRECTORY_SEPARATOR . $application_id;
            $fileName = static::setFileName($applicant_fname, $applicant_lname, $application_id);

            $result = $this->pdftk
                ->setOutputFileWithPath($innerFolderPath, $fileName)
                ->setPartnerPdfTemplate($template)
                ->process($data);
            
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * Merging PDF with data
     *
     * @param type $user_id
     * @param type $application_id
     * @param type $template
     * @param type $data
     * @param type $applicant_name
     */
    protected function mergePdfDocuments($user_id, $application_id, $template, $data, $applicant_name)
    {
        try {
            $outerRealPath = $this->path . $user_id;
            $innerFolderPath = $outerRealPath . DIRECTORY_SEPARATOR . $application_id;
            $result[0] = $this->pdftk
                ->setOutputFileWithPath($innerFolderPath . DIRECTORY_SEPARATOR, 'temp.pdf')
                ->setPartnerPdfTemplate($template)
                ->process($data);
            $pdf[] = $result[0]['output_file'];
            $finalOutput = $this->pdftk
                ->setJoinpdf(
                    $pdf,
                    $innerFolderPath .
                    DIRECTORY_SEPARATOR .
                    str_replace(' ', '-', $applicant_name) .
                    "-" .
                    $application_id .
                    '.pdf'
                );
            return $finalOutput;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    
     /**
     * Creates a single PDF For Aonly Talx Api
     *
     * @param integer $user_id
     * @param integer $application_id
     * @param string $template
     * @param array $data
     * @param string $applicant_name
     * @return array
     * @throws Exception
     */
    protected function createPdfForTalx($root_folder, $pdf_type, $user_id, $application_id, $template, $data, $applicant_fname, $applicant_lname)
    {
        try {
            $outerRealPath = storage_path().DIRECTORY_SEPARATOR.$root_folder;
            $innerFolderPath = $outerRealPath . DIRECTORY_SEPARATOR . $pdf_type;
            $fileName = static::setFileName($applicant_fname, $applicant_lname, $application_id);

            $result = $this->pdftk
                ->setOutputFileWithPath($innerFolderPath, $fileName)
                ->setPartnerPdfTemplate($template)
                ->process($data);
            
            return $result;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
