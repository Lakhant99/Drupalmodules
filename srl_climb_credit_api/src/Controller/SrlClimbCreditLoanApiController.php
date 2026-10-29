<?php

namespace Drupal\srl_climb_credit_api\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for SRL Climb Credit Api routes.
 */
class SrlClimbCreditLoanApiController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build() {
    // Do something with your variables here.
    $output = array();
    $cache_list = ['tags' => []];
    $result = db_select('srl_candidate_test_details','records');
    $result->fields('records',array('id','transaction_id','name','email_id','mobile_no','application_status','application_lastupdateddate'));
    $result = $result->execute()->fetchAll();
    foreach ($result as $key => $value) {
      $output[$key]['id'] = $value->id;
      $output[$key]['transaction_id'] = $value->transaction_id;
      $output[$key]['name'] = $value->name;
      $output[$key]['email_id'] = $value->email_id;
      $output[$key]['mobile_no']= $value->mobile_no;
      $output[$key]['application_status'] = $value->application_status;
      $output[$key]['application_lastupdateddate'] = $value->application_lastupdateddate;
      $cache_list['tags'][] = 'srl_candidate_test_details:' . $value->id;
    }
    $data['cache'] = $cache_list;
    return [
      '#theme' => 'srl_climb_credit_loan_api_getdata',
      '#loanpaymentloanData' => $output,
      '#attached' => [
        'library' => [
            'srl_climb_credit_api/srl_climb_credit_api.map'
        ],
      ],
    ];
  }

}
