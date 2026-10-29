<?php

namespace Drupal\hubspot_modification\Services;

use Drupal\Core\Session\AccountProxy;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HubspotAPI{

    /**
     * hubspot search API  get contact id by email 
     * */
    public function GetContactID($data){
        $contact = [];
        \Drupal::logger('hubspot API call')->notice('<pre>hubspotAPI: '.print_r($data,TRUE).'</pre>');
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
            'properties' => ["id","application_step","phase","sub_lead_status"],
            'limit' => 1,
            'after' => 0
        );
        $client_url = $data['api_url'];//.'?hapikey='.$data['api_key'];
        try{
            $client = \Drupal::httpClient();
            $request_search = $client->post($client_url, [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => $data['api_key']],
                'body' => json_encode($body),
            ])->getBody()->getContents();
            $result = json_decode($request_search);
            \Drupal::logger('hubspot get id')->notice('<pre> get id '.print_r($result,TRUE).'</pre>');
        }catch(\Exception $e) {
            \Drupal::logger('hubspot services')->notice('<pre> get id '.print_r($e->getMessage(),TRUE).'</pre>');
        }
        if($result->results[0]){
            $application_level = 2;
            $application_step = $result->results[0]->properties->application_step;
            if($application_step == 'Partial Applicant Step 1 (50%)'){
                $application_level = 2;
            }elseif($application_step == 'Partial Applicant Step 2 (75%)'){
                $application_level = 3;
            }elseif($application_step == 'Application Completed'){
                $application_level = 4;
            }
            $sub_status = 'application';
            if($result->results[0]->properties->phase == 'NEW INQUIRY MANAGMENT'){
                $sub_status = 'payment';
            }

            $contact['id'] = $result->results[0]->id;
            $contact['application_step'] = $application_level;
            $contact['application_step_value'] = $application_step;
            $contact['phase'] = $sub_status;
            $contact['phase_status'] = $result->results[0]->properties->phase;
        }
        return $contact;
    }
    
    /**
     * hubspot application steps user_info update.
     * Step 1
     **/
    public function saveRecordToHubspot($leadData,$emailid){
        
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $emailid;
        $contactID = $this->GetContactID($data);
        
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'];//.'?hapikey='.$data['api_key'];
            foreach($leadData as $key => $value){
                if($value){
                    $body['properties'][$key] = $value;
                }
            }
            if($contactID['phase_status'] == 'LEAD MANAGEMENT'){
                $body['properties']['journey_type'] = 'Self Service Model';
                $body['properties']['hs_lead_status'] = 'Partial Applicant';
                $body['properties']['phase'] = 'NEW INQUIRY MANAGMENT';
                $body['properties']['primary_pipeline_status'] = 'Inquiry  - Has completed our basic RFI form';
                $body['properties']['application_step'] = 'Partial Applicant Step 1';
                $body['properties']['sub_lead_status'] = 'Initial Attempting Contact';
            }
            $application_steps = [
                2 => 'Partial Applicant Step 1 (50%)',
                3 => 'Partial Applicant Step 2 (75%)',
                4 => 'Application Completed',
            ];
            // phase
            // primary_pipeline_status
            // sub_lead_status
            // 
            if($contactID['application_step'] == 4){
                unset($body['properties']['application_step']);
            }else{
                $application_step_no = $body['properties']['application_step'];
                if($contactID['application_step'] >= $body['properties']['application_step']){
                    $body['properties']['application_step'] = $application_steps[$contactID['application_step']];
                }else{
                    $body['properties']['application_step'] = $application_steps[$body['properties']['application_step']];
                }
                if($application_step_no == 4){
                    $body['properties']['hs_lead_status'] = 'Accepted';
                    $body['properties']['application_step'] = 'Application Completed';
                    $host = \Drupal::request()->getSchemeAndHttpHost();
                    $getRoute = \Drupal::routeMatch()->getRouteName();
                    if($current_path == '/application-form' || $getRoute == 'srl_common.applicationform'){
                        $body['properties']['application_completed_on_website'] = 'Yes';
                    }else{
                    $body['properties']['application_completed_on_website'] = '';
                    }
                    $body['properties']['application_completed_date'] = date('Y-m-d'); //2022-06-08
                    if($contactID['phase'] == 'application'){
                        $body['properties']['sub_lead_status'] = 'Initial Attempting Contact';
                        $body['properties']['primary_pipeline_status'] = 'Inquiry  - Has completed our basic RFI form';
                    }
                }

            }
            \Drupal::logger('hubspot services body')->notice('<pre> stepe'.$application_step_no.':-'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => $data['api_key']],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre>steperror '.print_r($e->getMessage(),TRUE).'</pre>');
            }

        }else{
            $client_url = $config->get('hubspot_update_api');//.'?hapikey='.$config->get('hubspot_api_key');
            foreach($leadData as $key => $value){
                $body['properties'][$key] = $value;
            }
            $body['properties']['journey_type'] = 'Self Service Model';
            $body['properties']['hs_lead_status'] = 'Partial Applicant';
            $body['properties']['phase'] = 'NEW INQUIRY MANAGMENT';
            $body['properties']['primary_pipeline_status'] = 'Inquiry  - Has completed our basic RFI form';
            $body['properties']['application_step'] = 'Partial Applicant Step 1 (50%)';
            $body['properties']['sub_lead_status'] = 'Initial Attempting Contact';
            \Drupal::logger('hubspot services body')->notice('<pre> stepe 1'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->post($client_url, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => $config->get('hubspot_api_key'),],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> steperror'.print_r($e->getMessage(),TRUE).'</pre>');
            }
        }
    }
    public function updateLeadStatus_ONPayment($leadData,$emailid){
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $emailid;
        $contactID = $this->GetContactID($data);

        if($contactID){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'];//.'?hapikey='.$data['api_key'];
            foreach($leadData as $key => $value){
                $body['properties'][$key] = $value;
            }
            if($contactID['phase_status'] == 'LEAD MANAGEMENT'){
                $body['properties']['journey_type'] = 'Self Service Model';
                $body['properties']['hs_lead_status'] = 'Partial Applicant';
                $body['properties']['phase'] = 'NEW INQUIRY MANAGMENT';
            }
            \Drupal::logger('hubspot services body')->notice('<pre> step payment'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => $data['api_key'],],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services4 ')->notice('<pre>steperror '.print_r($e->getMessage(),TRUE).'</pre>');
            }
        }
    }
    public function updateBrochureContactStatus($leadData,$emailid){

        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $emailid;
        $contactID = $this->GetContactID($data);

        
        if($contactID){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'];//.'?hapikey='.$data['api_key'];
            foreach($leadData as $key => $value){
                if($value){
                    $body['properties'][$key] = $value;
                }
            }
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => $data['api_key'],],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre>steperror '.print_r($e->getMessage(),TRUE).'</pre>');
            }
        }else{
            $client_url = $config->get('hubspot_update_api');//.'?hapikey='.$config->get('hubspot_api_key');
            foreach($leadData as $key => $value){
                $body['properties'][$key] = $value;
            }
            
         //  $body['properties']['hs_lead_status'] = 'Lead';
            $body['properties']['phase'] = 'NEW INQUIRY MANAGMENT';
            $body['properties']['primary_pipeline_status'] = 'Inquiry  - Has completed our basic RFI form';
            $body['properties']['sub_lead_status'] = 'Initial Attempting Contact';
          //  $body['properties']['journey_type'] = 'High Touch Model';
            \Drupal::logger('hubspot services')->notice('<pre> brochure'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->post($client_url, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => $config->get('hubspot_api_key'),],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> steperror'.print_r($e->getMessage(),TRUE).'</pre>');
            }
        }

        return $contactID['id'];
    }
    public function CreatelandingPageContactLead($leadData,$emailid){
        $config = \Drupal::config('assessment.config');
        $data['api_url'] = $config->get('hubspot_search_api');
        $data['api_key'] = $config->get('hubspot_api_key');
        $data['email'] = $emailid;
        $contactID = $this->GetContactID($data);
        if($contactID['id']){
            $update_api = $config->get('hubspot_update_api');
            $client_url = $update_api.'/'.$contactID['id'];//.'?hapikey='.$data['api_key'];
            foreach($leadData as $key => $value){
                if($value){
                    $body['properties'][$key] = $value;
                }
            }
            $body['properties']['application_step'] = 'Application Completed';
            // \Drupal::logger('hubspot landing page')->notice('<pre> stepe 1'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->patch($client_url, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => $data['api_key'],],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre>steperror '.print_r($e->getMessage(),TRUE).'</pre>');
            }
        }else{
            $client_url = $config->get('hubspot_update_api');
            foreach($leadData as $key => $value){
                $body['properties'][$key] = $value;
            }
            // $body['properties']['journey_type'] = 'Self Service Model';
            // $body['properties']['hs_lead_status'] = 'Partial Applicant';
            // $body['properties']['phase'] = 'ACTIVE PIPELINE';
            // $body['properties']['primary_pipeline_status'] = 'Partial App';
            if(isset($body['properties']['application_step']) && $body['properties']['application_step'] == '') {
                $body['properties']['application_step'] = '';
            }else{
                $body['properties']['application_step'] = 'Partial Applicant Step 1 (50%)';
            }
            
            // $body['properties']['sub_lead_status'] = 'Attempting Contact';
            // \Drupal::logger('hubspot landing page')->notice('<pre> stepe 1'.print_r($body,TRUE).'</pre>');
            try{
                $client = \Drupal::httpClient();
                $client_response = $client->post($client_url, [
                    'headers' => ['Content-Type' => 'application/json', 'Authorization' => $config->get('hubspot_api_key'),],
                    'body' => json_encode($body),
                ]);
            }catch(\Exception $e) {
                \Drupal::logger('hubspot services')->notice('<pre> steperror'.print_r($e->getMessage(),TRUE).'</pre>');
            }
        }
    }
} 