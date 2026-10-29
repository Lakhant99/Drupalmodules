<?php

namespace Drupal\srl_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\HttpFoundation\Request;
use \Dompdf\Dompdf;

class SrlPaymentController extends ControllerBase{


    public $filesystem;
    /**
     * {@inheritdoc}
     */
    public function __construct( FileSystemInterface $filesystem) {
        $this->request = $request;
        $this->filesystem = $filesystem;
    }
     /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('file_system')
        );
    }
    public function controller(){
        // require_once("vendor/autoload.php");
        $output = '';
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = 'testlevel@test.com';

        $HubspotAPI = \Drupal::service('api.hubspot');
        $lead_status = $HubspotAPI->GetContactID($data);
        // kint($lead_status);
            return array(
                '#type' => 'markup',
                '#markup' => t($output),
            );
        
    }
    public function generatePdf() {
        require_once("vendor/autoload.php");
        $output ='';
        
        $path = $this->filesystem->realpath('public://agreement_pdf2');
        $file_path = "public://agreement_pdf2/test.pdf";
        if (!$this->filesystem->prepareDirectory($file_path)) {
            $this->filesystem->mkdir($path);
        }
        $filename = 'default-pdf';
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        $pdfoutput = $dompdf->output();
        $filepath = "sites/default/files/agreement_pdf2/" . $filename.'.pdf';
        $fp = fopen($filepath, "w");
        fwrite($fp, $pdfoutput);
        fclose($fp);
    
        $url_redirect = file_create_url($filepath);
        return $url_redirect;
    }
}