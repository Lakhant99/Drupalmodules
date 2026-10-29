<?php

namespace Drupal\srl_climb_credit_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Url;

/**
 * Configure SRL Climb Credit Api settings for this site.
 */
class EmailSettingForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'srl_climb_credit_api_email_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['srl_climb_credit_api_email.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['custom_email'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Email Configuration'),
      '#default_value' => $this->config('srl_climb_credit_api_email.settings')->get('custom_email'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $mail = $form_state->getValue('custom_email');
    $mail = explode(',', $mail);
    foreach ($mail as $key => $value) {
        $email = trim($value);
        if (!valid_email_address($email)) {
            $form_state->setErrorByName('custom_email', $this->t('Please check email:-'.$email));
        }
    }   
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $key = $form_state->getValue('custom_email');
    $this->config('srl_climb_credit_api_email.settings')
      ->set('custom_email', $key)
      ->save();
    parent::submitForm($form, $form_state);
  }

}
