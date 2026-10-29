<?php
namespace Drupal\srl_payment\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\HttpFoundation\Request;
use \Dompdf\Dompdf;

class Agreementpdf {
    // public $filesystem;
    /**
     * {@inheritdoc}
     */
    // public function __construct( FileSystemInterface $filesystem) {
    //     $this->request = $request;
    //     $this->filesystem = $filesystem;
    // }
     /**
   * {@inheritdoc}
   */
    // public static function create(ContainerInterface $container) {
    //     return new static(
    //         $container->get('file_system')
    //     );
    // }
    /**
     * genrate enrollment agreement pdf.
     * 
    **/
    public function generatePdf($form_data) {
        require_once("vendor/autoload.php");
        $theme_logo = 'public://inline-images/HCG_logo_horiz-01.png';
        $university_policy = getUniversityPolicy();

        $agreement = \Drupal\taxonomy\Entity\Term::load($university_policy['enrollment_agreement']);
        $title_agreement_header = $agreement->getName();
        $enrolment_agreement = $agreement->getDescription();
        $address = $agreement->get('field_university_address')->getValue()[0]['value'];
                
        $univerisity =" 
        <table class='table' style='width: 100%;' cellpadding='10'>
            <tr>
                <td colspan='2' style='text-align: center;'>
                
                </td>
            </tr>
            <tr>
                <td>
                ".$address."
                    <h4 style='color: #000;margin-bottom: 1em;padding-bottom: .5em;border-bottom: 1px solid #ffc212;margin-top: 2em;
                    font-weight: 700;'>1. APPLICANT INFORMATION</h4>
                </td>
            </tr>
        </table>
        ";
        $end_date = date('M d, Y', strtotime("+5 months", strtotime($form_data['start_date'])));
        $start_date = date('M d, Y', strtotime($form_data['start_date']));

        $agreement = $enrolment_agreement;
        // $agreement = str_replace('#STUDENT_NAME#',$form_data['student_name'],$agreement);
        // $agreement = str_replace('#COURSE_NAME#',$form_data['course_name'],$agreement);
        // $agreement = str_replace('#FEE#',$form_data['total_amount'].' USD',$agreement);
        // $agreement = str_replace('#START_DATE#',$start_date,$agreement);
        // $agreement = str_replace('#END_DATE#',$end_date,$agreement);
        $agreement = str_replace('#STUDENT_NAME#','________________________',$agreement);
        $agreement = str_replace('#COURSE_NAME#','_______________________',$agreement);
        $agreement = str_replace('#FEE#','____________________',$agreement);
        $agreement = str_replace('#START_DATE#','__________________',$agreement);
        $agreement = str_replace('#END_DATE#','__________________',$agreement);
        // user information filled in form
        $user_info.='
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Status:</label>
            <span style="border-bottom: solid 1px #7d7d7d; margin:  5px 10px 5px 2px; line-height: 26px; ">&nbsp;&nbsp;New Student Applicant&nbsp;&nbsp;</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Name:</label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">_________________________</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Address: </label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">_________________________</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">City:</label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">_________________________</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;"> State: </label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">_________________________</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;"> Zip:</label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">_________________________</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Program Track: </label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">_________________________</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;"> Non-Degree Instructional Hours:</label> 
            <span style="border-bottom: solid 1px #7d7d7d;  margin:  5px 10px 5px 2px; line-height: 26px; ">&nbsp;&nbsp;280&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;"> Program Start Date:</label> 
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">___________________________</span><br />
        <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Expected Graduation Date: </label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">___________________________</span><br />';
        
        $user_info_sign.='
            <h4>Electronically or ink signed by:</h4>
            <h5>STUDENT:</h5>
            <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Student Signature: </label>
            <span style="margin:  5px 10px 5px 2px; line-height: 26px; ">________________________</span><br />

            <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Student Name Printed:</label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">___________________________</span><br />
            <label style="  margin-bottom: 5px; font-weight: bold; line-height: 26px;">Date: </label>
            <span style="  margin:  5px 10px 5px 2px; line-height: 26px; ">__________________________</span><br />
            
        ';
        $pdf_html = $univerisity;
        $pdf_html .= $user_info;
        $pdf_html .= $agreement;
        $pdf_html .= $user_info_sign;
        $ph_number = preg_replace('/\D/', '', $form_data['mobile_no']);
        

        $filename = 'EA_'.str_replace(' ','',$form_data['student_name']).'_'.str_replace(' ','',$form_data['course_name']).'_'.$ph_number.'_'.\Drupal::time()->getRequestTime();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdf_html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        $pdfoutput = $dompdf->output();
        $filepath = "sites/default/files/agreement_pdf/" . $filename.'.pdf';
        $fp = fopen($filepath, "w");
        fwrite($fp, $pdfoutput);
        fclose($fp);
    
        $url_redirect = \Drupal::request()->getSchemeAndHttpHost().'/'.$filepath;
        return $url_redirect;
    }
    /**Genrate term of use pdf */
    public function generateTermofUsePdf($form_data) {
        require_once("vendor/autoload.php");
        
        $university_policy = getUniversityPolicy();

        $agreement = \Drupal\taxonomy\Entity\Term::load($university_policy['term_of_use']);
        $title_agreement_header = $agreement->getName();
        $enrolment_agreement = $agreement->getDescription();
        $address = $agreement->get('field_university_address')->getValue()[0]['value'];
        
        
        $univerisity =" 
        <table class='table' style='width: 100%;' cellpadding='10'>
            <tr>
                <td colspan='2' style='text-align: center;'>
                
                </td>
            </tr>
            <tr>
                <td>
                    ".$address."
                </td>
            </tr>
        </table>
        ";

        $agreement = $enrolment_agreement;

        $pdf_html = $univerisity;
        $pdf_html .= $agreement;
        
        $filename = 'TermOfUse'.str_replace(' ','',$form_data['f_name']).'_'.str_replace(' ','',$form_data['course_name']).'_'.\Drupal::time()->getRequestTime();
        $dompdf = new Dompdf();
        $dompdf->loadHtml($pdf_html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        $pdfoutput = $dompdf->output();
        $filepath = "sites/default/files/agreement_pdf/" . $filename.'.pdf';
        $fp = fopen($filepath, "w");
        fwrite($fp, $pdfoutput);
        fclose($fp);
    
        $url_redirect = \Drupal::request()->getSchemeAndHttpHost().'/'.$filepath;
        return $url_redirect;
    }
}
