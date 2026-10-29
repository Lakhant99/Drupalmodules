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
 *   id = "Application form final submit and redirect response",
 *   label = @Translation("Application form submit confirmation and redirect response"),
 *   category = @Translation("setting"),
 *   description = @Translation("Save application information Webform Submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class ApplicationSubmitConfirmation extends WebformHandlerBase {

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
       
        $submition_data = $webform_submission->getData();

        $transaction_id = saveApplicationFormdata($submition_data);
        /** update hubspot assessment status */  
        $leadData['email'] = $submition_data['email'];
        $leadData['status'] = APPLICATION_SUBMIT_LEAD;
        $leadData['status_level'] = 2;
        $leadData['journey_type'] = 'Self Service Model';
        $HubspotAPI = \Drupal::service('api.hubspot');

        $response = $HubspotAPI->updateLeadStatus($leadData);
        
        $form_state->setRedirect('srl_common.CandidateFormPost');

    }
}