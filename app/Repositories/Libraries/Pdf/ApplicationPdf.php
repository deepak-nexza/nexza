<?php

namespace App\Libraries\Pdf;

use PDF;
use Exception;
use Illuminate\Support\Facades\File;
use Storage;

class ApplicationPdf
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
            PDF::SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

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
    
    /**
     * Create finance pdf
     *
     * @param array $pages
     * @param string $filename
     * @param string $dest
     * @return mixed
     * @throws Exception
     */
    public function createPdf($pages = [], $filename, $pdf_purpose, $dest = 'D', $header = [], $footer = [])
    {
       try {
            if (!in_array($dest, $this->dest)) {
                $dest = $this->dest[0];
            }
            $header_bottom_margin =2;
            $footer_bottom_margin =8;
            // Custom Header
             PDF::setHeaderCallback(function ($pdf) use ($header) {
                $title ='';
                $font  =7;
                $weight='';
                $title_position='L';
                $logo  ='';
                $logo_position='R';
                $logo_type ='';
                $logo_width ='0';
                $logo_hight ='0%';
                $logo_x ='0';
                $logo_y ='0';
                $header_margin ='0';
                if (isset($header['title']) && $header['title']!='') {
                    $title =$header['title'];
                }
                if (isset($header['title_font']) && $header['title_font']!='') {
                    $font =$header['title_font'];
                }
                if (isset($header['title_font_weight']) && $header['title_font_weight']!='') {
                    $weight =$header['title_font_weight'];
                }
                if (isset($header['title_position']) && $header['title_position']!='') {
                    $title_position =$header['title_position'];
                }
                // Logo
                if (isset($header['logo']) && $header['logo']!='') {
                    $logo = $header['logo'];
                }
                if (isset($header['logo_type']) && $header['logo_type']!='') {
                    $logo_type = $header['logo_type'];
                }
                if (isset($header['logo_position']) && $header['logo_position']!='') {
                    $logo_position =$header['logo_position'];
                }
                if (isset($header['logo_hight']) && $header['logo_hight']!='') {
                    $logo_hight =$header['logo_hight'];
                }
                if (isset($header['logo_width']) && $header['logo_width']!='') {
                    $logo_width =$header['logo_width'];
                }
                if (isset($header['logo_y']) && $header['logo_y']!='') {
                    $logo_y =$header['logo_y'];
                }
                if (isset($header['logo_x']) && $header['logo_x']!='') {
                    $logo_x =$header['logo_x'];
                }
                if (isset($header['header_bottom_margin']) && $header['header_bottom_margin']!='') {
                    $header_bottom_margin =$header['header_bottom_margin'];
                }
                $pdf->SetFont('freesans', $weight, $font);
                if (!empty($title)) {
                    $pdf->Cell(0, 15, $title, 0, 1, $title_position, 0, '', 0, false, 'M', 'M');
                }
                if (!empty($logo)) {
                    $w = $pdf->pixelsToUnits($logo_width);
                    $h = $pdf->pixelsToUnits($logo_hight);
                    $pdf->Image($logo, $logo_x, $logo_y, $w, $h, $logo_type, '', 'T', false, 250, $logo_position, false, false, 0, false, false, false);
                }

                if (isset($header['header_html']) && $header['header_html']!='') {
                    $pdf->writeHTMLCell($w = 0, $h = 0, $x = '9', $y = '3', $header['header_html'], $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = 'top', $autopadding = true);
                }
             });
            // Custom Footer
            PDF::setFooterCallback(function ($pdf) use ($footer) {
                $title ='';
                $font  =7;
                $weight='';
                if (isset($footer['title']) && $footer['title']!='') {
                    $title = $footer['title'];
                }
                if (isset($footer['title_font']) && $footer['title_font']!='') {
                    $font = $footer['title_font'];
                }
                if (isset($footer['title_font_weight']) && $footer['title_font_weight']!='') {
                    $weight = $footer['title_font_weight'];
                }
                if (isset($footer['footer_bottom_margin']) && $footer['footer_bottom_margin']!='') {
                    $footer_bottom_margin =$footer['footer_bottom_margin'];
                }
                $pdf->SetFont('freesans', $weight, $font);
                $page_no ='Page '.$pdf->getAliasNumPage().' of '.$pdf->getAliasNbPages();
                $pdf->writeHTMLCell(0, 0, '', '', $title, 0, 0, false, "L", true);
                //$pdf->Cell(12, 16, $page_no, 0, 0, 'R', 0, '');
                $pdf->Cell(12, 16, '' , 0, 0, 'R', 0, '');
            });
            
           
            // set document information
            PDF::SetCreator('');
            PDF::SetAuthor('');
            PDF::SetTitle('');
            PDF::SetSubject('');
            PDF::SetKeywords('');

            // set header and footer fonts
            PDF::setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            PDF::setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            PDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // set margins
            PDF::SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
            PDF::SetHeaderMargin(10);
            PDF::SetFooterMargin(20);
            // set auto page breaks
            PDF::SetAutoPageBreak(true, 20);
            // set image scale factor
            PDF::setImageScale(PDF_IMAGE_SCALE_RATIO);
            // set font
            PDF::SetFont('freesans', '', 10);
            PDF::SetMargins(2, $header_bottom_margin, 16, 7);
            // text to display
            foreach ($pages as $page) {
                PDF::AddPage();
                PDF::writeHTML($page, true, false, true, false, '');
            }
 
            // reset pointer to the last page
            PDF::lastPage();
            if ($dest=='F' || $dest=='f') {
                $temp_filename = '/tmp/'.$filename.'.pdf';
                PDF::Output($temp_filename, $dest);
                $pdf_data = $temp_filename;
            } else {
                $pdf_data = PDF::Output(''.str_replace(' ', '_', $filename).'.pdf', $dest);
            }
            PDF::reset();
            return $pdf_data;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
}
