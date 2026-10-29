<?php
namespace Drupal\srl_climb_credit_api\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Config\ConfigFactory;

/**
 * Provides a Demo Resource
 *
 * @RestResource(
 *   id = "climbcreditapi_application_response",
 *   label = @Translation("Climb Credit payment respons"),
 *   uri_paths = {
 *     "canonical" = "/get/application-response",
 *     "create" = "/climbcredit/application-response"
 *   }
 * )
 */
class ClimbcreditApi extends ResourceBase {

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
    $Srl_Config = \Drupal::config('srl_climb_credit_api_key.settings');
    $email_Config = \Drupal::config('srl_climb_credit_api_email.settings');
    try {
      $get_key = $Srl_Config->get('srl_api_key');
      $getemail =  $email_Config->get('custom_email');
      $params_key = $request->query->get('key');  
      if(isset($params_key) && $params_key == $get_key) {
        if(isset($data) && isset($data["data"][0])) {
          $ids = [];
          $data = $data["data"][0];
          $paymentAmount = $data["paymentAmount"];
          $paymentStatus = $data["paymentStatus"];
          $paymentDate =  $data["paymentDate"];
          $paymentMethod = $data["paymentMethod"];
          $student_id = $data["customer"]["studentID"];
          $student_name = $data["customer"]["name"];
          $student_email = $data["customer"]["email"];
          $student_phone_number = $data["customer"]["phone"];
          $connection = \Drupal::service('database');
          $query = db_select('srl_candidate_test_details','records');
          $query->fields('records',array('transaction_id','name','email_id','domain_id','course_code','json_response'));

          //Get the StudentID in the API !!
          if(!empty($student_id)) {
            $query->condition('records.transaction_id',$student_id, '=');
            $result = $query->execute()->fetchAll();

            //Get the StudentID in the API and also exit in our db then Updated the records !!
            if(!empty($result)) {
              $fields = [
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
              ];
              $updated = $connection->update('srl_candidate_test_details')
              ->condition('transaction_id', $student_id, '=')
              ->fields($fields)->execute();
             
              //Updated data on hubSpot
              $this->updateHubSpotData($data);
              $response = ['status' => 200,'message' => 'Payment record successfully updated.'];
            } 
            else {
              //StudentID is not diffrent then insert the new records !!
              //Student Api should not be diffrent ||         
              $response = ['status' => 404,'message' => "The requested resource does not exist."];
            }   
          }
          else {
            //StudentID is null in the API !! 
            $student_phone_number = str_replace("-", "", $student_phone_number);          
            $query->condition('email_id', $student_email, '=');
            $query->condition('mobile_no', '%'.$student_phone_number.'%', 'LIKE');
            $result = $query->execute()->fetchAll();
            foreach ($result as $key => $record) {
              $ids[] = $result[$key]->transaction_id;
              $get_time[] = (explode("_",$ids[$key]));
              $timestam[] = $get_time[$key][1];
              $jsonData = isset($result[$key]->json_response) ? $result[$key]->json_response : null ;
              if(!empty($jsonData)) {
                $unescapedJsonData = stripslashes($jsonData);
                $json_data_tb = json_decode($unescapedJsonData, true);
                $university[] = $json_data_tb["university"];
                $country_code[] = $json_data_tb["country"];
              }
            }
            $lastupdatetime = max($timestam);
            //Send Email
            if(Count($result) > 1){
              $detail['email'] = $student_email;
              $detail['phone'] = $student_phone_number;
              $detail['json_response'] = $university;
              $type = 'multiple';
              $this->StudentInfoMail($getemail, $ids, $type, $detail);
            }
            $student_id = $this->getStudentId($ids, $lastupdatetime);
            if (isset($lastupdatetime)) {                   
              //if get the multiple record with the same id and mobile_no so updated the latest updated record.
              $student_id = !empty($student_id) ? $student_id : $student_phone_number.'_'.\Drupal::time()->getRequestTime();
              //Data exits in our DB from the condition email and mobile then updated the records !!
              if(!empty($result) && isset($lastupdatetime)) {
                  $fields = [
                  'transaction_id' => $student_id,
                  'payment_status' => $paymentStatus,
                  'payment_method' => $paymentMethod,
                  'payment_date' => $paymentDate,
                ];
                $student_phone_number = isset($country_code[0]) ? (string)$country_code[0] .'-' .$student_phone_number : $student_phone_number;
                $updated = $connection->update('srl_candidate_test_details')
                ->condition('email_id', $student_email, '=')
                ->condition('mobile_no', $student_phone_number, '=')
                ->condition('transaction_id', $student_id, '=')
                ->fields($fields)->execute();

                //Updated data on hubSpot
                $this->updateHubSpotData($data);
                $response = ['status' => 200,'message' => 'Payment record successfully updated.'];
              }
            } 
            else {
              //Data not exits in our DB from the condition email and mobile then insert the records !!
              $student_id = !empty($student_id) ? $student_id : $student_phone_number.'_'.\Drupal::time()->getRequestTime();
              $value = [
                'transaction_id' => $student_id,
                'name' => $student_name,
                'email_id' => $student_email,
                'mobile_no' => $student_phone_number,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
              ];
              $connection->insert('srl_candidate_test_details')
              ->fields(['transaction_id', 'name', 'email_id','mobile_no','payment_status','payment_method','payment_date'
              ])
              ->values($value)
              ->execute();
              $type = "insertdb";
              $data['name'] = $student_name;
              $data['email'] = $student_email;
              $data['mobile_no'] = $student_phone_number;
              $data['type'] = 'insert_entry';
              $this->StudentInfoMail($getemail, $student_id, $type, $data);
              //Updated data on hubSpot
              $this->updateHubSpotData($data);
              $response = ['status' => 200,'message' => 'Payment record successfully inserted.'];
            }
          }
        }
        else{
          $response = ['status' => 400,'message' => 'Bad request.'];
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
   /**
   * hubspot Updated the records. 
   * */
  function updateHubSpotData($record) {
    $getdate = date_create($record["paymentDate"]);
    $date = date_format($getdate,"Y-m-d");
    $paymentAmount = $record["paymentAmount"];
    $paymentstatus = strtolower($record["paymentStatus"]);
    $email = $record["customer"]["email"];
    if(isset($paymentstatus) && $paymentstatus == 'paid'){
      $status = 'Yes';
    }else{
      $status = 'No';
    }
    if($record['type'] == 'insert_entry') {
      $student_name = $record['name'];
      $student_phone_number = $record['mobile_no'];
      $hubs_fields = [
        'payments_receieved' => 'Full Payer',
        'registration__y_n_' => $status,
        'registration_amount_collection' => $paymentAmount,
        'registration_date' => $date, //2022-06-08
        'firstname' => $student_name,
        'email' => $email,
        'phone' => $student_phone_number,
        'application_step' => '',
      ];
      /**
     * Created hubspot lead status
     */
      $HubspotAPI = \Drupal::service('api.hubspotupdate');
      $response = $HubspotAPI->CreatelandingPageContactLead($hubs_fields,$email);
      return;
    }
    else{
      $hubs_fields = [
        'payments_receieved' => 'Full Payer',
        'registration__y_n_' => $status,
        'registration_amount_collection' => $paymentAmount,
        'registration_date' => $date, //2022-06-08
      ];
    /**
     * Update hubspot lead status
     */
      $HubspotAPI = \Drupal::service('api.hubspotupdate');
      $response = $HubspotAPI->updateLeadStatus_ONPayment($hubs_fields,$email);
      return;
    }
  }
  /**
   * Get the Studentid last updated date. 
   **/
  function getStudentId($ids, $time) {
    foreach($ids as $key => $svalue) {
      $getupdatedata = substr( $svalue, -10);
      if($time == $getupdatedata) {
        $student_id = $svalue;
        return $student_id;
      }
    }
  }
  /**
  * Send Email for multiple User. 
  **/
  function StudentInfoMail($mail, $id, $type, $detail) {
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'srl_common';
    $key = 'payment_api'; // Replace with Your key
    $to = $mail;
    $email = $detail['email'];
    $phone = $detail['phone'];
    if($type == 'multiple') {
      $universitys = $detail['json_response'];
      $length = count($universitys);
      for ($x = 0; $x <= $length-1; $x++) {
        $content[] =  $universitys[$x] . ' or ';
      }
      $lastIndex = count($content) - 1;
      $content[$lastIndex] = str_replace(' or', '', $content[$lastIndex]);
      $university = implode(" ",$content);

      $params['title'] = 'Registered with multiple universities';
      $params['message'] = 'Hi team, <br><br>

      Greetings! <br><br>

      Learners email ID: ' . $email . '<br><br>

      Learners phone number: ' . $phone . '<br><br>
      
      <strong>' .$email. ' </strong>learner has done the payment, however the learner has visited multiple universities. Please check with the learner and understand if the payment has been done against '. $university . '.<br><br>
      
      Post getting the updates from the learner, please share the details to the development team (Madhann and team).<br><br>
      
      Thanks and regards,<br><br>
      
      SLI management<br><br>
       
      PS: learner details will be inserted in the DB once you share the details with the development team.';
    }
    if($type == 'insertdb') {
      $params['title'] = 'Please share the learner details';
      $params['message'] = 'Hi,<br><br>
      Learner ' . $email . ' has done the payment for course registration, however the learner has not visited Microsite.<br><br>
       
      Please connect with the learner and understand the university name against which the payment has been done.<br><br>
      
      Post getting the information from the learner, please share the details with the development team (Madhann and team).<br><br>
      
      Best regards,<br><br>
      
      SLI management<br><br>
      
      PS: learner details will be inserted in the DB once you share the details with the development team.';
    }
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $send = true;
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] != true) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      // drupal_set_message($message, 'error');
      \Drupal::messenger()->addMessage($message, 'error');
      \Drupal::logger('mail-log')->error($message);
      return;
    }
  }
}
  