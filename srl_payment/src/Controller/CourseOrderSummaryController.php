<?php

namespace Drupal\srl_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CourseOrderSummaryController controller class.
 */
class CourseOrderSummaryController extends ControllerBase
{


  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function courseordersummary($page = NULL, $id = NULL)
  {
    $session = \Drupal::request()->getSession();
    $transaction_id = $session->get('transaction_id');
    if(!empty($transaction_id)){
      $domain = \Drupal::service('domain.negotiator')->getActiveId();
      $connection = \Drupal::service('database');
      $query = $connection->update('srl_candidate_test_details')
        ->fields([
          'steps_level' => 3
        ])
        ->condition('transaction_id',$transaction_id, '=')
        ->execute();
      $query = db_select('srl_candidate_test_details','records');
      $query->fields('records',array('transaction_id','course_id','name', 'email_id','mobile_no','course_code','university_code',
      'country_code','center_code','json_response','batch_start_date'));
      $query->condition('records.transaction_id',$transaction_id);
      $field_values = $query->execute()->fetchAll();

      $name = $field_values['0']->name;
      $course_id = $field_values['0']->course_id;
      $course_code = $field_values['0']->course_code;
      $email_id = $field_values['0']->email_id;
      $mobile_no = $field_values['0']->mobile_no;
      $university_code = $field_values['0']->university_code;
      $center_code = $field_values['0']->center_code;
      $country_code = $field_values['0']->country_code;
      $batch_start_date = $field_values['0']->batch_start_date;
      
      if(!$batch_start_date){
        $batch_start_date = getCourseBatchDate($course_id,$domain);
        $connection = \Drupal::service('database');
        $query = $connection->update('srl_candidate_test_details')
          ->fields([
            'batch_start_date' => $batch_start_date,
          ])
          ->condition('transaction_id',$transaction_id, '=')
          ->execute();
      }

      $jsonData = stripslashes(html_entity_decode($field_values['0']->json_response));
      $student_detail = json_decode($jsonData,true);
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node_details = $node_storage->load($course_id);
      $course_name = $node_details->getTitle();
      
      // $taxo = db_select('taxonomy_term__field_center_code','center'); 
      // $taxo->condition('center.field_center_code_value',$center_code);
      // $taxo->leftjoin('taxonomy_term_field_data', 'fd', 'fd.tid = center.entity_id');
      // $taxo->fields('fd',array('name','tid'));
      // $taxonomy = $taxo->execute()->fetchAll();
      // if(!empty($taxonomy)){
      //   $university_name = $taxonomy['0']->name;
      //   $university_id = $taxonomy['0']->tid;
      // }
            
      $database = \Drupal::database();
      $course = $database->select('node__field_course_code_reference', 'ccr');
      $course->leftjoin('node__field_domain_access', 'domain', 'ccr.entity_id = domain.entity_id');
      $course->leftjoin('node__field_actualfee', 'actualfee', 'ccr.entity_id = actualfee.entity_id');
      $course->leftjoin('node__field_fee', 'fee', 'fee.entity_id = ccr.entity_id');
      $course->leftjoin('node__field_currency', 'fc', 'fc.entity_id = ccr.entity_id');
      $course->leftjoin('node__field_delivery_modal', 'dm', 'dm.entity_id = ccr.entity_id');
      
      $course->fields('fee', ['field_fee_value']);
      $course->fields('fc', ['field_currency_target_id']);
      $course->fields('dm', ['field_delivery_modal_value']);
      $course->fields('actualfee', ['field_actualfee_value']);

      $course->condition('ccr.field_course_code_reference_target_id',$course_id);
      $course->condition('ccr.bundle','course_university_mapping');
      $course->condition('domain.field_domain_access_target_id',$domain);

      $course_detail = $course->execute()->fetchAll();
      if(!empty($course_detail)){
        $course_fee = $course_detail['0']->field_fee_value;
        $currency_id = $course_detail['0']->field_currency_target_id;
        $delivery_modal = $course_detail['0']->field_delivery_modal_value;
		    $actual_fee = $course_detail['0']->field_actualfee_value;
      }

      if(!empty($currency_id)){
        $currency_code = \Drupal\taxonomy\Entity\Term::load($currency_id)->get('name')->value;
      }else{
        $currency_code = 'USD';
      }
      //$taxes = $course_fee/10;
      $taxes = '0';
      //$total_amount = $course_fee + $taxes;
	    $total_amount = $actual_fee + $taxes;

      $session = \Drupal::request()->getSession();
      $transaction_id = $session->get('transaction_id');
      
      $payeezyform = \Drupal::formBuilder()->getForm('\Drupal\srl_payment\Form\PayeezyPaymentForm',$total_amount,$currency_code,$transaction_id,$course_id,$course_fee,$batch_start_date,$course_code, $delivery_modal);

      $cohort_date = \Drupal::formBuilder()->getForm('\Drupal\srl_payment\Form\PayeezyPaymentCohortDateForm',$course_id,$transaction_id,$batch_start_date);
      $payment = $database->select('payeezy_payment_details', 'detail');
      $payment->fields('detail', ['transaction_id']);
      $payment->condition('detail.transaction_id',$transaction_id);
      $payment_detail = $payment->execute()->fetchAll();

      if(count($payment_detail) == 0){
        db_insert('payeezy_payment_details')
          ->fields(
            [
              'transaction_id' => $transaction_id,
              'user_name' => $name,
              'email_id' => $email_id,
              'mobile_no' => $mobile_no,
              'course_code' => $course_id,
              'university' => $domain,
              'created_on' => \Drupal::time()->getRequestTime(),
              'course_name' => $course_name,
              'delivery_mode' => 'online',
              'currency_code' => $currency_code,
              'cohort_date' => $batch_start_date,
              'base_amount' => $actual_fee,
              'tax_amount' => $taxes,
              'total_amount' => $total_amount,
              'zip_code' => $student_detail['zip'] 
            ]
        )->execute();
      }
      /**proced to payment */
      // $leadData['email'] = $email_id;
      // $leadData['status'] = PROCED_TO_PAYMENT;
      // $leadData['status_level'] = 3; 
      // $HubspotAPI = \Drupal::service('api.hubspot');
      // $response = $HubspotAPI->updateLeadStatus($leadData);
      $str = explode('-',$mobile_no);
      $mobile_no = (int) filter_var($str[0], FILTER_SANITIZE_NUMBER_INT).'-'.$str[1];


      return [
        '#theme' => 'course_order_summary',
        '#payeezy_form' => $payeezyform,
        '#cohort_date' => $cohort_date,
        '#batch_start_date' => $batch_start_date,
        '#course_code' => $course_code,
        '#name' => $name,
        '#email_id' => $email_id,
        '#mobile_no' => '+'.$mobile_no,
        '#course_name' => $course_name,
        '#course_fee' => $course_fee,
		    '#actual_fee' =>  number_format($actual_fee, 0, '.', ','),
        '#currency_code' => $currency_code,
        '#payable_amount' => number_format($total_amount, 0, '.', ','),
        '#taxes' => $taxes,
        '#cache' => ['max-age' => 0],
        '#attached' => [
          'library' => [
            'srl_payment/srl_payment',
          ],
        ],
      ];

    }else{
      $output = '<h2>Transaction Id is not available !</h2>';
      return [
        '#type' => 'markup',
        '#markup' => $output,
      ];
    }
  }
  public function createTermOFUsePDF($form_values){
    $HubspotAPI = \Drupal::service('pdf.agreement');
    $term_of_use_pdf = $HubspotAPI->generateTermofUsePdf($form_values);
    $leadData['term_of_use_pdf'] = $term_of_use_pdf;
  }
}

