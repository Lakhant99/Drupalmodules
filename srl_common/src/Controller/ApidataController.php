<?php

namespace Drupal\srl_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;

/**
 * An example controller.
 */
class ApidataController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function content() {
    $requestField = \Drupal::request()->request;
    $transaction_id = \Drupal::request()->query->get('transaction_id');
    $transaction_id = $transaction_id ? $transaction_id : '1-9467212202_1664198690';
    $records = getAssessmentUserRecord($transaction_id);
///apidata?transaction_id=1-1234567899_1680796128

  $connection = \Drupal::service('database');
  // $query = db_select('srl_loan_application_detail','records');
  // $query->fields('records', array('application_lastupdateddate'));
  // $query->fields('records',[max('application_lastupdateddate')]);
  //$query->addExpression('MAX(application_lastupdateddate)', 'application_lastupdateddate');
  // $query = db_select('srl_loan_application_detail','records');
  // $query->fields('records', array('application_lastupdateddate'));
  // $query->addExpression('MAX(application_lastupdateddate)', 'application_lastupdateddate');
  // $query->groupBy('records.application_lastupdateddate');
  // $query->execute()->fetchAssoc();

    $all_indicator_json_data_file_path = drupal_realpath('public://') . '/data/ex.json';
    $all_indicator_json_data = file_get_contents($all_indicator_json_data_file_path, TRUE);
    $all_indicator_data = json_decode($all_indicator_json_data, TRUE);
    $records = $all_indicator_data['data']['results'];

    foreach ($records as $key => $value) {
      $test[] = $value["schoolId"];
    }

  $query =  $connection->query("SELECT MAX(application_lastupdateddate) FROM srl_loan_application_detail");
  $result = $query->fetchAll();

  // echo $query;
  $result = $query->execute()->fetchAssoc();
  echo $result["application_lastupdateddate"];
  exit;
   
    $this->displaydata($records,'Get from table');

  $client_url = 'https://climbcredit.com/api/v1/applications';
  try{
    $client = \Drupal::httpClient();
    $request_response = $client->get($client_url, [
        'headers' => ['Content-Type' => 'application/json','API-KEY' => '0fbaa5f1-529f-58b4-9595-688c59664019'],
      ])->getBody()->getContents();
      $result = json_decode($request_response, true);
  }catch(\Exception $e) {
      \Drupal::logger('Api service')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
  }

  // $this->displaydata($result, $client_url);
  print_r($result['data']['totalPages']);
  // die();
  $Total_page = $result['data']['totalPages'];

  for($i=1 ; $i <= $Total_page; $i++ ) {
    echo "The number is: $i <br>";

    $client_url = 'https://climbcredit.com/api/v1/applications?page=' . $i . '&limit=50&sort_by=DATE_CREATED&sort_direction=DESC';
    try{
      $client = \Drupal::httpClient();
      $request_response = $client->get($client_url, [
          'headers' => ['Content-Type' => 'application/json','API-KEY' => '0fbaa5f1-529f-58b4-9595-688c59664019'],
        ])->getBody()->getContents();
        $result = json_decode($request_response, true);
    }catch(\Exception $e) {
        \Drupal::logger('Api service')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
    }
  
    $this->displaydata($result, $client_url);
  
  }


  

 
  die();


    $build = [
      '#markup' => $this->t('Hello World!'),
    ];
    return $build;
  }

  public function displaydata($data, $msg){
    echo '<h1>' . $msg . '</h1>';
    echo"<pre>";
    print_r($data);
    echo"<pre>";
    echo "<h1>End</h1>";
    echo "<br>";
    echo "<br>";
    echo "<br>";
    
  }

}