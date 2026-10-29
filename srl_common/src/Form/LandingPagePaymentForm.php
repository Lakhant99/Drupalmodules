<?php

namespace Drupal\srl_common\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\taxonomy\Entity\Term;
use Drupal\Node\Entity\Node;
/**
 * Show textfields based on Payeezy payment.
 *
 * @ingroup srl_payment
 */
class LandingPagePaymentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'landing_page_payment_form';
  }

  /**
   * {@inheritdoc}
   *
   * For all the requests this class gets, the buildForm() method will always be
   * called. If an AJAX request comes in, the form state will be set to the
   * state the user changed that caused the AJAX request. So if the user enabled
   * one of our checkboxes, it will be checked in $form_state.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $payment_variable = NULL) {
    $form['site_name'] = \Drupal::config('system.site')->get('name');
    $domains = \Drupal::service('domain.negotiator')->getActiveId();
    $API_Config = \Drupal::config('universityapi_paymentlink.config');
    $university_config = \Drupal::config('university_info.config');
    $form['chatbot_link'] = $university_config->get($domains . '_chatbot_link');
    $form['schedule_call_with_financing'] = $university_config->get($domains . '_schedule_call_with_financing');
    $domain = $payment_variable['domain'];
    $transaction_id = $payment_variable['transaction_id'];
    $course_id = $payment_variable['course_id'];
    $discount_fee_amount = $payment_variable['discount_fee_amount'];
    $course_code = $payment_variable['course_code'];
    $result = $payment_variable['result'];
    
    $application_values = json_decode(stripcslashes($result->json_response));
    
    $university_id = NULL;
    $results = \Drupal::entityQuery('node')
      ->condition('type', 'course_university_mapping')
      ->condition('field_domain_access.target_id', $domain)
      ->condition('field_course_code_reference.target_id',$course_id,'=')
      ->execute();
      foreach($results as $value){
        $university_id = $value;
      }
      


    
    
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node_course = $node_storage->load($course_id);
    
    $form['transaction_id'] = [
      '#type' => 'hidden',
      '#default_value' => $transaction_id,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['email'] = [
      '#type' => 'hidden',
      '#default_value' => $application_values->email,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];
    // print_r($application_values); 
    // die();
    $form['zip_code'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Email'),
      '#default_value' => $application_values->zip,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];
    $form['course_name'] = [
      '#type' => 'hidden',
      '#default_value' => $node_course->getTitle(),
      '#maxlength' => 500,
    ];
    $form['course_id'] = [
      '#type' => 'hidden',
      '#default_value' => $course_id,
    ];
    $form['student_name'] = [
      '#type' => 'hidden',
      '#default_value' => $application_values->full_name.' '.$application_values->last_name,
      '#maxlength' => 50,
    ];
    $form['student_fname'] = [
      '#type' => 'hidden',
      '#default_value' => $application_values->full_name,
      '#maxlength' => 50,
    ];
    $form['student_lname'] = [
      '#type' => 'hidden',
      '#default_value' => $application_values->last_name,
      '#maxlength' => 50,
    ];
    $form['mobilephone'] = [
      '#type' => 'hidden',
      '#default_value' => $application_values->phone,
      '#maxlength' => 50,
    ];
    $str_phone = strval($application_values->phone);
    $phone_edit = preg_replace("/[^0-9]/", '', $str_phone);
    $phone_edit = substr($phone_edit,0,10);
    $phone_format = substr($phone_edit,0,3).'-'.substr($phone_edit,3,3).'-'.substr($phone_edit,6);
    
    $form['mobilephone_format'] = [
      '#type' => 'hidden',
      '#default_value' => $phone_format,
      '#maxlength' => 50,
    ];
    $form['university_id'] = [
      '#type' => 'hidden',
      '#default_value' => $university_id,
    ];
    
    $form['school_id'] = [
      '#type' => 'hidden',
      '#default_value' => $payment_variable['school_id'],
    ];
    
    $form['campus'] = [
      '#type' => 'hidden',
      '#default_value' => $payment_variable['campus'],
    ];
    $form['program_funding'] = [
      '#type' => 'hidden',
      '#default_value' => $application_values->how_do_you_plan_on_funding_your_bootcamp,
      '#value' => $application_values->how_do_you_plan_on_funding_your_bootcamp,
    ];
    $cc_program_name = '';
    if($university_id){
      $course_map = Node::load($university_id);
      $cc_program_name = $course_map->get('field_climb_credit_program_name')->getValue()[0]['value'];
    } 
    $form['cc_program_name'] = [
      '#type' => 'hidden',
      '#default_value' => $cc_program_name,
    ];
    
    $form['product_id'] = ['#type' => 'hidden','#default_value' => $course_map->get('field_product_id')->getValue()[0]['value']];
    $form['cc_upfront_pid'] = ['#type' => 'hidden','#default_value' => $course_map->get('field_product_id')->getValue()[0]['value']];
    $form['cc_finance_pid'] = ['#type' => 'hidden','#default_value' => $course_map->get('field_product_id2')->getValue()[0]['value']];

    /** submit confirmation credit card */
    if($application_values->how_do_you_plan_on_funding_your_bootcamp == 'Pay in full up-front'){
      $form['submit_card'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Proceed to Payment'),
        '#button_type' => 'primary',
        '#op' => 'Pay via Climb Credit Payment Options',
        '#ajax' => array(
          'callback' => [$this,'callbackPaymentClick'],
          'event' => 'click',
          'progress' => array(
              'type' => 'throbber',
              'message' => 'Success',
          ),
        ),
        '#attributes' => [
          'class' => array('class' => 'btn btn-thanks btn btn-credit'),
        ],
      );
    }else{
      $form['link_response'] = [
        '#type' => 'markup',
        //'#markup' => '<p>Proceed to Payment via</p>',
      ];
      $form['payment_submit_1'] = array(
        '#type' => 'submit',
        '#value' => $this->t('CLIMB CREDIT LOAN APPLICATION'),
        '#button_type' => 'primary',
        '#op' => 'CLIMB_CREDIT_LOAN',
        '#ajax' => array(
          'callback' => [$this,'callbackPaymentClick'],
          'event' => 'click',
          'progress' => array(
              'type' => 'throbber',
              'message' => 'Success',
          ),
        ),
        '#attributes' => [
          'class' => array('class' => 'btn btn-thanks btn-credit'),
        ],
      );
      if($course_map->field_payment){
        $link_list = $course_map->get('field_payment')->getValue();
      }

      // if($link_list){

        //If show SCHEDULE CALL WITH FINANCE CONSULTANT on domain basis
        // if ($university_config->get($domain . '_schedule_call_with_financing') && $domain != 'adelphi_landing_university') {
        //   $op_title = 'SCHEDULE CALL WITH FINANCE CONSULTANT';
        //   $form['link_response1'] = [
        //     '#type' => 'markup',
        //     '#markup' => '<p>Other Financing Options are Available!</p><h1>There are many options available that we are ready to discuss with you. Schedule call with our financing consultant to find the best solution for you.</h1>',
        //   ];
          
        //   }elseif ($university_config->get($domain . '_schedule_call_with_financing') && $domain == 'adelphi_landing_university') {
        //     $op_title = 'SCHEDULE CALL WITH FINANCE CONSULTANT';
        //     $form['link_response2'] = [
        //       '#type' => 'markup',
        //       '#markup' => '<p>Other Financing Options are Available!</p><h1>There are many options available that we are ready to discuss with you. Schedule call with our financing consultant to find the best solution for you.</h1>',
        //     ];
            
            
        
          
        //   }
      if ($university_config->get($domains . '_chatbot_link')) {
      $op_title = 'SCHEDULE CALL WITH FINANCE CONSULTANT';
      $form['link_response2'] = [
            '#type' => 'markup',
            '#markup' => '<h4>We’re Here to Help!</h4><p>If you still have questions about funding your bootcamp, scholarship options, or just general questions about the program itself, your personal program consultant is here to help! </p>',
          ];
      }else{
          if($course_map->get('field_product_id2')->getValue()[0]['value']){
          $op_title = 'MERITIZE BANK LOAN OPTION';
          $form['payment_submit_0'] = array(
            '#type' => 'submit',
            '#value' => $this->t('MERITIZE BANK LOAN OPTION'),
            '#button_type' => 'primary',
            '#op' => 'MERITIZE_BANK_LOAN_OPTION',
            '#ajax' => array(
              'callback' => [$this,'callbackPaymentClick'],
              'event' => 'click',
              'progress' => array(
                  'type' => 'throbber',
                  'message' => 'Success',
              ),
            ),
            '#attributes' => [
              'class' => array('class' => 'btn btn-thanks btn-credit'),
            ],
          );
        }
        }
      }
    /** DMS required field */
    /** DMS API 1
     * @clientID (taxonomy univerisity) taxonomy university
     * @coursecode (our programs) content type
     */
    $form['coursecode'] = ['#type' => 'hidden','#default_value' => $course_code];
    $form['CountryCode'] = ['#type' => 'hidden','#default_value' => '+'.(int) filter_var($application_values->country, FILTER_SANITIZE_NUMBER_INT)];
    $form['MobileNo'] = ['#type' => 'hidden','#default_value' => $application_values->phone];
    $form['start_date'] = ['#type' => 'hidden','#default_value' => $payment_variable['batch_date']];
    if($application_values->how_do_you_plan_on_funding_your_bootcamp == 'Pay in full up-front'){
      $c_fee = (int) $payment_variable['course_fee'];
      // if($domain == 'srl_qc_niit_mts_com' || $domain == 'srl_adelphi_qc_niit_mts_com' || $domain == 'adelphi_landing_university' || $domain == 'srl_pointpark_qc_niit_mts_com' || $domain == 'point_park_university' || $domain == 'stackroute_suffolk_university'){
      //   $payment_variable['course_fee'] = $c_fee - 3000;
      //   } else{
      //     $payment_variable['course_fee'] = $c_fee - 1000;
      //   }
     // $payment_variable['course_fee'] = $c_fee - 1000;
     $payment_variable['course_fee'] = $c_fee - $discount_fee_amount;
      
    }
    $form['x_amount_total'] = ['#type' => 'hidden','#default_value' => $payment_variable['course_fee']];
    $form['#theme'] = 'proceed_payment_form';
    
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, $fee = NULL, $currency_code = NULL, $transaction_id = NULL, $course_id = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $fee = NULL, $currency_code = NULL, $transaction_id = NULL, $course_id = NULL) {
    

  }
  /** Payment click (PAY NOW)  */
  public function callbackPaymentClick(array &$form, FormStateInterface $form_state){
    $ajax_response = new AjaxResponse();
    $connection = \Drupal::service('database');
    $payment_button_name = 'Proceed to Payment';
    
    $DMS_API = \Drupal::service('srl_payment.dms');
    $DMS_Config = \Drupal::config('university_dmsapi.config');

    if($form_state->getValue('university_id')){
      $form_field_values = $form_state->getValues();
      $university = \Drupal::entityTypeManager()->getStorage('node')->load($form_state->getValue('university_id'));
      if($form_state->getTriggeringElement()['#op'] == 'Pay via Climb Credit Payment Options'){
        $payment_option = 'Paid via Climb Credit Payment Options';
        $PaymentMode = 'ONLINE';

   //     $data['primary_pipeline_status'] = 'No Financing Required';
   //     $data['sub_lead_status'] = 'N/A';
        if($DMS_Config->get('cc_API_key') && !empty($form_field_values['cc_upfront_pid']) && !empty($form_field_values['school_id'])){
          $payment_link = $DMS_API->ClimbCreditPaymentCreateLink($form_field_values,$DMS_Config);
        }else{
          $payment_link = $university->get('field_credit_payment_link')->getValue()[0]['value'];
        }
      }else{
        $payment_option = 'Paid Via ELMSelect Payment Options';
        $PaymentMode = 'LOAN';
     //   $data['primary_pipeline_status'] = 'Financing Required';
     //   $data['sub_lead_status'] = 'Attempting Contact';
        if($form_state->getTriggeringElement()['#op'] == 'CLIMB_CREDIT_LOAN'){
          $data['clicked_on_climb_credit'] = 'true';
          $payment_button_name = 'CLIMB CREDIT LOAN APPLICATION';
          if($DMS_Config->get('cc_API_key') && !empty($form_field_values['cc_finance_pid']) && !empty($form_field_values['school_id'])){
            $payment_link = $DMS_API->ClimbCreditLoanPaymentCreateLink($form_field_values,$DMS_Config);
            $payment_link = $payment_link->url;
          }else{
            $payment_link = $university->get('field_climb_credit_finance')->getValue()[0]['value'];
          }
        }else{
          $payment_button_name = 'MERITIZE BANK LOAN OPTION';
          $data['clicked_on_meritize'] = 'true';
          $link_list = $university->get('field_meritize_bank_finance')->getValue()[0]['value'];
          if($link_list){
              $payment_link = $link_list;
          }
        }
      }

      /**
       * Update hubspot lead status
       */
      $HubspotAPI = \Drupal::service('api.hubspotupdate');
      //$data['phase'] = 'PAYMENT MGMT';
            
      $response = $HubspotAPI->updateLeadStatus_ONPayment($data,$form_field_values['email']);
      /**DMS API2 call */

          /** get batch details */
          // $api_values['clientID'] = $form_field_values['clientID'];
          // $api_values['coursecode'] = $form_field_values['coursecode'];
          // $DMS_FEE = $DMS_API->DMS_GetBatchFeeDetails($api_values,$DMS_Config);

          // $api_values = [];
          // $api_values['OrgID'] = 3;
          // $api_values['Name'] = $form_field_values['student_name'];
          // $api_values['CourseCode'] = $form_field_values['coursecode'];
          // $api_values['ClientID'] = $form_field_values['clientID'];
          // $api_values['BatchID'] = $DMS_FEE->batchDetailsSummaryModelMT[0]->BATCHID;
          // $api_values['CountryCode'] = $form_field_values['CountryCode'];
          // $api_values['MobileNo'] = $form_field_values['MobileNo'];
          // $api_values['EmailID'] = $form_field_values['email'];
          // $date =  \Drupal::time()->getRequestTime();
          // $api_values['DateTime'] = date('Y-m-d',$date);
          // $api_values['Fee'] = [
          //   array(
          //     'PaymentMode' => $DMS_FEE->courseDetailsSummaryModelMT[0]->DISPLAYINFORMATION,
          //     'FeePatternCode' => $DMS_FEE->courseDetailsSummaryModelMT[0]->FEEPATTERNCODE,
          //     'Currency' => $DMS_FEE->courseDetailsSummaryModelMT[0]->CURRENCYCODE,
          //     'FeeAmount' => $DMS_FEE->courseDetailsSummaryModelMT[0]->BATCHFEES,
          //   ),
          //   array(
          //     'PaymentMode' => $DMS_FEE->courseDetailsSummaryModelMT[1]->DISPLAYINFORMATION,
          //     'FeePatternCode' => $DMS_FEE->courseDetailsSummaryModelMT[1]->FEEPATTERNCODE,
          //     'Currency' => $DMS_FEE->courseDetailsSummaryModelMT[1]->CURRENCYCODE,
          //     'FeeAmount' => $DMS_FEE->courseDetailsSummaryModelMT[1]->BATCHFEES,
          //   )
          // ];
          // if($form_field_values['coursecode']){
          //   $DMS_FEE = $DMS_API->DMS_InsertLearnerDetail($api_values, $DMS_Config);
          // }
          if($payment_link){
            $leadData['link']  = $payment_link;
            $leadData['window_name']  = \Drupal::time()->getRequestTime();
            $leadData['btn_name']  = $payment_button_name;
            $leadData['sitetype'] = 'landing microsite';
            $leadData['batch_date'] = $form_field_values['start_date'];
            $leadData['program_name'] = $form_field_values['cc_program_name'];
            $leadData['transaction_id'] = $form_field_values['transaction_id'];
            $ajax_response->addCommand(new InvokeCommand(NULL, 'paymentredirect', [$leadData]));
          }else{
            $connection->insert('srl_payment_quest_response')
            ->fields([
                'transaction_id' => $form_field_values['transaction_id'],
                'error_description' => 'There is something wrong in payment link',
            ])->execute();
          }
    }
    return $ajax_response;
  }
  
}