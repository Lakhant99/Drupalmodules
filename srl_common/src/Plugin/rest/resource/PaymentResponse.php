<?php

namespace Drupal\srl_common\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
/**
 * Provides a operations Resource
 *
 * @RestResource(
 *   id = "order_summary_payment_response",
 *   label = @Translation("Order summary payment response"),
 *   uri_paths = {
 *     "canonical" = "/get-payment-response",
 *      "https://www.drupal.org/link-relations/create" = "/get-payment-response"
 *   }
 * )
 */
class PaymentResponse extends ResourceBase {
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
    * 204 No Content
    * 201 undefined header
    * 400 Bad request
    * 401 Unauthorized
    * 405 Method Not Allowed
    * 500 Internal Server Error
    */
  public function post($data,Request $request) {

    // $header = $request->headers->get( 'Content-Type' );
    $university_ID = $request->headers->get( 'UniversityID');
    $secret_key = $request->headers->get( 'Secret');
    if($university_ID && $secret_key){
      $query = db_select('taxonomy_term__field_university_code','uid');
      $query->leftjoin('taxonomy_term__field_university_domain','domain','uid.entity_id=domain.entity_id');
      $query->leftjoin('taxonomy_term__field_secret_key','s_key','uid.entity_id=s_key.entity_id');
      
      $query->fields('domain',array('field_university_domain_target_id'));
      $query->condition('uid.field_university_code_value',$university_ID);
      $validate =  $query->condition('s_key.field_secret_key_value',$secret_key)->execute()->fetchAll();
      if($validate[0]){
        /**ffFirst Name
        Last Name
        Email ID
        Phone No
        Program
        @delivery_mode => Payment Type
        @transaction_completed_on => Payment Date
        @paid_amount => Payment Amount
        */

        $domain = $validate[0]->field_university_domain_target_id;
        $connection = \Drupal::service('database');
        $fields = [
          'delivery_mode' => $data['payment_type'],
          'transaction_completed_on' => $data['payment_date'],
          'paid_amount' => $data['paid_amount'],
          'response_data' => json_encode($data),
        ];
        $updated = $connection->update('payeezy_payment_details')
        ->fields($fields)
        ->condition('email_id', $data['email_id'])
        ->condition('mobile_no', $data['phone_no'])
        ->condition('course_name', $data['program'])
        ->condition('university', $domain)->execute();
        if($updated){
          // set hubspot status to "Paid"
          $leadData['email'] = $data['email_id'];
          $leadData['status'] = 'Paid';
          $leadData['journey_type'] = 'Self Service Model';
          $leadData['status_level'] = 6;
          $HubspotAPI = \Drupal::service('api.hubspot');
          $HubspotAPI->updateLeadStatus($leadData);


          $response[] = [
            'status' => 200,
            'message' => 'Payment successfully updated.'
          ];
          $response = json_encode($response);
          return new ResourceResponse($response);
        }else{
          $response[] = [
            'status' => 404,
            'message' => 'No records found.'
          ];
          $response = json_encode($response);
          return new ResourceResponse($response);
        }
      }else{
        $response[] = [
          'status' => 404,
          'message' => 'No records found.'
        ];
        $response = json_encode($response);
        return new ResourceResponse($response);
      }

    }else{
      $response[] = [
        'status' => 403,
        'message' => 'The request did not include required headers.'
      ];
      $response = json_encode($response);
      return new ResourceResponse($response);
    }
  }
} 
