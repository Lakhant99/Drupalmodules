<?php

namespace Drupal\srl_common\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\Component\Utility\Html;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Webform validate handler.
 *
 * @WebformHandler(
 *   id = "custom_webform_validate_handler",
 *   label = @Translation("Webform custom validation"),
 *   category = @Translation("Settings"),
 *   description = @Translation("Form alter to validate it."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class webformValidation extends WebformHandlerBase {

   /**
   * {@inheritdoc}
   */
   public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
      
      $submition_data = $webform_submission->getData();
      $HubspotAPI = \Drupal::service('api.hubspot');
      if($form_state->get('current_page') == 'user_info'){
         
         $data = [
            'step' => 'user_info',
            'program_interest' => $submition_data['program_interest'],
            'naame' => $submition_data['naame'],
            'last_name' => $submition_data['last_name'],
            'email' => $submition_data['email'],
            'country' => $submition_data['country'],
            'phone' => $submition_data['phone'],
            'zip' => $submition_data['zip'],
            'gclid' => $submition_data['gclid'],
            'course_name' => $submition_data['course_name'],
            'utm_found' => FALSE,
         ];
         if($submition_data['utm_source'] || $submition_data['utm_campaign'] || $submition_data['utm_medium'] || $submition_data['utm_term'] || $submition_data['utm_content']){
            $data['utm_source'] = $submition_data['utm_source'];
            $data['utm_campaign'] = $submition_data['utm_campaign'];
            $data['utm_medium'] = $submition_data['utm_medium'];
            $data['utm_term'] = $submition_data['utm_term'];
            $data['utm_content'] = $submition_data['utm_content'];
            $data['utm_found'] = TRUE;
         }
         /**reset steps*/
         resetResumeApplicationSteps($data);
         insertWebformTempData($data);
         $connection = \Drupal::service('database');
         $response = $HubspotAPI->updateApplication_user_info($data);
      }elseif($form_state->get('current_page') == 'your_program_goals'){
         $data = [
            'step' => 'your_program_goals',
            'email' => $submition_data['email'],
            'country' => $submition_data['country'],
            'phone' => $submition_data['phone'],
            'program_interest' => $submition_data['program_interest'],
            'motivation_to_do_program' => $submition_data['motivation_to_do_program'],
            'motivation_to_do_program_note' => $submition_data['motivation_to_do_program_note'],
            // 'motivation_to_do_program_note_data' => $submition_data['motivation_to_do_program_note_data'],
            'greatest_need_in_program' => $submition_data['greatest_need_in_program'],
            'greatest_need_in_program_note' => $submition_data['greatest_need_in_program_note'],
            // 'greatest_need_in_program_note_data' => $submition_data['greatest_need_in_program_note_data'],
            'you_hope_to_do_upon_completing' => $submition_data['you_hope_to_do_upon_completing'],
            'you_hope_to_do_upon_completing_note' => $submition_data['you_hope_to_do_upon_completing_note'],
            // 'you_hope_to_do_upon_completing_note_data' => $submition_data['you_hope_to_do_upon_completing_note_data'],
         ];
         insertWebformTempData($data);
         // $response = $HubspotAPI->updateApplication_programgoals($data);
      }elseif($form_state->get('current_page') == 'your_educational_background'){
         $data = [
            'step' => 'your_educational_background',
            'email' => $submition_data['email'],
            'country' => $submition_data['country'],
            'phone' => $submition_data['phone'],
            'program_interest' => $submition_data['program_interest'],
            'highest_level_of_education' => $submition_data['highest_level_of_education'],
            'highest_level_of_education_note' => $submition_data['highest_level_of_education_note'],
            // 'highest_level_of_education_note_data' => $submition_data['highest_level_of_education_note_data'],
            'online_learning_experience' => $submition_data['online_learning_experience'],
            'online_learning_experience_note' => $submition_data['online_learning_experience_note'],
            // 'online_learning_experience_note_data' => $submition_data['online_learning_experience_note_data'],
            'previous_courses_in_technology' => $submition_data['previous_courses_in_technology'],
            'previous_courses_in_technology_note' => $submition_data['previous_courses_in_technology_note'],
            // 'previous_courses_in_technology_note_data' => $submition_data['previous_courses_in_technology_note_data'],
         ];
         insertWebformTempData($data);
         // $response = $HubspotAPI->updateApplication_educationalbackground($data);
      }elseif($form_state->get('current_page') == 'your_current_employment_status'){
         $data = [
            'step' => 'your_current_employment_status',
            'email' => $submition_data['email'],
            'country' => $submition_data['country'],
            'phone' => $submition_data['phone'],
            'program_interest' => $submition_data['program_interest'],
            'are_you_currently_employed' => $submition_data['are_you_currently_employed'],
            'are_you_currently_employed_note' => $submition_data['are_you_currently_employed_note'],
            // 'are_you_currently_employed_note_data' => $submition_data['are_you_currently_employed_note_data'],
            'work_schedule' => $submition_data['work_schedule'],
            'work_schedule_note' => $submition_data['work_schedule_note'],
            // 'work_schedule_note_data' => $submition_data['work_schedule_note_data'],
            'hours_per_week_are_you_currently_working' => $submition_data['hours_per_week_are_you_currently_working'],
            'hours_per_week_are_you_currently_working_note' => $submition_data['hours_per_week_are_you_currently_working_note'],
            // 'hours_per_week_are_you_currently_working_note_data' => $submition_data['hours_per_week_are_you_currently_working_note_data'],
            'years_work_experience' => $submition_data['years_work_experience'],
            'years_work_experience_note' => $submition_data['years_work_experience_note'],
            // 'years_work_experience_note_data' => $submition_data['years_work_experience_note_data'],
            'working_with_digital_technologies' => $submition_data['working_with_digital_technologies'],
            'working_with_digital_technologies_note' => $submition_data['working_with_digital_technologies_note'],
            // 'working_with_digital_technologies_note_data' => $submition_data['working_with_digital_technologies_note_data'],
         ];
         insertWebformTempData($data);
         // $response = $HubspotAPI->updateApplication_currentemployment($data);
      }elseif($form_state->get('current_page') == 'our_overall_career_outlook'){
         $data = [
            'step' => 'our_overall_career_outlook',
            'email' => $submition_data['email'],
            'country' => $submition_data['country'],
            'phone' => $submition_data['phone'],
            'program_interest' => $submition_data['program_interest'],
            'your_current_income_earning_potential' => $submition_data['your_current_income_earning_potential'],
            'your_current_income_earning_potential_note' => $submition_data['your_current_income_earning_potential_note'],
            'the_opportunity_for_career_development' => $submition_data['the_opportunity_for_career_development'],
            'the_opportunity_for_career_development_note' => $submition_data['the_opportunity_for_career_development_note'],
            'you_satisfied_with_your_current_employment' => $submition_data['you_satisfied_with_your_current_employment'],
            'you_satisfied_with_your_current_employment_note' => $submition_data['you_satisfied_with_your_current_employment_note'],
            'you_earn_a_higher_income_and_greater_career_stability' => $submition_data['you_earn_a_higher_income_and_greater_career_stability'],
            'you_earn_a_higher_income_and_greater_career_stability_note' => $submition_data['you_earn_a_higher_income_and_greater_career_stability_note'],
            'new_career_as_a_technology_professional' => $submition_data['new_career_as_a_technology_professional'],
            'new_career_as_a_technology_professional_note' => $submition_data['new_career_as_a_technology_professional_note'],
         ];
         
         $response = $HubspotAPI->updateApplication_overall_career($data);

         
         // $domain = \Drupal::service('domain.negotiator')->getActiveId();
         // $connection = \Drupal::service('database');
         // $connection->delete('stackroute_webform_tempdata')
         //    ->condition('transaction_id', $temp_transaction_id->transaction_id)
         //    ->condition('domain_id', $domain)
         //    ->execute();
      }
   }
}