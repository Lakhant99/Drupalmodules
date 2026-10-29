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
 *   id = "assessment test details",
 *   label = @Translation("Save application form assessment information"),
 *   category = @Translation("test application information"),
 *   description = @Translation("Save a test assessment info from Webform Submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class CreateAssessmentWebformHandler extends WebformHandlerBase {

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

        $transaction_id = insertApplicationInfo($values);
        /** update hubspot assessment status */  
            $leadData['email'] = $values['email'];
            $leadData['status'] = APPLICATION_SUBMIT_LEAD;
            $leadData['status_level'] = 2;
            $leadData['journey_type'] = 'Self Service Model';
            $HubspotAPI = \Drupal::service('api.hubspot');
            deleteWebformTempData($values);
            $response = $HubspotAPI->updateLeadStatus($leadData);
        
        $form_state->setRedirect('srl_common.CandidateFormPost');

    }
}