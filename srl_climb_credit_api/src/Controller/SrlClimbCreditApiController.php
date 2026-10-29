<?php

namespace Drupal\srl_climb_credit_api\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for SRL Climb Credit Api routes.
 */
class SrlClimbCreditApiController extends ControllerBase {

  /**
   * Builds the response of api.
   */
  public function build() {
    // Do something with your variables here.
    $output = array();
    $state = \Drupal::state();
    $last_refresh_state = 'srl_climb_credit_api.last_refresh';
    $last_refresh = $state->get($last_refresh_state);
    $timezone = date_default_timezone_get();
    date_default_timezone_set($timezone);
    $last_refresh = date('m/d/Y h:i:s a', $last_refresh);
    $cache_list = ['tags' => []];
    $result = db_select('srl_loan_application_detail','records');
    $result->fields('records',array('id','transaction_id','name','email','phone','application_status','application_lastupdateddate'));
    $result = $result->execute()->fetchAll();
    foreach ($result as $key => $value) {
      $output[$key]['id'] = $value->id;
      $output[$key]['transaction_id'] = $value->transaction_id;
      $output[$key]['name'] = $value->name;
      $output[$key]['email'] = $value->email;
      $output[$key]['phone']= $value->phone;
      $output[$key]['application_status'] = $value->application_status;
      $output[$key]['application_lastupdateddate'] = $value->application_lastupdateddate;
      // $output[$key]['application_course'] = $value->application_course;
      // $output[$key]['application_university'] = $value->application_university;
      $cache_list['tags'][] = 'srl_loan_application_detail:' . $value->id;
    }
    $data['cache'] = $cache_list;
    return [
      '#theme' => 'srl_climb_credit_api_loan_data',
      '#loanData' => $output,
      '#lastruncron' => $last_refresh,
      '#attached' => [
        'library' => [
            'srl_climb_credit_api/srl_climb_credit_api.map'
        ],
      ],
    ];
  }

}
