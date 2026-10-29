<?php

namespace Drupal\srl_common\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Node\Entity\Node;


/**
 * save a test assessment  from a webform submission.
 *
 * @WebformHandler(
 *   id = "Download Course Brochure",
 *   label = @Translation("Download Course Brochure"),
 *   category = @Translation("Download Course Brochure"),
 *   description = @Translation("Download Course Brochure on Webform Submissions."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_UNLIMITED,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_REQUIRED,
 * )
 */

class DownloadBrochure extends WebformHandlerBase {

    /**
     * {@inheritdoc}
     */

    // Function to be fired after submitting the Webform.
    public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
        // Get an array of the values from the submission. 
        
    }
    /**
     * {@inheritdoc}
    */
    public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
        $values = $webform_submission->getData();
        
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $university_config = \Drupal::config('university_info.config');
        $hubspot_university_name = $university_config->get($domain.'_hubspot_university_name'); 
        $site_name = \Drupal::config('system.site')->get('name');
         
       
        $query = db_select('node__field_course_brochure','CB');
        $query->fields('CB',array('field_course_brochure_target_id'));
        $query->leftjoin('node__field_course_code_reference','cr','CB.entity_id=cr.entity_id');
        $query->leftjoin('node__field_domain_access','domain','CB.entity_id=domain.entity_id');
        $query->condition('cr.bundle','course_university_mapping');
        $query->condition('cr.field_course_code_reference_target_id',$values['course_id']);
        $query->condition('domain.field_domain_access_target_id',$domain);
        $result = $query->execute()->fetchAll();
        if(isset($result[0]->field_course_brochure_target_id)){
            $file = file::load($result[0]->field_course_brochure_target_id);
            $uri = $file->getFileUri();
            $headers = array(
                'Content-Type'     => $file->getMimeType(),
                'Content-Disposition' => 'attachment;filename="'.$file->getFilename().'"'
            );
            
            $leadData['firstname'] = $values['first_name'];
            $leadData['lastname'] = $values['last_name'];
            $leadData['email'] = $values['email'];
            $leadData['phone'] = '+'.(int) filter_var($values['country'], FILTER_SANITIZE_NUMBER_INT).' '.$values['phone_number'];
            $leadData['zip'] = $values['city'];
            
            if($values['utm_source'] || $values['utm_campaign'] || $values['utm_medium'] || $values['utm_term'] || $values['utm_content']){
                $leadData['utm_source'] = $values['utm_source'];
                $leadData['utm_campaign'] = $values['utm_campaign'];
                $leadData['utm_medium'] = $values['utm_medium'];
                $leadData['utm_term'] = $values['utm_term'];
                $leadData['utm_content'] = $values['utm_content'];
            }
            $leadData['gclid'] = $values['gclid'];
            $leadData['program'] = $values['program'];
            $leadData['university'] = $hubspot_university_name;
            $leadData['do_you_have_a_high_school_diploma_or_ged_equivalent_'] = $values['do_you_have_a_high_school_diploma_or_ged_equivalent'];
            $leadData['how_do_you_plan_on_funding_your_bootcamp_'] = $values['do_you_anticipate_needing_payment_assistance'];
            $leadData['are_you_18_or_older_'] = $values['are_you_18_or_older'];
            $HubspotAPI = \Drupal::service('api.hubspotupdate');
            $response = $HubspotAPI->updateBrochureContactStatus($leadData,$values['email']);
            $form_state->setResponse(new BinaryFileResponse($uri, 200, $headers, true));
        }
    }

    public function confirmForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
       
    }
}
