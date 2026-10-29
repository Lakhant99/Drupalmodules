<?php

namespace Drupal\hubspot_modification;

use Drupal\Core\Session\AccountProxy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HubspotCustomServices{

    /**
     * hubspot search API  get contact id by email 
     * */
    public function GetContactID($data){
        $contact = [];
        \Drupal::logger('hubspot API call')->notice('<pre>HubspotCustomServices: '.print_r($data,TRUE).'</pre>');
        $body = array(
            'filterGroups' => [
                array(
                    'filters' => [array(
                        'value' => $data['email'],
                        'propertyName' => 'email',
                        'operator' => 'EQ'
                    )],
                ),
            ],
            'properties' => ["id","hs_lead_status"],
            'limit' => 1,
            'after' => 0
        );
        $client_url = $data['api_url'].'?hapikey='.$data['api_key'];
        try{
            $client = \Drupal::httpClient();
            $request_search = $client->post($client_url, [
                'headers' => ['Content-Type' => 'application/json',],
                'body' => json_encode($body),
            ])->getBody()->getContents();
            $result = json_decode($request_search);
        }catch(\Exception $e) {
            \Drupal::logger('hubspot services')->notice('<pre> '.print_r($e->getMessage(),TRUE).'</pre>');
        }
        $lead_level = 1;
        if($result->results[0]){
            $hubspot_status = $result->results[0]->properties->hs_lead_status;
            if($hubspot_status == 'Lead'){
                $lead_level = 1;
            }elseif($hubspot_status == 'Partial Applicant'){
                $lead_level = 2;
            }elseif($hubspot_status == 'Accepted'){
                $lead_level = 3;
            }elseif($hubspot_status == 'Failed Assesment' || $hubspot_status == 'Post Assessment/Accepted'){
                $lead_level = 4;
            }elseif($hubspot_status == 'Paid via Credit Card' || $hubspot_status == 'Paid via Installment Plan'){
                $lead_level = 5;
            }elseif($hubspot_status == 'Paid'){
                $lead_level = 6;
            }

            $contact['id'] = $result->results[0]->id;
            $contact['lead_status'] = $lead_level;
            $content['lead_value'] = $hubspot_status;
        }
        return $contact;
    }
    /**
     * Update hubspot contact status 
     */
    public function updateLeadStatus($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            if(array_key_exists('pdf_url',$leadData)){
                $body = array(
                    'properties' => array(
                        'hs_lead_status' => $leadData['status'],
                        'journey_type' => $leadData['journey_type'],
                        'enrollment_agreement' => $leadData['pdf_url']
                    )
                );
            }else{
                $body = array(
                    'properties' => array(
                        'hs_lead_status' => $leadData['status'],
                        'journey_type' => $leadData['journey_type']
                    )
                );
            }
            if($leadData['status_level'] >= $contactID['lead_status']){
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }
            
        }
        return $contactID['id'];
    }
    /**
     * Update hubspot payment status status
     */
    public function updateHubspotPyamentStatus($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            if(array_key_exists('pdf_url',$leadData)){
                $body = array(
                    'properties' => array(
                        'enrollment_agreement' => $leadData['pdf_url'],
                        'term_of_use' => $leadData['term_of_use_pdf']
                    )
                );
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }
        }
        return $contactID['id'];
    }
    /**Download pdf update */
    public function update_PDF_status($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client = \Drupal::httpClient();
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            $lead_value = 'Lead';
            $journey_type = 'High Touch Model';
            if($contactID['lead_status'] > 1){
                $lead_value = $contactID['lead_value'];
                $journey_type = 'Self Service Model';
            }
            $body = array(
                'properties' => array(
                    'firstname' => $leadData['firstname'],
                    'lastname' => $leadData['lastname'],
                    'email' => $leadData['email'],
                    'phone' => $leadData['phone'],
                    'hs_lead_status' => $lead_value,
                    'journey_type' => $journey_type,
                    'program' => $leadData['program'],
                    'zip' => $leadData['zip'],
                    'university' => $leadData['university'],
                )
            );
            if($leadData["utm_found"]){
                
                $body['properties']['utm_source'] = $leadData['utm_source'];
                $body['properties']['utm_campaign'] = $leadData['utm_campaign'];
                $body['properties']['utm_medium'] = $leadData['utm_medium'];
                $body['properties']['utm_term'] = $leadData['utm_term'];
                $body['properties']['utm_content'] = $leadData['utm_content'];
            }
            try{
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> Download brochure'.print_r($body,TRUE).'</pre>');
            }
            // \Drupal::logger('hubspot lead update')->notice('<pre> '.print_r($leadData,TRUE).'</pre>');
        }else{
            $client_url = $config->get('hubspot_update_api').'?hapikey='.$config->get('hubspot_api_key');
            $body = array(
                'properties' => array(
                    'firstname' => $leadData['firstname'],
                    'lastname' => $leadData['lastname'],
                    'email' => $leadData['email'],
                    'phone' => $leadData['phone'],
                    'program' => $leadData['program'],
                    'zip' => $leadData['zip'],
                    'university' => $leadData['university'],
                    'journey_type' => $leadData['journey_type'],
                )
            );
            if($leadData["utm_found"]){
                $body['properties']['utm_source'] = $leadData['utm_source'];
                $body['properties']['utm_campaign'] = $leadData['utm_campaign'];
                $body['properties']['utm_medium'] = $leadData['utm_medium'];
                $body['properties']['utm_term'] = $leadData['utm_term'];
                $body['properties']['utm_content'] = $leadData['utm_content'];
            }
            $client = \Drupal::httpClient();
            try{
                $client_response = $client->post($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre>Download brochurenull '.print_r($body,TRUE).'</pre>');
            }


            // \Drupal::logger('hubspot lead create')->notice('<pre> '.print_r($client_response,TRUE).'</pre>');
        }

        return $contactID['id'];
    }
    /**
     * Create Hubspot contact call in controller
     */
    public function createHubspotContactLead($data){
        $config = \Drupal::config('assessment.config');
        $client_url = $config->get('hubspot_update_api').'?hapikey='.$config->get('hubspot_api_key');
        $body = array(
            "properties" => array(
                "email" => "asdfg@user.com",
                "name" => "asdfg",
                "firstname" => "asdfg",
                "mobilephone" => "4561235852",
                "zip" => "123456"
            )
        );
        $client = \Drupal::httpClient();
        $client_response = $client->post($client_url, [
            'headers' => ['Content-Type' => 'application/json',],
            'body' => json_encode($body),
        ]);
        return $client_response;
    }
    /**
     * Create hubspot Task service
     */
    public function createHubspotTaskLead($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $leadData['contactID'];

        $request_time = \Drupal::time();

        $client_url = $config->get('hubspot_task_api').'?hapikey='.$config->get('hubspot_api_key');
        $body = array(
            "engagement" => array(
                "active" => true,
                "type"=> "TASK",
                "ownerId" => OWNER_ID,
                "timestamp" => $request_time->getRequestMicroTime()*1000
            ),
            "associations" => array(
                "contactIds" => [$contactID],
                 "companyIds" => [],
                 "dealIds" => [],
                 "ownerIds" => [],
                "ticketIds" =>[]
            ),
            "attachments" => [],
            "metadata" => array(
                "subject" => $leadData['subject'],
                "body" => $leadData['body'],
                "status" => "NOT_STARTED",
                "forObjectType" => "CONTACT"
            ),
        );
        
        $client = \Drupal::httpClient();
        $client_response = $client->post($client_url, [
            'headers' => ['Content-Type' => 'application/json',],
            'body' => json_encode($body),
        ]);
        return $client_response;
    }
    /**
     * hubspot application steps user_info update.
     * Step 1
     **/
    public function updateApplication_user_info($leadData){
        $site_name = \Drupal::config('system.site')->get('name');
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        $program = $leadData['course_name'];
        
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            $lead_value = 'Lead';
            if($contactID['lead_status'] > 1){
                $lead_value = $contactID['lead_value'];
            }
            $body = array(
                'properties' => array(
                    'hs_lead_status' => $lead_value,
                    'journey_type' => 'Self Service Model',
                    "university" => $site_name,
                    "firstname" => $leadData["naame"],
                    "lastname" => $leadData["last_name"],
                    "mobilephone" => '+'.(int) filter_var($leadData["country"], FILTER_SANITIZE_NUMBER_INT).'  '.$leadData["phone"],
                    "zip" => $leadData["zip"],
                    "program" => $program,
                )
            );
            if($leadData["utm_found"]){
                
                $body['properties']['utm_source'] = $leadData['utm_source'];
                $body['properties']['utm_campaign'] = $leadData['utm_campaign'];
                $body['properties']['utm_medium'] = $leadData['utm_medium'];
                $body['properties']['utm_term'] = $leadData['utm_term'];
                $body['properties']['utm_content'] = $leadData['utm_content'];
            }
            // \Drupal::logger('hubspot services')->notice('<pre> lead data body'.print_r($body,TRUE).'</pre>');
            // \Drupal::logger('hubspot  services')->notice('<pre> info'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre>ste1 '.print_r($e->getMessage(),TRUE).'</pre>');
            }

        }else{
            $client_url = $config->get('hubspot_update_api').'?hapikey='.$config->get('hubspot_api_key');
            $body = array(
                "properties" => array(
                    'hs_lead_status' => 'Lead',
                    'journey_type' => 'Self Service Model',
                    "university" => $site_name,
                    "email" => $leadData["email"],
                    "name" => $leadData["naame"].' '.$leadData["last_name"],
                    "firstname" => $leadData["naame"],
                    "lastname" => $leadData["last_name"],
                    "mobilephone" => '+'.(int) filter_var($leadData["country"], FILTER_SANITIZE_NUMBER_INT).' '.$leadData["phone"],
                    "zip" => $leadData["zip"],
                    "program" => $program,
                )
            );
            if($leadData["utm_found"]){
                
                $body['properties']['utm_source'] = $leadData['utm_source'];
                $body['properties']['utm_campaign'] = $leadData['utm_campaign'];
                $body['properties']['utm_medium'] = $leadData['utm_medium'];
                $body['properties']['utm_term'] = $leadData['utm_term'];
                $body['properties']['utm_content'] = $leadData['utm_content'];
            }
            // \Drupal::logger('hubspot services')->notice('<pre> info-null'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->post($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> step1null'.print_r($e->getMessage(),TRUE).'</pre>');
            }

        }
        
    }
    /**
     * hubspot application steps your_program_goals update. 
     * Step 2  
     **/
    public function updateApplication_programgoals($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            $lead_value = 'Partial Applicant';
            if($contactID['lead_status'] > 2){
                $lead_value = $contactID['lead_value'];
            }
            $body = array(
                'properties' => array(
                    'hs_lead_status' => $lead_value,
                    'what_is_your_motivation_for_doing_the_program_' => $leadData['motivation_to_do_program'],
                    'motivation_to_do_program_note_one' => $leadData['motivation_to_do_program_note'],
                    'what_is_your_greatest_need_in_a_program_' => $leadData['greatest_need_in_program'],
                    'greatest_need_in_program_note' => $leadData['greatest_need_in_program_note'],
                    "what_do_you_hope_to_do_upon_completing" => $leadData["you_hope_to_do_upon_completing"],
                    "what_do_you_hope_to_do_upon_completing_note_one" => $leadData['you_hope_to_do_upon_completing_note'],
                )
            );
            // \Drupal::logger('hubspot services')->notice('<pre> step2'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> step2'.print_r($e->getMessage(),TRUE).'</pre>');
            }

        }
    }
    /**
     * hubspot application steps your_educational_background update.
     * Step 3
     **/
    public function updateApplication_educationalbackground($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            $body = array(
                'properties' => array(
                    'what_is_your_highest_level_of_education_' => $leadData["highest_level_of_education"],
                    'highest_level_of_education_note_one' => $leadData["highest_level_of_education_note"],
                    "do_you_have_experience_with_online_learning_" => $leadData["online_learning_experience"],
                    "online_learning_experience_note_one" => $leadData["online_learning_experience_note"],
                    "have_you_taken_previous_courses_in_technology_" => $leadData["previous_courses_in_technology"],
                    "previous_courses_in_technology__note_one" => $leadData["previous_courses_in_technology_note"],
                )
            );
            // \Drupal::logger('hubspot services')->notice('<pre> step3'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> step3 '.print_r($e->getMessage(),TRUE).'</pre>');
            }

        }
    }
    /**
     * hubspot application steps your_current_employment_status update.
     * Step 4
     **/
    public function updateApplication_currentemployment($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            $body = array(
                'properties' => array(
                    'are_you_currently_employed_' => $leadData["are_you_currently_employed"],
                    'are_you_currently_employed_note' => $leadData["are_you_currently_employed_note"],

                    "your_current_work_schedule_" => $leadData["work_schedule"],
                    "if_yes_are_you_working_full_time_or_part_time_note" => $leadData["work_schedule_note"],

                    "your_current_work_experience_in_years_" => $leadData["years_work_experience"],
                    "years_work_experience_note_one" => $leadData["years_work_experience_note"],
                    "does_your_current_job_involve_working_with_digital_technologies" => $leadData["working_with_digital_technologies"],
                    "does_your_current_job_involve_working_with_digital_technologies_note" => $leadData["working_with_digital_technologies_note"],
                )
            );
            // \Drupal::logger('hubspot services')->notice('<pre> step4'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> step4'.print_r($e->getMessage(),TRUE).'</pre>');
            }

        }
        return $client_response;
    }
    /**
     * hubspot application steps our_overall_career_outlook update.
     * Step 5
     **/
    public function updateApplication_overall_career($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            $lead_value = 'Accepted';
            if($contactID['lead_status'] > 3){
                $lead_value = $contactID['lead_value'];
            }
            $body = array(
                'properties' => array(
                    'hs_lead_status' => $lead_value,
                    'are_you_satisfied_with_your_current_income_earning_potential_in_your_current_position_or_industry_'  => $leadData['your_current_income_earning_potential'],
                    'are_you_satisfied_with_your_current_income_earning_potential_in_your_current_position_note_one'      => $leadData['your_current_income_earning_potential_note'],
                    'are_you_satisfied_with_the_opportunity_for_career_development_and_advancement_in_your_current_posit' => $leadData['the_opportunity_for_career_development'],
                    'are_you_satisfied_with_the_opportunity_for_career_development_and_advancement_note_one'              => $leadData['the_opportunity_for_career_development_note'],
                    'if_no_current_job_are_you_satisfied_with_your_current_employment_prospects__in_a_similar_job__can_y' => $leadData['you_satisfied_with_your_current_employment'],
                    'if_no_current_job_are_you_satisfied_with_your_current_employment_prospects__in_a_similar_job_note_o' => $leadData['you_satisfied_with_your_current_employment_note'],
                    'do_you_see_yourself_committing_20_25_hours_per_week_towards_becoming_a_digital_technology_professio' => $leadData['you_earn_a_higher_income_and_greater_career_stability'],
                    'do_you_see_yourself_committing_hours_per_week_towards_becoming_a_digital_technology_professio'       => $leadData['you_earn_a_higher_income_and_greater_career_stability_note'],
                    'can_you_envision_yourself__after_successful_completion_of_the_university_s_professional_certificati' => $leadData['new_career_as_a_technology_professional'],
                    'can_you_envision_yourself__after_successful_completion_of_the_university_s_professional_note_one'    => $leadData['new_career_as_a_technology_professional_note'],
                )
            );
            // \Drupal::logger('hubspot services')->notice('<pre> step5'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> sep5error'.print_r($e->getMessage(),TRUE).'</pre>');
            }

        }
    }
    /**
     * Update hubspot term of use pdf
     */
    public function updateHubspotPDFLinks($leadData){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $leadData['email'];
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'].'?hapikey='.$data['api_key'];
            
            if(array_key_exists('policy_pdf',$leadData)){
                $body = array(
                    'properties' => array(
                        $leadData['hubspot_key'] => $leadData['policy_pdf']
                    )
                );
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json',],
                    'body' => json_encode($body),
                ]);
            }
        }
        return $contactID['id'];
    }
    
} 