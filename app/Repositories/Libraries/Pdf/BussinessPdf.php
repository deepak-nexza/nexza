<?php

namespace App\Libraries\Pdf;

use PDF;
use Exception;
use Illuminate\Support\Facades\File;
use Storage;

class BussinessPdf
{

    /**
     * Destination where to send the document.
     *
     * I: send the file inline to the browser (default). The plug-in is used if available.
     *   The name given by name is used when one selects the "Save as" option on the link
     *   generating the PDF.
     * D: send to the browser and force a file download with the name given by name.
     * F: save to a local server file with the name given by name.
     * S: return the document as a string (name is ignored).
     * FI: equivalent to F + I option
     * FD: equivalent to F + D option
     * E: return the document as base64 mime multi-part email attachment (RFC 2045)

     * @var array
     */
    protected $dest = [
        'D', 'I', 'F', 'S', 'FI', 'FD', 'E'
    ];

    /**
     * Write html to Pdf document
     *
     * @param array $pages
     * @param string $filename
     * @param string $dest
     * @return mixed
     * @throws Exception
     */
    public function createPdfDocuments(array $pages, $filename, $pdf_purpose, $dest = 'D')
    {
        try {
            if (!in_array($dest, $this->dest)) {
                $dest = $this->dest[0];
            }
            
            // set document information
            PDF::reset();
            PDF::SetCreator('');
            PDF::SetAuthor('');
            PDF::SetTitle('Application Form');
            PDF::SetSubject('');
            PDF::SetKeywords('');
            
            // set header and footer fonts
            PDF::setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            PDF::setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
            
            // set default monospaced font
            PDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            
            // set margins
            PDF::SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
            PDF::SetHeaderMargin(0);
            PDF::SetFooterMargin(0);
            
            // set auto page breaks
            PDF::SetAutoPageBreak(true, 5);
            
            // set image scale factor
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            // set font
            PDF::SetFont('freesans', '', 10);

            // text to display
            foreach ($pages as $page) {
                // add a page
                PDF::AddPage();

                PDF::writeHTML($page, true, false, true, false, '');
            }

            // reset pointer to the last page
            PDF::lastPage();
            
            if ($pdf_purpose == "AdditionalInfo") {
                //Code added for file   
                $filePath =  DIRECTORY_SEPARATOR . '' . str_replace(' ', '_', $filename) . '.pdf';
                $pdf_data   = PDF::Output($filePath, 'I');
            } else {
                if($dest == 'F'){
                    $filePath = '/tmp/'.$filename.'.pdf';
                    $pdf_data   = PDF::Output($filePath, 'F');
                }
                else{
                    $pdf_data = PDF::Output('' . str_replace(' ', '_', $filename) . '.pdf', $dest);
                }
            }

                return $pdf_data;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
