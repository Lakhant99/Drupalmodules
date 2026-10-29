<?php

namespace Drupal\srl_common\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * save a test assessment  from a webform submission.
 *
 * @WebformHandler(
 *   id = "resume application form",
 *   label = @Translation("resume application form"),
 *   category = @Translation("resume form submition"),
 *   description = @Translation("resume application field form."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class ResumeApplicationValidation extends WebformHandlerBase {

   /**
   * {@inheritdoc}
   */
   public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
      $values = $webform_submission->getData();
      $domain = \Drupal::service('domain.negotiator')->getActiveId();
      
      if($values['emailid']){
         if (filter_var($values['emailid'], FILTER_VALIDATE_EMAIL)) {
            $emailsubmit = TRUE;
         }else{
            $emailsubmit = FALSE;
            $form_state->setErrorByName('emailid', $this->t('Please enter valid email.'));
            $form['elements']['email']['error'] = [
               '#type' => 'markup',
               '#markup' => '<div class="requireErrorClass email-error">Invalid Email id</div>'
             ];
         }
      }else{
         $form['elements']['required'] = [
            '#type' => 'markup',
            '#markup' => '<div class="requireErrorClass all-require">Please fill all the required fields (*)</div>'
          ];
      }
      if($values['phone_no']){
         $validate = phoneNumberValidation($values['phone_no']);
         if($validate){
            $phonesubmit = TRUE;
         }else{
            $phonesubmit = FALSE;
            $form['elements']['phone']['p_error'] = [
               '#type' => 'markup',
               '#markup' => '<div class="requireErrorClass error-phone">Invalid phone no</div>'
             ];
            $form_state->setErrorByName('phone_no', $this->t('Please enter valid email.'));
         }

      }else{
         $form['elements']['required'] = [
            '#type' => 'markup',
            '#markup' => '<div class="requireErrorClass all-require">Please fill all the required fields (*)</div>'
          ];
      }
      
      if($emailsubmit && $phonesubmit){
         $phone_number = merge_PhoneWithCountry($values['phone_no'],$values['phone_code']);
         $application = db_select('srl_candidate_test_details', 'detail');
            $application->fields('detail', ['transaction_id','steps_level']);
            $application->condition('detail.email_id',$values['emailid']);
            $application->condition('detail.mobile_no',$phone_number);
            $application->condition('detail.domain_id',$domain);
            $application->condition('detail.steps_level',0,'>');
            $application->condition('detail.form_version',2,'=');
            $application->orderby('detail.id','DESC');
            $application->range(0,1);
            $assessment = $application->execute()->fetchAll();
         if($assessment){
            $session = \Drupal::request()->getSession();
            $session->set('transaction_id', $assessment[0]->transaction_id);
            if($assessment[0]->steps_level == 2){
               $form_state->setValue('route_name','srl_common.CandidateFormPost');
            }elseif($assessment[0]->steps_level == 3){
               $form_state->setValue('route_name','srl_common.CandidateFormPost');
            }else{
               $form_state->setValue('route_name','srl_common.CandidateFormPost');
            }
            $form_state->setValue('application_id',$assessment[0]->transaction_id);
         }else{
            // no record found resume incomplete form
            $resume = db_select('stackroute_webform_tempdata', 'detail');
            $resume->fields('detail', ['transaction_id']);
            $resume->condition('detail.email_id',$values['emailid']);
            $resume->condition('detail.mobile_no',$phone_number);
            $resume->condition('detail.step','program_fit','=');
            $resume->condition('detail.form_version',2,'=');
            $resume->condition('detail.domain_id',$domain);
            $resume->orderby('detail.id','DESC');
            $resume->range(0,1);
            $resume_form = $resume->execute()->fetchCol();
            if($resume_form){
               $session = \Drupal::request()->getSession();
               $session->set('temp_transaction_id_resume', $resume_form[0]);

               // $form_state->setValue('route_name','srl_common.applicationform');
               $form_state->setValue('route_name','srl_common.applicationform');
               $form_state->setValue('application_id',$resume_form[0]);
            }else{
               $form['#attributes'] = ['rebuild' => ['rebuild-error-form']];
               
               unset($form['elements']['email']);
               unset($form['elements']['phone']);
               // $form['actions']['#attributes'] = ['class' => ['d-none']];
               unset($form['actions']);
               $form['elements']['error_html'] = [
                  '#type' => 'markup',
                  '#markup' => '<div class="no-data-found"><img src="/themes/hilbert/images/default_images/no-data.png"><p class="first-p">No Application Found</p><p>Looks like you haven’t filled your application yet</p><p>Want to start an application now?</p><a href="/application-form?no_fapp=nodata" type="button" class="btn-apply" >APPLY NOW</a></div>'
               ];
               
               $form_state->setErrorByName('emailid', $this->t('No match found.'));
               $form_state->setRebuild($rebuild = true);
               
            }
         }
      }
   }
    /**
     * {@inheritdoc}
     */

    // Function to be fired after submitting the Webform.
    public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
        // Get an array of the values from the submission.
        // $values = $webform_submission->getData();
    }
    /**
     * {@inheritdoc} 
    */
    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
      

   }

    public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
       
      $values = $webform_submission->getData();
      if($values['route_name'] && $values['application_id']){
         $values['application_id'] = 'true';
         $url = \Drupal\Core\Url::fromRoute($values['route_name'])->setRouteParameters(array('resume'=> $values['application_id']));
         $form_state->setRedirectUrl($url);

      }
      
     }
}