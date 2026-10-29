<?php

namespace Drupal\hubspot_modification\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HubspotModificationController extends ControllerBase{
    public function Hubspot_API(){
        
        $leadData['email'] = 'lakhan.9.thakur@niit.com';
        $leadData['status'] = 'Applicant/Pre Assesment';
        $HubspotAPI = \Drupal::service('api.hubspot');
        $response = $HubspotAPI->createHubspotContactLead($leadData);
        
        return array(
            '#type' => 'markup',
            '#markup' => '$response->getStatusCode()',
        );
    }
}