<?php

namespace Drupal\srl_common\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Webform validate handler.
 *
 * @WebformHandler(
 *   id = "LandingPageLeadCreate",
 *   label = @Translation("Landing page lead Create Webform submit Handler"),
 *   category = @Translation("Settings"),
 *   description = @Translation("Landing page lead form create/update hubspot lead."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class LandingPageLeadCreate extends WebformHandlerBase {

   /**
   * {@inheritdoc}
   */
   public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
      
      $submition_data = $webform_submission->getData();
      $curret_page = $form_state->get('current_page');
      $HubspotAPI = \Drupal::service('api.hubspotupdate'); 

      if($curret_page == 'basic_information'){
         \Drupal::logger('landing lead form')->notice('<pre>basic_information: '.print_r($submition_data,TRUE).'</pre>');
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
            'which_factors_are_the_most_important_when_deciding_to_enroll_' => $submition_data['factors_deciding_to_enroll'],
            'firstname' => $submition_data['full_name'],
            'lastname' => $submition_data['last_name'],
            'email' => $submition_data['email'],
            "phone" => '+1  '.$submition_data['phone'],
            'zip' => $submition_data['zip'],
            'are_you_18_or_older_' => 'true',
          //  'gclid' => $submition_data['gclid'], 
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
         \Drupal::logger('landing lead form hubspot data')->notice('<pre>basic_information: '.print_r($data,TRUE).'</pre>');

         if($form_state->getErrors()){
            \Drupal::logger('form error')->notice('<pre>basic_information: '.print_r($form_state->getErrors(),TRUE).'</pre>');
         }else{
            \Drupal::logger('create hubspot with loger')->notice('<pre>basic_information: '.print_r($form_state->getErrors(),TRUE).'</pre>');
            $response = $HubspotAPI->saveRecordToHubspot($data,$submition_data['email']);
         }
      }elseif($curret_page == 'educational_background'){
         \Drupal::logger('landing lead form')->notice('<pre>educational_background: '.print_r($submition_data,TRUE).'</pre>');
         
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
         \Drupal::logger('landing lead form hubspot data')->notice('<pre>educational_background: '.print_r($data,TRUE).'</pre>');
         if($form_state->getErrors()){
            \Drupal::logger('form error')->notice('<pre>educational_background: '.print_r($form_state->getErrors(),TRUE).'</pre>');
         }else{
            \Drupal::logger('create hubspot with loger')->notice('<pre>educational_background: '.print_r($form_state->getErrors(),TRUE).'</pre>');
            $response = $HubspotAPI->saveRecordToHubspot($data,$submition_data['email']);
         }
         
      }elseif($curret_page == 'program_funding'){
         \Drupal::logger('landing lead form')->notice('<pre>program_funding: '.print_r($submition_data,TRUE).'</pre>');
         $data = [
            'how_do_you_plan_on_funding_your_bootcamp_' => $submition_data['how_do_you_plan_on_funding_your_bootcamp'],
            'if_funding___type' => $submition_data['financing_type'],
            'if_funding___co_singer_needed' => $submition_data['do_you_have_a_co_signer_if_needed'],
            'application_step' => 4,
         ];
         \Drupal::logger('landing lead form hubspot data')->notice('<pre>program_funding: '.print_r($data,TRUE).'</pre>');
         if($form_state->getErrors()){
            \Drupal::logger('form error')->notice('<pre>program_funding: '.print_r($form_state->getErrors(),TRUE).'</pre>');
         }else{
            \Drupal::logger('create hubspot with loger')->notice('<pre>program_funding: '.print_r($form_state->getErrors(),TRUE).'</pre>');
            $response = $HubspotAPI->saveRecordToHubspot($data,$submition_data['email']);
         }
      }
   }
   /**
   * {@inheritdoc}
   */
   public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
      
      $submition_data = $webform_submission->getData();
      $transaction_id = insertApplicationInfo($submition_data);
      $form_state->setRedirect('srl_common.orderSummary');
   }
   
}