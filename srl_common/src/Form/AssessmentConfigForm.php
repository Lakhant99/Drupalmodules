<?php

namespace Drupal\srl_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AssessmentConfigForm extends ConfigFormBase{
    /**
    * Config.
    *
    * @var string
    */
    const SETTINGS = 'assessment.config';
    /**
    * {@inheritdoc}
    */
    public function getFormId() {
        return 'assessment_config_form';	
    }
    /**
     * {@inheritdoc}
    */
    protected function getEditableConfigNames() {
        return [
        static::SETTINGS,
        ];
    }
    /**
    *  {@inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config(static::SETTINGS);

        $form['assessment_nas_url'] = [
          HASH_TYPE => TEXTFIELD,
          HASH_TITLE => $this->t('Assessment nas training url '),
          HASH_DEFAULT_VALUE => $config->get('assessment_nas_url'),
        ];
        $form['hubspot_search_api'] = [
          HASH_TYPE => TEXTFIELD,
          HASH_TITLE => $this->t('HubSpot API search url '),
          HASH_ATTRIBUTES => [
            PLACEHOLDER => t('EX:- https://api.hubapi.com/crm/v3/objects/contacts/search'),
          ],
          HASH_DEFAULT_VALUE => $config->get('hubspot_search_api'),
          
        ];
        $form['hubspot_update_api'] = [
          HASH_TYPE => TEXTFIELD,
          HASH_TITLE => $this->t('HubSpot API update url '),
          HASH_ATTRIBUTES => [
            PLACEHOLDER => t('Ex:- https://api.hubapi.com/crm/v3/objects/contacts'),
          ],
          HASH_DEFAULT_VALUE => $config->get('hubspot_update_api'),
        ];
        $form['hubspot_task_api'] = [
          HASH_TYPE => TEXTFIELD,
          HASH_TITLE => $this->t('HubSpot API create task url '),
          HASH_ATTRIBUTES => [
            PLACEHOLDER => t('Ex:-  https://api.hubapi.com/engagements/v1/engagements'),
          ],
          HASH_DEFAULT_VALUE => $config->get('hubspot_task_api'),
        ];
        $form['hubspot_property_api'] = [
          HASH_TYPE => TEXTFIELD,
          HASH_TITLE => $this->t('HubSpot API propery url '),
          HASH_ATTRIBUTES => [
            PLACEHOLDER => t('Ex:- https://api.hubapi.com/crm/v3/properties/contact'),
          ],
          HASH_DEFAULT_VALUE => $config->get('hubspot_property_api'),
        ];
        $form['hubspot_api_key'] = [
          HASH_TYPE => TEXTFIELD,
          HASH_TITLE => $this->t('HubSpot API key '),
          HASH_ATTRIBUTES => [
            PLACEHOLDER => t('API key...'),
          ],
          HASH_DEFAULT_VALUE => $config->get('hubspot_api_key'),
        ];
        
        return parent::buildForm($form, $form_state);
    }
      /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $nas_url = $form_state->getValue('assessment_nas_url');
    
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('assessment_nas_url', $nas_url)
      ->set('hubspot_search_api',$form_state->getValue('hubspot_search_api'))
      ->set('hubspot_update_api',$form_state->getValue('hubspot_update_api'))
      ->set('hubspot_property_api',$form_state->getValue('hubspot_property_api'))
      ->set('hubspot_task_api',$form_state->getValue('hubspot_task_api'))
      ->set('hubspot_api_key',$form_state->getValue('hubspot_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}