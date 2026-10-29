<?php

namespace Drupal\srl_payment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;

/**
 * Show textfields based on Payeezy payment.
 *
 * @ingroup srl_payment
 */
class PayeezyPaymentCohortDateForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'srl_payeezy_cohort_date_form';
  }

  /**
   * {@inheritdoc}
   *
   * For all the requests this class gets, the buildForm() method will always be
   * called. If an AJAX request comes in, the form state will be set to the
   * state the user changed that caused the AJAX request. So if the user enabled
   * one of our checkboxes, it will be checked in $form_state.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $course_id = NULL, $transaction_id = NULL,$batch_start_date = NULL) {

    $form['#prefix'] = '<div id="payeezy-batch-date-form-wrapper">';
    $form['#suffix'] = '</div>';
    $domain = \Drupal::service('domain.negotiator')->getActiveId();

    
    // $date_query = db_select('node__field_course_batch_start_date','c_date');
    // $date_query->leftjoin('node__field_course_code_reference','c_map','c_date.entity_id=c_map.entity_id');
    // $date_query->leftjoin('node__field_domain_access','domain','domain.entity_id=c_date.entity_id');
    // $date_query->fields('c_date',array('field_course_batch_start_date_value'));
    // $date_query->condition('c_date.bundle','course_university_mapping');
    // $date_query->condition('c_map.field_course_code_reference_target_id',$course_id);
    // $records = $date_query->condition('domain.field_domain_access_target_id',$domain)->execute();
    // $date_format = [];
    // while($result = $records->fetchAssoc()){
    //   $date_format[] = strtotime($result['field_course_batch_start_date_value']);
      
    // }
    // sort($date_format);
    // foreach($date_format as $timestamp){
    //     $option[date('Y-m-d',$timestamp)] = date('M d, Y',$timestamp);
    // }
    $option = [];
    $timestamp = strtotime($batch_start_date);
    $option[$batch_start_date] = date('M d, Y',$timestamp);
    // if(!empty($option)){ 
      $form['cohort_date'] = [
        HASH_TYPE => 'select',
        HASH_TITLE => $this->t('Class Start Date:'),
        // '#title_display' => 'invisible',
        // '#required' => TRUE,
        '#options' => $option,
        '#default_value' => $batch_start_date,
        '#attributes' => ['readonly' => 'readonly']
        // '#ajax' => [
        //   'callback' => '::dateAjaxCallback',
        //   'event' => 'change',
        // ],
      ];
    // }
    
    $form['transaction_id'] = [
      HASH_TYPE => 'hidden',
      HASH_TITLE => $this->t('Transaction Id'),
      '#default_value' => $transaction_id,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];
    return $form;
  }

    /**
   * Ajax callback.
   */
  public function dateAjaxCallback(array &$form, FormStateInterface $form_state,$transaction_id = NULL) {

    $cohort_date = $form_state->getValue('cohort_date');
    $trans_id = $form_state->getValue('transaction_id');
    $response = new AjaxResponse();
    $connection = \Drupal::service('database');
    $query = $connection->update('payeezy_payment_details')
      ->fields([
        'cohort_date' => $cohort_date,
      ])
      ->condition('transaction_id', $trans_id, '=')
      ->execute();

    return $response;
  }
 
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, $course_id = NULL, $transaction_id = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $course_id = NULL, $transaction_id = NULL) {

  }

}

