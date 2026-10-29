<?php

namespace Drupal\srl_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Database\Database;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AppendCommand;
use Drupal\Node\Entity\Node;


class SrlCommonController extends ControllerBase{

    public function CandidateFormPost(){
        $output = '';
        $session = \Drupal::request()->getSession();
        $transaction_id = $session->get('transaction_id');

        $records = getAssessmentUserRecord($transaction_id);
        $connection = \Drupal::service('database');
            $query = $connection->update('srl_candidate_test_details')
            ->fields([
            'steps_level' => 2
            ])
            ->condition('transaction_id',$transaction_id, '=')
            ->execute();

        if($records->actual_ended_on != NULL && $records->result == 'skiped'){
            if($records->result == 'passed' || $records->result == 'skiped'){
                $url = \Drupal::request()->getSchemeAndHttpHost();
                
                // $course_name = Node::load($records->course_id)->getTitle();
                /**hubspot lead update after assessment passed */
                // $leadData['email'] = $records->email_id;
                // $leadData['status'] = APPLICATION_PASS_LEAD;
                // $leadData['status_level'] = 3;
                // $leadData['journey_type'] = 'Self Service Model';
                // $HubspotAPI = \Drupal::service('api.hubspot');
                // $response = $HubspotAPI->updateLeadStatus($leadData);

                $output.='
                <div class="proceed-to-payment">
                    <div class="panel panel-default">
                        <div class="panel-heading">Skills Inventory Review</div>
                        <div class="panel-body">
                            <div class="verify-application">
                                <div class="loader"></div>
                                <span>Thank you for submitting the application. We are verifying your information, evaluating program requirement\'s.</span>
                            </div>
                            <div class="confirm-application">Congratulations <span class="user-name">'.$records->name.'</span>! - You have been accepted into '.$site_name.' - '.$course_name.'!</div>
                            <div class="media message-card">
                                <div class="media-left">
                                    <img src="/modules/custom/srl_common/images/check-mark.png" class="media-object">
                                </div>
                                <div class="media-body">
                                    <span class="badge pull-right completed">Completed</span>
                                    <h4 class="media-heading">Congratulation <span class="user-name">'.$records->name.'</span>!</h4>
                                    <p>You have successfully completed your course requirement.</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12 text-right">
                                    <form method="post" action="/order-summary">
                                        <input type="hidden" id="transaction_id" name="transaction_id" value="'.$transaction_id.'">
                                        <input type="hidden" id="name" name="name" value="'.$records->name.'">
                                        <input type="hidden" id="email" name="email" value="'.$records->email_id.'">
                                        <input type="hidden" id="phone" name="phone" value="'.$records->mobile_no.'">
                                        <input type="hidden" id="coursecode" name="coursecode" value="'.$records->course_code.'">
                                        <input type="hidden" id="univeristy_code" name="univeristy_code" value="'.$records->university_code.'">
                                        <input type="submit" value ="Proceed to payment">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                ';

            }else{
                /**hubspot lead update after assessment fail */
                // $leadData['email'] = $records->email_id;
                // $leadData['status'] = APPLICATION_FAIL_LEAD;
                // $leadData['status_level'] = 3;
                // $leadData['journey_type'] = 'Self Service Model';
                // $HubspotAPI = \Drupal::service('api.hubspot');
                // $response = $HubspotAPI->updateLeadStatus($leadData);
                $output='<div class="media message-card">
                            <div class="media-left">
                                <img src="/modules/custom/srl_common/images/light-bulb.png" class="media-object">
                            </div>
                            <div class="media-body">
                                <h4 class="media-heading">
                                Thank you for completing your Skills Inventory Review and taking the next step on your student journey.
                                One of our Program Admissions Consultants will be in contact with you regarding next steps.</h4>
                            </div>
                        </div>
              ';

            }
        }elseif($records){
            $batch_code = 'B'.date('dmY',\Drupal::time()->getRequestTime());
            $url_video = getCourseVideoLink($records->course_id);
            $site_name = \Drupal::config('system.site')->get('name');
            $course_name = \Drupal::entityTypeManager()->getStorage('node')->load($records->course_id)->getTitle();
            $config = \Drupal::config('assessment.config');
            $assessment_redirect = $config->get('assessment_nas_url');
            $output='';
            $assessment_start_form =
            '<form id="form-assessment_confirmation" >
                <input type="hidden" id="BU_CD" name="BU_CD" value="'.$records->bu_code.'">
                <input type="hidden" id="SRC_CD" name="SRC_CD" value="'.$records->source_code.'">
                <input type="hidden" id="Organization_ID" name="Organization_ID" value="'.$records->orgid.'">
                <input type="hidden" id="Student_Name" name="Student_Name" value="'.$records->name.'">
                <input type="hidden" id="Student_MName" name="Student_Name" value="">
                <input type="hidden" id="Student_LName" name="Student_Name" value="">
                <input type="hidden" id="Registration_ID" name="Registration_ID" value="'.$transaction_id.'">
                <input type="hidden" id="Batch_Code" name="Batch_Code" value="'.$batch_code.'">
                <input type="hidden" id="Centre_Code" name="Centre_Code" value="'.$records->center_code.'">
                <input type="hidden" id="Country_Code" name="Country_Code" value="'.$records->country_code.'">
                <input type="hidden" id="Appraisal_Code" name="Appraisal_Code" value="'.$records->test_code.'">
                <input type="hidden" id="Start_DateTime" name="Start_DateTime" value="">
                <input type="hidden" id="End_DateTime" name="End_DateTime" value="">
                <input type="hidden" id="Test_Type" name="Test_Type" value="'.$records->test_type.'">
                <input type="hidden" id="Evaluation_Start_DateTime" name="Evaluation_Start_DateTime" value="">
                <input type="hidden" id="Evaluation_End_DateTime" name="Evaluation_End_DateTime" value="">
                <input type="hidden" id="Topic_Desc" name="Topic_Desc" value="">
                <input type="hidden" id="Sub_Topic_Desc" name="Sub_Topic_Desc" value="">
                <input type="hidden" id="Reattempt_Flag" name="Reattempt_Flag" value="N">
                <input type="hidden" id="proctoringParams" name="proctoringParams" value="">
                <input type="hidden" id="Application_Transaction_ID" name="Application_Transaction_ID" value="'.$transaction_id.'">
                <input type="hidden" id="IS_JWT_REQUIRED" name="IS_JWT_REQUIRED" value="">
                <input id="assmentStart" class="panel-btn" type="button" value="Start Test" onclick="startAssessment(\''.$transaction_id.'\',\''.$assessment_redirect.'\')" transaction_id="'.$transaction_id.'">
            </form>';
            $hide_section = 'd-none';
            $show_section = '';
            $jquery_update = 'timer_update';

            if(isset($_COOKIE[$transaction_id])){
                $hide_section = 'd-block';
                $show_section = 'd-none';
                $jquery_update = '';
            }
            $output.='
            <div class="application-confirmation '.$jquery_update.' '.$show_section.'">
                <div class="media message-card">
                    <div class="media-left verify-application">
                        <div class="loader"></div>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">Thank you for submitting the application. We are verifying your information, evaluating program requirement\'s.</h4>
                    </div>
                </div>
            </div>
            <div class="application-confirmed '.$jquery_update.' '.$hide_section.'">
                <div class="media message-card">
                    <div class="media-left">
                        <img src="/modules/custom/srl_common/images/check-mark.png" class="media-object">
                    </div>
                    <div class="media-body">
                        <h4>
                        Congratulations <span class="user-name">'.$records->name.'</span>! - You have been accepted into '.$site_name.' - '.$course_name.'!
                        </h4>
                    </div>
                </div>
            </div>
            ';


            setcookie($transaction_id, $transaction_id, \Drupal::time()->getRequestTime() + (1800), "/"); // 86400 = 1 day
            $assessment_srart = '
            <div class="panel panel-default">
                <div class="panel-heading">Skills Inventory Review</div>
                <div class="panel-body">

                    <h3>While you start the Skills Inventory Review, Kindly take note of the following</h3>
                    <p><strong>Skills Inventory:</strong> The assessment is designed to show off your problem-solving abilities. It is multiple-choice and you do not need any prior experience. In fact, feel free to use available resources such as Google (recommended) to help you if you get stuck.</p>
                    <p>We advise you to take the assessment on a laptop or desktop computer as having multiple tabs open at once will be helpful. Take it in a location where you are free from any distractions. Most importantly, don\'t stress and have fun!</p>
                    <div class="media message-card result-awaited" style="display:none;">
                        <div class="media-body">
                            <div class="text-left"><img src="/themes/aubum/images/default_images/awaited.gif" class="result-aw-img">
                                <h4 class="media-heading"> Thank You for Completing your Skills Inventory Review</h4>
                                <p>Please wait while we process your results</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    <div class="col-sm-12 text-right">
                        '.$assessment_start_form.'
                    </div>
                    </div>
                </div>
            </div>
            ';
            if($url_video[1]){
                $output.='<div id="course-video-container">
                    <input id="skipvideo" class="panel-btn" type="button" value="Skip video & Proceed to payment" onclick="skipvideo()">
                    <iframe id="player" src="'.$url_video[1].'" width="100%" height="500px" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                    <input id="skipvideo" class="panel-btn" type="button" value="Skip video & Proceed to payment" onclick="skipvideo()">
                </div>';
                $output.='<div id="default-assessment-section" style="display:none;">'.$assessment_srart.'</div>';
            }else{
                $output.= $assessment_srart;
            }


        }
        $output_html = '<div id="assessment_confirmation">'.$output.'</div>';
        return [
            '#theme' => 'assessment_application',
            '#assessment_html' => $output_html,
            '#assessment_video' => $url_video[0],
            '#cache' => ['max-age' => 0],
        ];
    }
    public function PageThankYou(){
        $session = \Drupal::request()->getSession();
        $domain = \Drupal::service('domain.negotiator')->getActiveId();

        $transaction_id = $session->get('transaction_id');
        $site_name = \Drupal::config('system.site')->get('name');
        $university_config = \Drupal::config('university_info.config');
        $records = getAssessmentUserRecord($transaction_id);
        
        $course_name = \Drupal::entityTypeManager()->getStorage('node')->load($records->course_id)->getTitle();
        $course_fee = $this->getCoursefee($domain,$records->course_id);
        $discount_fee_amount = $this->getdiscountfee($domain,$records->course_id);
        $connection = \Drupal::service('database');
            $query = $connection->update('srl_candidate_test_details')
            ->fields([
            'steps_level' => 2
            ])
            ->condition('transaction_id',$transaction_id, '=')
            ->execute();

        $output = [];
        $date_timestamp[0] = strtotime($records->batch_start_date);
        $next_date = date('F d',$date_timestamp[0]).'<span>'.date('S',$date_timestamp[0]).'</span> '.date('Y',$date_timestamp[0]);
        $output['user_name'] = $records->name;
        $output['university_course'] = $site_name.':</br>'.$course_name;
        $output['next_date'] = $next_date;
        $output['phone_no'] = $university_config->get($domain.'_phone');
        $output['chatbot_link'] = $university_config->get($domain.'_chatbot_link');

        
        /** confirmation process */
        $hide_section = 'd-none';
        $show_section = '';
        $jquery_update = 'timer_update';

        if(isset($_COOKIE[$transaction_id])){
            $hide_section = 'd-block';
            $show_section = 'd-none';
            $jquery_update = '';
        }
        /**save student payment information */
        $application_field_data = json_decode(stripcslashes($records->json_response));
        $student_info = [
            'transaction_id' => $transaction_id,
            'student_name' => $records->name,
            'email_id' => $application_field_data->email,
            'mobile_no' => $records->mobile_no,
            'course_id' => $application_field_data->program_interest,
            'CountryCode' => $application_field_data->country,
            'MobileNo' => $application_field_data->phone,
            'domain' => $domain,
            'course_name' => $course_name,
            'discount_fee_amount' => $discount_fee_amount,
            'currency_code' => 'USD',
            'start_date' => $records->batch_start_date,
            'actual_fee' => $course_fee,
            'total_amount' => $course_fee,
            'zip' => $application_field_data->zip
        ];
        $this->savePayment_information($student_info);
        $payment_variable = [
            'domain' => $domain,
            'school_id' => $university_config->get($domain.'_climb_credit_schoolID'),
            'campus' => $university_config->get($domain.'_climb_credit_campus_name'),
            'cc_program_name' => $university_config->get($domain.'_climb_credit_campus_name'),
            'batch_date' => $records->batch_start_date,
            'course_fee' => $course_fee,
            'discount_fee_amount' => $discount_fee_amount,
            'transaction_id' => $transaction_id,
            'course_id' => $records->course_id,
            'course_code' => $records->course_code,
            'result' => $records,
        ];
        $discounted_amount = 0;
        if($application_field_data->how_do_you_plan_on_funding_your_bootcamp == 'Pay in full up-front'){
            $discounted_amount = $discount_fee_amount ;
            
            // if($domain == 'srl_qc_niit_mts_com' || $domain == 'srl_adelphi_qc_niit_mts_com' || $domain == 'adelphi_landing_university' || $domain == 'srl_pointpark_qc_niit_mts_com' || $domain == 'point_park_university' || $domain == 'stackroute_suffolk_university'){
            //     $discounted_amount = 3000;
            //     } else{
            //         $discounted_amount = 1000 ;
            //     }
        }
        $university_TOS = getUniversityTOS($domain); 
        $paymentform = \Drupal::formBuilder()->getForm('\Drupal\srl_common\Form\ProceedPaymentForm',$payment_variable,$university_TOS);
        $output['jquery_update'] = $jquery_update; 
        $output['show_section'] = $show_section;
        $output['hide_section'] = $hide_section;
        $output['proceed_payment_form'] = $paymentform;
        $output['student_name'] = $records->name;
        $output['email_id'] = $application_field_data->email;
        $output['course_name'] = $course_name;
        $output['mobile_no'] = '+'. (int) filter_var($application_field_data->country, FILTER_SANITIZE_NUMBER_INT).'-'. $application_field_data->phone;
        $output['course_fee'] = $course_fee;
        $output['discouted_amount'] = $discounted_amount;
        $output['actual_course_fee'] = $course_fee - $discounted_amount;
        
        setcookie($transaction_id, $transaction_id, \Drupal::time()->getRequestTime() + (1800), "/"); // 86400 = 1 day



        return [
            '#theme' => 'page_thank_you',
            '#page_html' => $output,
            '#cache' => ['max-age' => 0],
        ];

    }
    /** 
     * landing page
     **/
    public function landingPage(){
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $site_name = \Drupal::config('system.site')->get('name');
        $university_config = \Drupal::config('university_info.config');
        $properties = [
            'type' => 'landing_pages',
            'field_landing_page_type' => 'landing_page',
            'field_domain_access.target_id' => $domain,
        ];
        $output['site_name'] = $site_name;
        $output['phone_no'] = $university_config->get($domain.'_phone');
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties($properties);
        foreach($nodes as $key => $values){
            if(!empty($values->get('field_banner_image')->getValue()[0]['target_id'])){
                $file = \Drupal\file\Entity\File::load($values->get('field_banner_image')->getValue()[0]['target_id']);
               $output['banner_src'] = file_url_transform_relative(file_create_url($file->getFileUri()));
            }
            $output['description'] = $values->get('field_banner_description')->getValue()[0]['value'];
            $paragraph = $values->get('field_page_section_content')->getValue();
        }
        $page_content = '';
        foreach ( $paragraph as $element ) {
            $content = \Drupal\paragraphs\Entity\Paragraph::load( $element['target_id'] );
            $page_content.= $content->field_section_content->getValue()[0]['value'];
          }
        $output['lead_form'] = \Drupal::entityTypeManager()->getStorage('webform')->load('landing_page_lead_form')->getSubmissionForm();
        $output['paragraph'] = $page_content;
        return [
            '#theme' => 'landing_page',
            '#page_html' => $output,
            '#cache' => ['max-age' => 0],
        ];
    }

    /** 
     * Application Form landing page
     **/
    public function applicationformlandingPage(){
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $site_name = \Drupal::config('system.site')->get('name');
        $university_config = \Drupal::config('university_info.config');
        $properties = [
            'type' => 'landing_pages',
            'field_landing_page_type' => 'landing_application_form',
            'field_domain_access.target_id' => $domain,
        ];
        $output['site_name'] = $site_name;
        $output['phone_no'] = $university_config->get($domain.'_phone');
        $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties($properties);
        foreach($nodes as $key => $values){
            if(!empty($values->get('field_banner_image')->getValue()[0]['target_id'])){
                $file = \Drupal\file\Entity\File::load($values->get('field_banner_image')->getValue()[0]['target_id']);
               $output['banner_src'] = file_url_transform_relative(file_create_url($file->getFileUri()));
            }
            $output['description'] = $values->get('field_banner_description')->getValue()[0]['value'];
            $paragraph = $values->get('field_page_section_content')->getValue();
        }
        $page_content = '';
        foreach ( $paragraph as $element ) {
            $content = \Drupal\paragraphs\Entity\Paragraph::load( $element['target_id'] );
            $page_content.= $content->field_section_content->getValue()[0]['value'];
          }
        $output['lead_form'] = \Drupal::entityTypeManager()->getStorage('webform')->load('landing_page_lead_form')->getSubmissionForm();
        $output['paragraph'] = $page_content;
        return [
            '#theme' => 'landing_pages',
            '#page_html' => $output,
            '#cache' => ['max-age' => 0],
        ];
    }
    /**
     * Landing page thankyou 
     * */
    public function landingPage_thankyou(){
        $session = \Drupal::request()->getSession();
        $site_name = \Drupal::config('system.site')->get('name');
        $domain = \Drupal::service('domain.negotiator')->getActiveId();

        $transaction_id = $session->get('transaction_id');
        $university_config = \Drupal::config('university_info.config');
        $records = getAssessmentUserRecord($transaction_id);
        if($records->course_id){
            $course_name = \Drupal::entityTypeManager()->getStorage('node')->load($records->course_id)->getTitle();
        }
        $course_fee = $this->getCoursefee($domain,$records->course_id);
        $discount_fee_amount = $this->getdiscountfee($domain,$records->course_id);
        // $upfront_schlor_url = $this->getupfront_schlor_url($domain,$records->course_id);
        // $finance_schlor_url = $this->getfinance_schlor_url($domain,$records->course_id);
        $scholarships_amount = $this->getscholarships_amount($domain,$records->course_id);
        
        
        
        $connection = \Drupal::service('database');
            $query = $connection->update('srl_candidate_test_details')
            ->fields([
            'steps_level' => 2
            ])
            ->condition('transaction_id',$transaction_id, '=')
            ->execute();

        $output = [];
        $date_timestamp[0] = strtotime($records->batch_start_date);
        $next_date = date('F d',$date_timestamp[0]).'<span>'.date('S',$date_timestamp[0]).'</span> '.date('Y',$date_timestamp[0]);
        $next_updatedate = date('F j',$date_timestamp[0]).'<span>'.date('S',$date_timestamp[0]).'</span> '.date('Y',$date_timestamp[0]);

        $output['user_name'] = $records->name;
        $output['university_course'] = $site_name.':</br>'.$course_name;
        $output['next_date'] = $next_date;
        $output['next_updatedate'] = $next_updatedate;
        $output['chatbot_link'] = $university_config->get($domain.'_chatbot_link');
        $output['financing_scholarship_url'] = $university_config->get($domain.'_financing_scholarship_url');
        $output['fullpay_scholarship_url'] = $university_config->get($domain.'_fullpay_scholarship_url');
        $output['scholarships_amount'] = $scholarships_amount;
        /**This is fullpay and finance schlorship url for each course wise */
        // $output['upfront_schlor_url'] = $upfront_schlor_url;
        // $output['finance_schlor_url'] = $finance_schlor_url;
      
        
        
      
      //  $output['update_discounted_amount'] = 1000;
      $output['update_discounted_amount'] = $discount_fee_amount;
        // if($domain == 'srl_qc_niit_mts_com' || $domain == 'srl_adelphi_qc_niit_mts_com' || $domain == 'adelphi_landing_university' || $domain == 'srl_pointpark_qc_niit_mts_com' || $domain == 'point_park_university' || $domain == 'stackroute_suffolk_university'){
        //   $output['update_discounted_amount'] = 3000;
        // } else{
        //         $output['update_discounted_amount'] = 1000;
        //     }
 
        /**save student payment information */
        $application_field_data = json_decode(stripcslashes($records->json_response));
        $student_info = [
            'transaction_id' => $transaction_id,
            'student_name' => $records->name,
            'email_id' => $application_field_data->email,
            'mobile_no' => $records->mobile_no,
            'course_id' => $application_field_data->program_interest,
            'CountryCode' => $application_field_data->country,
            'MobileNo' => $application_field_data->phone,
            'domain' => $domain,
            'course_name' => $course_name,
            'currency_code' => 'USD',
            'start_date' => $records->batch_start_date,
            'actual_fee' => $course_fee,
            'discount_fee_amount' => $discount_fee_amount,
            'total_amount' => $course_fee,
            'zip' => $application_field_data->zip
        ];
        $this->savePayment_information($student_info);
       
        $payment_variable = [
            'domain' => $domain,
            'school_id' => $university_config->get($domain.'_climb_credit_schoolID'),
            'campus' => $university_config->get($domain.'_climb_credit_campus_name'),
            'cc_program_name' => $university_config->get($domain.'_climb_credit_campus_name'),
            'batch_date' => $records->batch_start_date,
            'course_fee' => $course_fee,
            'discount_fee_amount' => $discount_fee_amount,
            'transaction_id' => $transaction_id,
            'course_id' => $records->course_id,
            'course_code' => $records->course_code,
            'result' => $records,
        ];
        
        $university_TOS = getUniversityTOS($domain); 
        $payment_variable['clientID'] = $university_TOS['clientID'];
        $paymentform = \Drupal::formBuilder()->getForm('\Drupal\srl_common\Form\LandingPagePaymentForm',$payment_variable);
        $output['payment_type'] = $application_field_data->how_do_you_plan_on_funding_your_bootcamp;
        $output['proceed_payment_form'] = $paymentform;
       
        $output['site_name'] = $site_name;

        return [
            '#theme' => 'landing_page_thank_you',
            '#page_html' => $output,
            '#cache' => ['max-age' => 0],
        ];

    }
    /** ajax callback to update assessment status */
    public function assessmentUpdate(){
        $requestField = \Drupal::request()->request;
        $transaction_id = $requestField->get('transaction_id');
        $connection = \Drupal::service('database');
            $query = $connection->update('srl_candidate_test_details')
                ->fields([
                'assessment_started_on' => \Drupal::time()->getRequestTime()
                ])
                ->condition('transaction_id', $transaction_id, '=')
                ->execute();
        $result = ['data' => 'success'];
        return new JsonResponse($result);
    }
    /** check redirection on payment if test completed */
    public function assessment_getresult(Request $request){
        $requestField = \Drupal::request()->request;
        $transaction_id = \Drupal::request()->query->get('transaction_id');
        // $transaction_id = $requestField->get('transaction_id');

        $query = db_select('srl_candidate_test_details', 'records');
        $query->fields('records',array('actual_ended_on','test_score'));
        $query->condition('records.transaction_id',$transaction_id);
        $field_values=$query->execute()->fetchAll();
        if($field_values[0]->test_score){
            $response['score'] = $field_values[0]->test_score;
            return new JsonResponse( $response );
        }else{
            $response['score'] = NULL;
            return new JsonResponse( $response );
        }

    }
    public function applicationFormCallbackNew(){

        $webform = \Drupal::entityTypeManager()->getStorage('webform')->load('new_application_form')->getSubmissionForm();

        return [
            '#theme' => 'assessment_new_application_form',
            '#form_html' => $webform,
            '#cache' => array(
                'max-age' => 0,
            ),
        ];
    }
    public function removeResumeSession(Request $request){

        $session = \Drupal::request()->getSession();
        $session->remove('temp_transaction_id_resume');
        $response['score'] = 'removed';
        return new JsonResponse( $response );

    }
    public function resumeApplicationFormBuild(){
        $resumeApplicationForm = \Drupal::formBuilder()->getForm('\Drupal\srl_common\Form\ResumeApplicationForm');
        $ajax_response = new AjaxResponse();
        $ajax_response->addCommand(new AppendCommand('#build-resumeapplication', $resumeApplicationForm));
        return $ajax_response;
    }
    public function getCoursefee($domain,$course_id){
        $query = db_select('node__field_actualfee', 'fee');
        $query->leftjoin('node__field_course_code_reference','cr','fee.entity_id=cr.entity_id');
        $query->leftjoin('node__field_domain_access','domain','cr.entity_id=domain.entity_id');
        $query->fields('fee',array('field_actualfee_value'));
        $query->condition('fee.bundle','course_university_mapping');
        $query->condition('cr.field_course_code_reference_target_id',$course_id);
        $query->condition('domain.field_domain_access_target_id',$domain);
        $field_values=$query->execute()->fetchAll();
        if($field_values[0]){
            return $field_values[0]->field_actualfee_value;
        }
    }
    public function getdiscountfee($domain,$course_id){
        $query = db_select('node__field_discount_fees', 'disfee');
        $query->leftjoin('node__field_course_code_reference','cr','disfee.entity_id=cr.entity_id');
        $query->leftjoin('node__field_domain_access','domain','cr.entity_id=domain.entity_id');
        $query->fields('disfee',array('field_discount_fees_value'));
        $query->condition('disfee.bundle','course_university_mapping');
        $query->condition('cr.field_course_code_reference_target_id',$course_id);
        $query->condition('domain.field_domain_access_target_id',$domain);
        $field_values=$query->execute()->fetchAll();
        if($field_values[0]){
            return $field_values[0]->field_discount_fees_value;
        }
        
    }
 
    
    public function getupfront_schlor_url($domain,$coursereference_id){
        $query = db_select('node__field_upfront_scholarship_url', 'upfrontschlor');
        $query->leftjoin('node__field_course_code_reference','cr','upfrontschlor.entity_id=cr.entity_id');
        $query->leftjoin('node__field_domain_access','domain','cr.entity_id=domain.entity_id');
        $query->fields('upfrontschlor',array('field_upfront_scholarship_url_value'));
        $query->condition('upfrontschlor.bundle','course_university_mapping');
        $query->condition('cr.field_course_code_reference_target_id',$coursereference_id);
        $query->condition('domain.field_domain_access_target_id',$domain);
        $field_values=$query->execute()->fetchAll();
        if($field_values[0]){
            return $field_values[0]->field_upfront_scholarship_url_value;
        }
        
      }
      
      public function getfinance_schlor_url($domain,$coursereference_id){
        $query = db_select('node__field_finance_scholarship_url', 'financeschlor');
        $query->leftjoin('node__field_course_code_reference','cr','financeschlor.entity_id=cr.entity_id');
        $query->leftjoin('node__field_domain_access','domain','cr.entity_id=domain.entity_id');
        $query->fields('financeschlor',array('field_finance_scholarship_url_value'));
        $query->condition('financeschlor.bundle','course_university_mapping');
        $query->condition('cr.field_course_code_reference_target_id',$coursereference_id);
        $query->condition('domain.field_domain_access_target_id',$domain);
        $field_values=$query->execute()->fetchAll();
        if($field_values[0]){
            return $field_values[0]->field_finance_scholarship_url_value;
        }
        
      }

      public function getscholarships_amount($domain,$coursereference_id){
        $query = db_select('node__field_scholarships_amount', 'scholarships_amount');
        $query->leftjoin('node__field_course_code_reference','cr','scholarships_amount.entity_id=cr.entity_id');
        $query->leftjoin('node__field_domain_access','domain','cr.entity_id=domain.entity_id');
        $query->fields('scholarships_amount',array('field_scholarships_amount_value'));
        $query->condition('scholarships_amount.bundle','course_university_mapping');
        $query->condition('cr.field_course_code_reference_target_id',$coursereference_id);
        $query->condition('domain.field_domain_access_target_id',$domain);
        $field_values=$query->execute()->fetchAll();
        if($field_values[0]){
            return $field_values[0]->field_scholarships_amount_value;
        }
        
      }
    public function savePayment_information($student_info){
        $database = \Drupal::database();
        $payment = $database->select('payeezy_payment_details', 'detail');
        $payment->fields('detail', ['transaction_id']);
        $payment->condition('detail.transaction_id',$student_info['transaction_id']);
        $payment_detail = $payment->execute()->fetchAll();
        
        if(!$payment_detail){
            
            db_insert('payeezy_payment_details')
            ->fields(
                [
                'transaction_id' => $student_info['transaction_id'],
                'user_name' => $student_info['student_name'],
                'email_id' => $student_info['email_id'],
                'mobile_no' => $student_info['mobile_no'],
                'course_code' => $student_info['course_id'],
                'university' => $student_info['domain'],
                'created_on' => \Drupal::time()->getRequestTime(),
                'course_name' => $student_info['course_name'],
                'delivery_mode' => 'online',
                'currency_code' => $student_info['currency_code'],
                'cohort_date' => $student_info['start_date'],
                'base_amount' => $student_info['actual_fee'],
                'total_amount' => $student_info['total_amount'],
                'zip_code' => $student_info['zip']
                ]
            )->execute();
        }
    }

}
