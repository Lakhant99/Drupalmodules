<?php
namespace Drupal\srl_payment\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactory;


class DMSServices {
   /**
   * {@inheritdoc}
   * 
   * https://qa.training.com/DigitalsliAPI/api
   * https://msd.training.com/DigitalsliAPI/api
   * https://www.training.com/DigitalsliAPI/api
   **/ 
   /**Get Token */
   public function Get_Token($client_url){
      // $client_url = 'https://qa.training.com/DigitalsliAPI/api/JWTtoken/GenerateToken'; 
      $client_url = trim($client_url);
      $client = \Drupal::httpClient();
      $api_header['headers'] = [
         'Content-Type' => 'application/json',
         'key' => '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429090fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1',
         'name' => 'training',
         'Authorization' => 'Basic c3Nzczpzc3M=',
      ];
      \Drupal::logger('DMS gnt')->notice('<pre>1 '.print_r($client_url,TRUE).'</pre>');
      try{
         $response = $client->get($client_url, [
            'headers' => $api_header['headers'],
        ])->getBody()->getContents();

      }catch(\Exception $e) {
         \Drupal::logger('DMS gnt')->notice('<pre>1 '.print_r($client_url,TRUE).'</pre>');
         \Drupal::logger('DMS Geneate token')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
      }
      if($response){
         $token = json_decode($response);
         $token = $token->token;

         $session = \Drupal::request()->getSession();
         $transaction_id = $session->get('transaction_id');
         $api_values['transaction_id'] = $transaction_id;
         $api_values['timestamp'] = \Drupal::time()->getRequestTime();
         $api_values['api_url'] = $client_url;
         $api_values['api_method'] = 'GET/Generate token';
         $api_values['api_json_header'] = json_encode($api_header['headers']);
         $api_values['api_json_response'] = $response;
         $records = $this->DMS_API_records_log($api_values,'srl_dms_api_records');
         return $token;
      }else{
         return FALSE;
      }
   }
   
    /**
     * DMS => Delivery Management System.
     * @ClientID > university code
     * 
    **/
   public function DMS_GetBatchFeeDetails($values, $DMS_Config){
      
      $token = $this->Get_Token($DMS_Config->get('API_get_token'));
      if($token){
         // $client_url = 'https://qa.training.com/DigitalsliAPI/api/CourseBatcheswrapperMT/';
         $client_url = $DMS_Config->get('API_get_batch');
         $client_url = trim($client_url);
         try{
            $client = \Drupal::httpClient();
            $api_header = [
               'token' => $token,
                  'CourseCode' => $values['coursecode'],
                  'Currency' => 'USD',
                  'OrgID' => 3,
                  'ClientID' => $values['clientID']
               ];
            $request_search = $client->get($client_url, [
               'headers' => $api_header,
            ])->getBody()->getContents();
            $result = json_decode($request_search);
            /** save get batch details api response */
               $session = \Drupal::request()->getSession();
               $transaction_id = $session->get('transaction_id');
               $api_values['transaction_id'] = $transaction_id;
               $api_values['timestamp'] = \Drupal::time()->getRequestTime();
               $api_values['api_url'] = $client_url;
               $api_values['api_method'] = 'GET/Get batch detail';
               $api_values['api_json_header'] = json_encode($api_header);
               $api_values['api_json_response'] = $request_search;
               $records = $this->DMS_API_records_log($api_values,'srl_dms_api_records');
               
            return $result;
         }catch(\Exception $e) {
            \Drupal::logger('DMS getinfo')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
         }
      }
   }
   public function DMS_InsertLearnerDetail($values, $DMS_Config){
   
      $token = $this->Get_Token($DMS_Config->get('API_get_token'));
      // $client_url = 'https://qa.training.com/DigitalsliAPI/api/SLI/PaymentRequestSaveSLI';
      $client_url = $DMS_Config->get('API_save_learner');
      $client_url = trim($client_url);
      try{
         $client = \Drupal::httpClient();
         $request_response = $client->post($client_url, [
               'headers' => ['token' => $token,'Content-Type' => 'application/json'],
               'body' => json_encode($values)
         ])->getBody()->getContents();
         $result = json_decode($request_response);
         
         \Drupal::logger('DMS Data Response')->notice('<pre> '.print_r($request_response,TRUE).'</pre>');
  
         /** save insert api response */
         $session = \Drupal::request()->getSession();
         $transaction_id = $session->get('transaction_id');
         $api_header = ['token' => $token,'Content-Type' => 'application/json'];
         $api_values['transaction_id'] = $transaction_id;
         $api_values['timestamp'] = \Drupal::time()->getRequestTime();
         $api_values['api_url'] = $client_url;
         $api_values['api_method'] = 'POST/Save student information';
         $api_values['api_json_header'] = json_encode($api_header);
         $api_values['api_json_body'] = json_encode($values);
         $api_values['api_json_response'] = $request_response;
         $records = $this->DMS_API_records_log($api_values,'srl_dms_api_records');
         return $result;
         \Drupal::logger('DMS Data Response')->notice('<pre> '.print_r($result,TRUE).'</pre>');
  
      }catch(\Exception $e) {
         \Drupal::logger('DMS insert')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
      }
   }
   public function DMS_API_records_log($values,$table_name){
      $connection = \Drupal::service('database');
      $connection->insert($table_name)->fields($values)->execute();
   }
   /** Create clib credit loan payment links */
   public function ClimbCreditLoanPaymentCreateLink($form_values, $DMS_Config){
      $DMS_Config = \Drupal::config('university_dmsapi.config');
      $client_url = $DMS_Config->get('CC_API_loan_client_url');
      $values = [
         "schoolId" => $form_values['school_id'],
         "firstName" => $form_values['student_fname'],
         "lastName" => $form_values['student_lname'],
         "email" => $form_values['email'],
         "phone" => $form_values['mobilephone_format'],
         "courseProductId" =>  $form_values['product_id'],
         "financeAmt_tuition" => (int)($form_values['x_amount_total']),
         "addressZip" => (string) $form_values['zip_code'],
         "campus" => $form_values['campus'],
         "programDate" => $form_values['start_date'],
         "program" => $form_values['cc_program_name'],
         "isPartTime" => ""
      ];
      try{
         $client = \Drupal::httpClient();
         $request_response = $client->post($client_url, [
               'headers' => ['Content-Type' => 'application/json'],
               'body' => json_encode($values)
         ])->getBody()->getContents();
         $result = json_decode($request_response);
            
         return $result;
      }catch(\Exception $e) {
         \Drupal::logger('DMS getinfo')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
      }
   }
   /** Create climb credit UP FRONT payment link */
   public function ClimbCreditPaymentCreateLink($form_values, $DMS_Config){
      $DMS_Config = \Drupal::config('university_dmsapi.config');
      $client_url = $DMS_Config->get('CC_API_online_client_url');
      $values = [
         "studentId" => $form_values['transaction_id'],
         "courseProductId" => $form_values['product_id'],
         "firstName" => $form_values['student_fname'],
         "lastName" => $form_values['student_lname'],
         "email" => $form_values['email'],
         "phone" => $form_values['mobilephone_format'],
         "totalTuition" => (int)($form_values['x_amount_total'].'00'),
         "frequencyType" => "days",
         "frequency" => 10,
         "numberInstallments" => 1,
         "addressZip" => $form_values['zip_code'],
         "campus" => $form_values['campus'],
         "program" => $form_values['cc_program_name'],
      ];
      try{
         $client = \Drupal::httpClient();
         $request_response = $client->post($client_url, [
               'headers' => ['Content-Type' => 'application/json','API-KEY' => $DMS_Config->get('cc_API_key')],
               'body' => json_encode($values)
         ])->getBody()->getContents();
         $result = json_decode($request_response);
         $result_link = FALSE;
         if($result->message == 'Information was processed successfully.' || $result->status == 201){
            $result_link =  $result->data->url; 
         }
         return $result_link;
      }catch(\Exception $e) {
         \Drupal::logger('DMS getinfo')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
      }
   }
}

