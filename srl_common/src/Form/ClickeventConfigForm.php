<?php

namespace Drupal\srl_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ClickeventConfigForm extends ConfigFormBase{
    /**
    * Config.
    *
    * @var string
    */
    const SETTINGS = 'clickevent.config';
    /**
    * {@inheritdoc}
    */
    public function getFormId() {
        return 'clickevent_config_form';	
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

        $form['click_event_url'] = [
          HASH_TYPE => TEXTFIELD,
          HASH_TITLE => $this->t('Click Event Url '),
          HASH_DEFAULT_VALUE => $config->get('click_event_url'),
        ];
        return parent::buildForm($form, $form_state);
    }
      /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $click_event_url = $form_state->getValue('click_event_url');
    
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('click_event_url', $click_event_url)
      ->save();

    parent::submitForm($form, $form_state);
  }
}