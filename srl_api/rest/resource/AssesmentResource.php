<?php

namespace Drupal\srl_common\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
/**
 * Provides a operations Resource
 *
 * @RestResource(
 *   id = "assessment_confirmation_submit",
 *   label = @Translation("Assesment Confirmation Submit"),
 *   uri_paths = {
 *     "canonical" = "/assessment-submit",
 *      "https://www.drupal.org/link-relations/create" = "/assessment-submit"
 *   }
 * )
 */
class AssesmentResource extends ResourceBase {
  /**
   * Responds to entity GET requests.
   * @return \Drupal\rest\ResourceResponse
   */

  public function get($data) {
    $response = ['status' => 503,'message' => 'Service Unavailable'];
    return new ResourceResponse($response);
  }
   /*status code
    * 200 success
    * 201 undefined header
    * 400 Bad request
    * 401 Unauthorized
    * 405 Method Not Allowed
    * 500 Internal Server Error
    */
  public function post($data,Request $request) {

    // $header = $request->headers->get( 'Content-Type' );
  
    $transaction_id = $data["pTestCode"];
	  // \Drupal::logger('webform handler')->notice('echo '.print_r($data,TRUE));
    if(!$transaction_id){
      $response[] = [
        'status' => 404,
        'message' => 'The request did not include an undefined header or missing transaction id.'
      ];
      $response = json_encode($response);
      return new ResourceResponse($response);
    }else{
        $connection = \Drupal::service('database');
        
          $pass = db_select('srl_candidate_test_details','records');
          $pass->fields('records',array('passing_percentage'));
          $pass->condition('records.transaction_id',$transaction_id);
          $pass_status = $pass->execute()->fetchAll();
          $result_status = 'fail';
          if($data["pMarks"] >= $pass_status[0]->passing_percentage){
            $result_status = 'passed';
          }
          $query = $connection->update('srl_candidate_test_details')
            ->fields([
              "assessment_ended_on" => \Drupal::time()->getRequestTime(),
              "actual_started_on" => $data["pStart_dt"],
              "actual_ended_on" => $data["pEnd_dt"],
              "test_score" => $data["pMarks"],
              "result" => $result_status,
            ])
            ->condition('transaction_id', $transaction_id, '=')
            ->execute();
      if($query){
        $response = [
          'status' => 200,
          'message' => 'success.'
        ];
      }else{
        $response = [
          'status' => 201,
          'message' => 'The request was invalid or invalid transaction id.'
        ];
      }
      return new ResourceResponse($response);
    }
  }

  
} 