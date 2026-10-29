<?php

namespace Drupal\srl_common\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Webform validate handler.
 *
 * @WebformHandler(
 *   id = "WebformCustomSubmitHandler",
 *   label = @Translation("Webform new application Custom Submit Handler"),
 *   category = @Translation("Settings"),
 *   description = @Translation("Form alter to add new custom submit handler."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class WebformCustomSubmitHandler extends WebformHandlerBase {

   /**
   * {@inheritdoc}
   */
   public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
      
      $submition_data = $webform_submission->getData();
      $curret_page = $form_state->get('current_page');
      $HubspotAPI = \Drupal::service('api.hubspotupdate'); 

      $session = \Drupal::request()->getSession();
      $temp_transaction_id = $session->get('temp_transaction_id_resume');
      if(!$temp_transaction_id){
         if($curret_page == 'basic_information'){
            \Drupal::logger('new webform')->notice('<pre>basic_information: '.print_r($submition_data,TRUE).'</pre>');
            $site_name = \Drupal::config('system.site')->get('name');
            $domain = \Drupal::service('domain.negotiator')->getActiveId();
            $university_config = \Drupal::config('university_info.config');
            $hubspot_university_name = $university_config->get($domain.'_hubspot_university_name'); 
            $options = setUniversityProgramsOptions($domain);
            $data = [
               "university" => $hubspot_university_name,
               "program" => $options[$submition_data['program_interest']],
               'why_are_you_interested_in_pursuing_a_bootcamp_' => $submition_data['interested_in_pursuing_a_bootcamp'],
               'if_other___interested_in_bootcamp_notes' => $submition_data['interested_in_bootcamp_notes'],
               'which_factors_are_the_most_important_when_deciding_to_enroll_' => implode(";", $submition_data['factors_deciding_to_enroll']),
               'firstname' => $submition_data['full_name'],
               'lastname' => $submition_data['last_name'],
               'email' => $submition_data['email'],
               "phone" => '+'.(int) filter_var($submition_data['country'], FILTER_SANITIZE_NUMBER_INT).'  '.$submition_data['phone'],
               'zip' => $submition_data['zip'],
               'are_you_18_or_older_' => $submition_data['are_you_18_or_older'],
              // 'gclid' => $submition_data['gclid'], 
               'application_step' => 2,
            ];
            if($submition_data['utm_source'] || $submition_data['utm_campaign'] || $submition_data['utm_medium'] || $submition_data['utm_term'] || $submition_data['utm_content'] || $submition_data['gclid']){
               $data['utm_source'] = $submition_data['utm_source'];
               $data['utm_campaign'] = $submition_data['utm_campaign'];
               $data['utm_medium'] = $submition_data['utm_medium'];
               $data['utm_term'] = $submition_data['utm_term'];
               $data['utm_content'] = $submition_data['utm_content'];
               $data['gclid'] = $submition_data['gclid'];

            }
            if(!$form_state->getErrors() && $data['firstname'] != ''){
            $response = $HubspotAPI->saveRecordToHubspot($data,$submition_data['email']);
            }
            /**rest form steps */
            $connection = \Drupal::service('database');
            $phone_number = merge_PhoneWithCountry($submition_data['phone'],$submition_data['country']);
            $connection->update('srl_candidate_test_details')
            ->fields([
               'steps_level' => 0,
            ])
            ->condition('email_id',$data['email'])
            ->condition('mobile_no', $phone_number)
            ->condition('domain_id',$domain)
            ->execute();
            
            $webformData = [
               'step' => 'program_fit',
               'program_interest' => $submition_data['program_interest'],
               'interested_in_pursuing_a_bootcamp' => $submition_data['interested_in_pursuing_a_bootcamp'],
               'interested_in_bootcamp_notes' => $submition_data['interested_in_bootcamp_notes'],
               'factors_deciding_to_enroll' => implode(";", $submition_data['factors_deciding_to_enroll']),
               'email' => $submition_data['email'],
               "phone" => $submition_data['phone'],
               'country' => $submition_data['country'],
               'gclid' => $submition_data['gclid'],
               'utm_source' => $data['utm_source'],
               'utm_campaign' => $data['utm_campaign'],
               'utm_medium' => $data['utm_medium'],
               'utm_term' => $data['utm_term'],
               'utm_content' => $data['utm_content'],
               'gclid' => $data['gclid'],
            ];
            $response = step1_insert_webformTeampData($webformData);

            $webformData = [
               'step' => $curret_page,
               'program_interest' => $submition_data['program_interest'],
               'full_name' => $submition_data['full_name'],
               'last_name' => $submition_data['last_name'],
               'email' => $submition_data['email'],
               "phone" => $submition_data['phone'],
               'country' => $submition_data['country'],
               'zip' => $submition_data['zip'],
               // 'when_are_you_looking_to_start_your_bootcamp_' => $submition_data['when_are_you_looking_to_start_your_bootcamp_'],
               'are_you_18_or_older' => $submition_data['are_you_18_or_older'],
            ];
            if($response){
               save_WebformTempData($webformData);
            }
         }elseif($curret_page == 'educational_background'){
            \Drupal::logger('new webform')->notice('<pre>educational_background: '.print_r($submition_data,TRUE).'</pre>');
            
            $data = [
               'do_you_have_a_high_school_diploma_or_ged_equivalent_' => $submition_data['do_you_have_a_high_school_diploma_or_ged_equivalent'],
               'what_is_your_highest_level_of_education_' => $submition_data['what_is_your_highest_level_of_education_'],
               'data_analytics_level_of_knowledge' => $submition_data['data_analytics'],
               'cyber_security_level_of_knowledge' => $submition_data['cyber_security'],
               'web_development_level_of_knowledge' => $submition_data['web_development_software_engineering'],
               'cloud_computing_level_of_knowledge' => $submition_data['cloud_computing'],
               'digital_marketing_level_of_knowledge' => $submition_data['digital_marketing'],
               'application_step' => 3,
            ];
            if(!$form_state->getErrors()){
               $response = $HubspotAPI->saveRecordToHubspot($data,$submition_data['email']);
               }
          
            
            $webformData = [
               'step' => $curret_page,
               'program_interest' => $submition_data['program_interest'],
               'do_you_have_a_high_school_diploma_or_ged_equivalent' => $submition_data['do_you_have_a_high_school_diploma_or_ged_equivalent'],
               'data_analytics' => $submition_data['data_analytics'],
               'cyber_security' => $submition_data['cyber_security'],
               'web_development_software_engineering' => $submition_data['web_development_software_engineering'],
               'cloud_computing' => $submition_data['cloud_computing'],
               'digital_marketing' => $submition_data['digital_marketing'],
               'email' => $submition_data['email'],
               "phone" => $submition_data['phone'],
               'country' => $submition_data['country'],
            ];
            save_WebformTempData($webformData);

         }elseif($curret_page == 'program_funding'){
            \Drupal::logger('new webform')->notice('<pre>program_funding: '.print_r($submition_data,TRUE).'</pre>');
            $data = [
               'how_do_you_plan_on_funding_your_bootcamp_' => $submition_data['how_do_you_plan_on_funding_your_bootcamp'],
               'if_funding___type' => $submition_data['financing_type'],
               'if_funding___co_singer_needed' => $submition_data['do_you_have_a_co_signer_if_needed'],
               'application_step' => 4,
            ];
            
            if(!$form_state->getErrors()){
               $response = $HubspotAPI->saveRecordToHubspot($data,$submition_data['email']);
               }
            
            $phone_number = merge_PhoneWithCountry($submition_data['phone'],$submition_data['country']);
            $temp_transaction_id = get_transactionid($submition_data['email'], $phone_number, 'basic_information');
            $connection = \Drupal::service('database');
            $connection->delete('stackroute_webform_tempdata')
            ->condition('transaction_id', $temp_transaction_id->transaction_id)
            ->execute();
         }
      }
   }
}