<?php

namespace Drupal\srl_common\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\RemoveCommand;

/**
 * SRL payment form
 */
class SRLProceedPaymentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'srl_proceed_payment_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['site_name'] = \Drupal::config('system.site')->get('name');
    $domain = \Drupal::service('domain.negotiator')->getActiveId();
    
    
    $API_Config = \Drupal::config('universityapi_paymentlink.config');
    $link_count = 0;
    $university_config = \Drupal::config('university_info.config');
    $form['chatbot_link'] = $university_config->get($domain . '_chatbot_link');
    $form['schedule_call_with_financing'] = $university_config->get($domain . '_schedule_call_with_financing');
    $form['site_phone']  = $university_config->get($domain.'_phone');
    $form['upfront_payment_heading'] = \Drupal::config('onlinepayment.config')->get('upfront_payment_heading.value');
    $form['climb_loan_heading'] = \Drupal::config('onlinepayment.config')->get('climb_loan_heading.value');
    $form['interest_financing_heading'] = \Drupal::config('onlinepayment.config')->get('interest_financing_heading.value');
    $form['ascent_payment_text_heading'] = \Drupal::config('onlinepayment.config')->get('ascent_payment_text_heading.value');
    $form['meritize_credit_text_heading'] = \Drupal::config('onlinepayment.config')->get('meritize_credit_text_heading.value');


    $up_front = FALSE;
    $loan_link_count = 0;
    if ($API_Config->get($domain . '_upfront_payment')) {
      $link_count++;
      $up_front = TRUE;
      $form['upfront_payment'] = [
        '#type' => 'submit',
        '#value' => 'SELECT',
        '#name' => 'upfront_payment',
        '#button_type' => 'primary',
        '#op' => 'upfrontpayment',
        '#ajax' => array(
          'callback' => [$this, 'callbackPaymentClick'],
          'event' => 'click',
          'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
          ),
        ),
        '#attributes' => [
          'class' => array('class' => 'btn btn-submit'),
        ],
      ];
    }
    if ($API_Config->get($domain . '_climb_loan_payment')) {
      $link_count++;
      $loan_link_count++;
      $form['climb_credit_loan'] = [
        '#type' => 'submit',
        '#value' => 'SELECT',
        '#name' => 'climb_credit_loan',
        '#button_type' => 'primary',
        '#op' => 'climbcreditloan',
        '#ajax' => array(
          'callback' => [$this, 'callbackPaymentClick'],
          'event' => 'click',
          'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
          ),
        ),
        '#attributes' => [
          'class' => array('class' => 'btn btn-submit'),
        ],
      ];
    }
    
    if ($university_config->get($domain . '_schedule_call_with_financing')) {
      $op_title = 'SCHEDULE CALL WITH FINANCE CONSULTANT';
      $form['link_response1'] = [
        '#type' => 'markup',
        '#markup' => '<p>Other Financing Options are Available!</p><h1>There are many options available that we are ready to discuss with you. Schedule call with our financing consultant to find the best solution for you.</h1>',
      ];
    }
    
    if ($API_Config->get($domain . '_upfront_payment_text')) {
      $form['_upfront_payment_text'] = $API_Config->get($domain.'_upfront_payment_text.value');
    }
    if ($API_Config->get($domain . '_climb_loan_text')) {
      $form['_climb_loan_text'] = $API_Config->get($domain.'_climb_loan_text.value');
    }
    if ($API_Config->get($domain . '_interest_financing_text')) {
      $form['_interest_financing_text'] = $API_Config->get($domain.'_interest_financing_text.value');
    }
    if ($API_Config->get($domain . '_ascent_payment_text')) {
      $form['_ascent_payment_text'] = $API_Config->get($domain.'_ascent_payment_text.value');
    }
    if ($API_Config->get($domain . '_fieldset_meritize_credit_text')) {
      $form['_fieldset_meritize_credit_text'] = $API_Config->get($domain.'_fieldset_meritize_credit_text.value');
    }

      if($domain == 'srl_qc_niit_mts_com' || $domain == 'srl_adelphi_qc_niit_mts_com' || $domain == 'adelphi_landing_university' || $domain == 'srl_pointpark_qc_niit_mts_com' || $domain == 'point_park_university' || $domain == 'stackroute_suffolk_university'){
        $op_title = 'SCHEDULE CALL WITH FINANCE CONSULTANT';
        $form['link_response2'] = [
          '#type' => 'markup',
          '#markup' => '<p>Other Financing Options are Available!</p><h1>There are many options available that we are ready to discuss with you. Schedule call with our financing consultant to find the best solution for you.</h1>',
        ];
      
    }

    if ($API_Config->get($domain . '_financing_through_climb_payment')) {
      $link_count++;
      $loan_link_count++;
      $form['finance_climb_credit_loan'] = [
        '#type' => 'submit',
        '#value' => 'SELECT',
        '#name' => 'finance_climb_credit_loan',
        '#button_type' => 'primary',
        '#op' => 'financeclimbcreditloan',
        '#ajax' => array(
          'callback' => [$this, 'callbackPaymentClick'],
          'event' => 'click',
          'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
          ),
        ),
        '#attributes' => [
          'class' => array('class' => 'btn btn-submit'),
        ],
      ];
    }

    $programs_list = setUniversityProgramsOptions($domain);
    $options['SFO'] = 'SELECT';
    foreach($programs_list as $key => $value){
      if($API_Config->get($domain . '_meritize_payment_' . $key)){
        $links[] = $API_Config->get($domain . '_meritize_payment_' . $key);
      }
      if($API_Config->get($domain . '_upfront_payment_link_coursewise' . $key)){
        $course_links[] = $API_Config->get($domain . '_upfront_payment_link_coursewise' . $key);
      }
      $options[$key] = $value;
    }
    if ($links) {
      $link_count++;
      $loan_link_count++;
      $form['finance_meritize'] = [
        HASH_TYPE => 'select',
        HASH_TITLE => '',
        HASH_OPTIONS => $options,
        '#op' => 'meritize_payment',
        '#ajax' => array(
          'callback' => [$this, 'callbackPaymentClick'],
          'event' => 'change',
          'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
          ),
        ),
      ];
    }

    if ($course_links) {
      $link_count++;
      $loan_link_count++;
      $form['upfront_payment_link_coursewise'] = [
        HASH_TYPE => 'select',
        HASH_TITLE => '',
        HASH_OPTIONS => $options,
        '#op' => 'upfront_payment_link_coursewise',
        '#ajax' => array(
          'callback' => [$this, 'callbackPaymentClick'],
          'event' => 'change',
          'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
          ),
        ),
      ];
    }

    if ($API_Config->get($domain . '_ascent_payment')) {
      $link_count++;
      $loan_link_count++;
      $form['ascent_payment'] = [
        '#type' => 'submit',
        '#value' => 'SELECT',
        '#name' => 'ascent_payment',
        '#button_type' => 'primary',
        '#op' => 'ascent_payment',
        '#ajax' => array(
          'callback' => [$this, 'callbackPaymentClick'],
          'event' => 'click',
          'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
          ),
        ),
        '#attributes' => [
          'class' => array('class' => 'btn btn-submit'),
        ],
      ];
    }
    if ($API_Config->get($domain . '_fieldset_meritize_credit')) {
      $link_count++;
      $loan_link_count++;
      $form['meritize_credit'] = [
        '#type' => 'submit',
        '#value' => 'SELECT',
        '#name' => 'meritize_credit',
        '#button_type' => 'primary',
        '#op' => 'meritize_credit',
        '#ajax' => array(
          'callback' => [$this, 'callbackPaymentClick'],
          'event' => 'click',
          'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
          ),
        ),
        '#attributes' => [
          'class' => array('class' => 'btn btn-submit'),
        ],
      ];
    }
    
    
    // if($domain == 'srl_qc_niit_mts_com' || $domain == 'srl_adelphi_qc_niit_mts_com'){
    //   // $link_count++;
    //   // $loan_link_count++;
    //   $op_title = 'SCHEDULE CALL WITH FINANCE CONSULTANT';
    //   $form['link_response1'] = [
    //     '#type' => 'markup',
    //     '#markup' => '<p>Other Financing Options are Available!</p><h1>There are many options available that we are ready to discuss with you. Schedule call with our financing consultant to find the best solution for you.</h1>',
    //   ];
      
    // }

    if ($link_count == 4 || $link_count == 5 || $link_count == 6) {
      $link_count = 'col-md-6 col-lg-4';
    } elseif ($link_count == 3) {
      $link_count = 'col-md-4 col-lg-4';
    } elseif ($link_count == 2) {
      $link_count = 'col-md-6 col-lg-6';
    } else {
      $link_count = 'col-md-6 col-lg-6 offset-md-3 offset-lg-3';
    }
    if($loan_link_count == 1){
      $break_parent1 = 6;
      $break_parent2 = 6;
      $break_link = 12;
    }elseif($loan_link_count == 2){
      $break_parent1 = 4;
      $break_parent2 = 8;
      $break_link = 6;
    }elseif($loan_link_count == 3){
      $break_parent1 = 3;
      $break_parent2 = 9;
      $break_link = 4;
    }
    $form['university_link_count'] = $link_count;

    $form['break_parent1'] = $break_parent1;
    $form['break_parent2'] = $break_parent2;
    $form['break_link'] = $break_link;
    $form['#theme'] = 'srl_proceed_payment_form';
    
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
    $domain = \Drupal::service('domain.negotiator')->getActiveId();
    $API_Config = \Drupal::config('universityapi_paymentlink.config');
    $ajax_response = new AjaxResponse();
    $payment_link = false;
    $trigger_button = $form_state->getTriggeringElement()['#op'];
    // \Drupal::logger('online payment')->notice('trigger'.print_r($form_state,true));
    $leadData['payment_course'] = 'select';
    $payment_button_name = 'Upfront Payment';
    if($trigger_button == 'upfrontpayment'){
      $payment_link = $API_Config->get($domain.'_upfront_payment');
      $payment_button_name = 'Upfront Payment';
    }elseif($trigger_button == 'climbcreditloan'){
      $payment_link = $API_Config->get($domain.'_climb_loan_payment');
      $payment_button_name = 'Apply for a Climb Loan';
    }elseif ($trigger_button == 'financeclimbcreditloan') {
      $payment_button_name = 'Apply for Financing Through Climb';
      $payment_link = $API_Config->get($domain.'_financing_through_climb_payment');
    }elseif ($trigger_button == 'meritize_payment' && $form_state->getValue('finance_meritize') != 'SFO') {
      $course_id = $form_state->getValue('finance_meritize');
      $payment_link = $API_Config->get($domain . '_meritize_payment_'. $course_id);
      $payment_button_name = 'Finance Through Meritize';
      $leadData['payment_course'] =  \Drupal::entityTypeManager()->getStorage('node')->load($course_id)->getTitle();
    }elseif ($trigger_button == 'upfront_payment_link_coursewise' && $form_state->getValue('upfront_payment_link_coursewise') != 'SFO') {
      $course_id = $form_state->getValue('upfront_payment_link_coursewise');
      $payment_link = $API_Config->get($domain . '_upfront_payment_link_coursewise'. $course_id);
      $payment_button_name = 'Upfront Payment';
      $leadData['payment_course'] =  \Drupal::entityTypeManager()->getStorage('node')->load($course_id)->getTitle();
    }
    elseif ($trigger_button == 'ascent_payment') {
      $payment_button_name = 'Ascent Payment';
      $payment_link = $API_Config->get($domain.'_ascent_payment');
    }
    elseif ($trigger_button == 'meritize_credit') {
      $payment_button_name = 'meritize Payment';
      $payment_link = $API_Config->get($domain.'_fieldset_meritize_credit');
    }

    // \Drupal::logger('online payment')->notice('trigger link' . print_r($payment_link, true));
    if($payment_link){
      $leadData['link']  = $payment_link;
      $leadData['window_name']  = \Drupal::time()->getRequestTime();

      $leadData['btn_name']  = $payment_button_name;
      $leadData['sitetype'] = 'online independent payment';
      $leadData['page_name'] = 'online independent payment';
      $ajax_response->addCommand(new InvokeCommand(NULL, 'onlinepaymentredirect', [$leadData]));
    }
    return $ajax_response;
  }
  
}