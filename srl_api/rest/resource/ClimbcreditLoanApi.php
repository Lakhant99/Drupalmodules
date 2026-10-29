<?php
namespace Drupal\srl_common\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactory;

/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "climbcreditloanapi_application_response",
 *   label = @Translation("Climb Credit payment loan respons"),
 *   uri_paths = {
 *     "canonical" = "/getloan/application-response",
 *     "create" = "/climbcreditloan/application-response"
 *   }
 * )
 */
class ClimbcreditLoanApi extends ResourceBase {

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
    * 403 Forbidden
    * 404 Not Found
    * 405 Method Not Allowed
    * 500 Internal Server Error
    */

    /**
     * Responds to entity POST requests.
     * @return \Drupal\rest\ResourceResponse
     */
    public function post(Request $request) {
      $data = json_decode($request->getContent(), TRUE);
      $DMS_Config = \Drupal::config('university_dmsapi.config');
      $get_key = $DMS_Config->get('cc_API_key');
      try {
        $params_key = $request->query->get('key');     
        if(isset($params_key) && $params_key == $get_key) {
          $data = $data["data"][0];
          $paymentStatus = $data["paymentStatus"];
          $paymentType = $data["paymentType"];
          $paymentMethod = $data["paymentMethod"];
          $student_id = $data["customer"]["studentID"];
          $student_name = $data["customer"]["name"];
          $student_email = $data["customer"]["email"];
          $student_phone_number = $data["customer"]["phone"];
          $connection = \Drupal::service('database');
          $query = db_select('srl_candidate_test_details','records');
          $query->fields('records',array('transaction_id','name','email_id','domain_id','course_code'));

          //Get the StudentID in the API !!
          if(!empty($student_id)) {
            $query->condition('records.transaction_id',$student_id);
            $result = $query->execute()->fetchAll();

            //Get the StudentID in the API and also exit in our db then Updated the records !!
            if(!empty($result)) {
              $fields = [
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentType,
                'payment_date' => $paymentMethod,
              ];
              $updated = $connection->update('srl_candidate_test_details')
              ->condition('transaction_id', $student_id)
              ->fields($fields)->execute();
              $response = ['status' => 200,'message' => 'Payment record successfully updated.'];
            } 
            else {

              //StudentID is not diffrent then insert the new records !!         
              $response = ['status' => 404,'message' => "The requested resource does not exist."];
            }   
          }
          else {

            //StudentID is null in the API !!
            $query->condition('email_id', $student_email);
            $query->condition('mobile_no', $student_phone_number);
            $result = $query->execute()->fetchAll();

            $student_id = !empty($student_id) ? $student_id : $student_phone_number.'_'.\Drupal::time()->getRequestTime();
            //Data exits in our DB from the condition email and mobile then updated the records !!
            if(!empty($result) && isset($result[0])) {
                $fields = [
                'transaction_id' => $student_id,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentType,
                'payment_date' => $paymentMethod,
              ];
              $updated = $connection->update('srl_candidate_test_details')
              ->condition('email_id', $student_email)
              ->condition('mobile_no', $student_phone_number)
              ->fields($fields)->execute();
              $response = ['status' => 200,'message' => 'Payment record successfully updated.'];
            } 
            else {

              //Data not exits in our DB from the condition email and mobile then insert the records !!
              $value = [
                'transaction_id' => $student_id,
                'name' => $student_name,
                'email_id' => $student_email,
                'mobile_no' => $student_phone_number,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentType,
                'payment_date' => $paymentMethod,
              ];
              $connection->insert('srl_candidate_test_details')
              ->fields(['transaction_id', 'name', 'email_id','mobile_no','payment_status','payment_method','payment_date'
              ])
              ->values($value)
              ->execute();
              $response = ['status' => 200,'message' => 'Payment record successfully inserted.'];
            }
          }
        } 
        else{
          $response = ['status' => 403,'message' => "The API key does not have permissions to perform the request."];
        }  
      } catch (EntityStorageException $e) {
        \Drupal::logger('custom-rest')->error($e->getMessage());
      }
      return new ResourceResponse($response);
    }
  }
  