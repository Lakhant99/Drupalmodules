<?php
namespace Drupal\stackroute_modification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;

class StackrouteModification extends ControllerBase{

   /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
   // protected $entityFormBuilder;

   /**
      * Class constructor.
      *
      * @param \Drupal\Core\Form\FormBuilder $entityFormBuilder
      *   The Entity type manager service.
      */
   // public function __construct(FormBuilder $entityFormBuilder) {
   //    $this->entityFormBuilder = $entityFormBuilder;
   // }

   public function CustomLogin(){

      if (\Drupal::currentUser()->isAuthenticated()) {
           $path = "/user";
           $response = new RedirectResponse($path);
           $response->send();
       } else {
         $fb = $this->formBuilder();
         return $fb->getForm("Drupal\user\Form\UserLoginForm");
       }
      // kint('sfsf');
         
   }
   public function DisabledDomaiTitle(){
      return $site_name = \Drupal::config('system.site')->get('name');
   }
   public function DisabledDomain(){
      $output = '<div class="container">
         <div class="univesity-message">
         <h3>
         “Thank You for Your Interest in Hilbert\'s bootcamp series. We have discontinued these technical bootcamps until further notice. If you have any questions please reach out to <a href="mailto:rgarnica.hilbert@stackroute.com">rgarnica.hilbert@stackroute.com</a>”
         </h3>
         </div>
      </div>';
      return array(
         '#type' => 'markup',
         '#markup' => $output,
      );

   }
   public function DebuCode(){
      // $domain = 'mercy_edu';
      // /** update our programs */
      // $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['type' => 'our_programs']);
      // foreach($nodes as $key => $node){
      //       $node->set('field_course_code','WEBDP', $notify = true);
      //       $node->save();
      // }
      // /** update taxonomy value */
      // $properties['vid'] = 'university';
      // $terms = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
      // foreach($terms as $term){
      //    $term->field_university_code->setValue('AU001');
      //    $term->Save();
      // }
      // /** update course code */
      // $connection = \Drupal::service('database');
      // $query = $connection->update('srl_candidate_test_details')
      //       ->fields([
      //       'course_code' => 'WEBDP' ])
      //       ->condition('transaction_id', 3232, '!=')
      //       ->execute();


      // $DMS_API = \Drupal::service('srl_payment.dms');
      // $api_values = [];
      // $api_values['OrgID'] = 3;
      // $api_values['coursecode'] = 'WEBDP';
      // $api_values['clientID'] = 'AU001';
      // $api_values['BatchID'] = 2675;
      // $api_values['CountryCode'] = '1';
      // $api_values['MobileNo'] = '1234567890';
      // $api_values['EmailID'] = 'api@test.com';
      // $api_values['DateTime'] = '2021-03-07';
      // $api_values['Fee'] = [
      //   array(
      //     'PaymentMode' => 'LOAN',
      //     'FeePatternCode' => 'P985',
      //     'Currency' => 'USD',
      //     'FeeAmount' => '9500',
      //   ),
      //   array(
      //    'PaymentMode' => 'LOAN',
      //    'FeePatternCode' => 'P985',
      //    'Currency' => 'USD',
      //    'FeeAmount' => '9500',
      //   )
      // ];
      // echo '<pre>';print_r(json_encode($api_values));echo'</pre>';
      // // die();
      // $DMS_FEE = $DMS_API->DMS_InsertLearnerDetail($api_values);
      // kint($DMS_FEE);
      // \Drupal::logger('API response')->notice(print_r($DMS_FEE,true));



   //    $values = [
   //       'type' => 'course_university_mapping',
   //       'field_domain_access.target_id' => $domain,
   //       'status' => 1,
   //     ];
   //   $nodes_university = \Drupal::entityTypeManager()
   //   ->getStorage('node')
   //   ->loadByProperties($values);
   //   $nid = array_key_first($nodes_university);
   //   $batch_date = $nodes_university[$nid]->field_course_batch_start_date->getValue();
   //   $items = count($batch_date);
   //   foreach($batch_date as $key => $date_value){
   //      kint($date_value);
   //       $datetime = strtotime($date_value['value']);
   //       if($items == $key){
   //       $dates_string.= date('M d, Y',$datetime);
   //       }else{
   //       $dates_string.= date('M d, Y',$datetime).', ';
   //       }
     
   //   }
   //   kint($dates_string);

   //   kint();
   // $DMS_API = \Drupal::service('srl_payment.dms');
   // $DMS_FEE = $DMS_API->DMS_GetBatchFeeDetails();
      // $token = json_decode($DMS_token);
      // $token = $token->token;
      // kint($DMS_FEE);

      
      // kint($terms);
      // $current_date = \Drupal::time()->getRequestTime();
      // $results = \Drupal::entityQuery('node')
      //    ->condition('type', 'course_university_mapping')
      //    ->condition('field_domain_access.target_id', $domain)
      //    ->condition('field_course_code_reference.target_id', 3)
      //    ->execute();
      //    $course_id = array_values($results);
      // $course = \Drupal::entityTypeManager()->getStorage('node')->load($course_id[0]);

      // $batch_dates = $course->get('field_course_batch_start_date')->getValue();
      // $close_day = $course->get('field_registration_close_day')->getValue()[0]['value'];
      // $next_current_date = strtotime('+'.$close_day, $current_date); // 13th

      // $date_timestamp = [];
      // foreach($batch_dates as $batch_date){
      //    $get_date = strtotime($batch_date['value']);
      //    $get_date = date('d M Y',$get_date).' 23:59:59';
      //    $get_date = strtotime($get_date);
      //    if($get_date >= $next_current_date){
      //       $date_timestamp[] = $get_date;
      //    }
      // }
      // sort($date_timestamp);
      // $next_date = date('d F Y',$date_timestamp[0]);
      // kint($next_date);
         // kint($course);
      return array(
         '#type' => 'markup',
         '#markup' => 'debug'
      );
   }
}