<?php

namespace Drupal\srl_climb_credit_api\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\Core\Url;

/**
 * Configure SRL Climb Credit Api settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'srl_climb_credit_api_key_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['srl_climb_credit_api_key.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['srl_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Climb Credit Loan API Key'),
      '#default_value' => $this->config('srl_climb_credit_api_key.settings')->get('srl_api_key'),
    ];
    $form['get_payment'] = [
      '#type' => 'link',
      '#title' => [
        '#markup' => '<strong>Get The Payment Info<br><br></strong>',
      ],
      '#url' => Url::fromUri('internal:/srl-climb-credit-api/get-payment-info'),
    ];
    $form['get_loan'] = [
      '#type' => 'link',
      '#title' => [
        '#markup' => '<strong>Get The Loan Info<br><br></strong>',
      ],
      '#url' => Url::fromUri('internal:/srl-climb-credit-api/get-loan-info'),
    ];
    
    $form['get_cron'] = [
      '#type' => 'link',
      '#title' => [
        '#markup' => '<strong>Get The Cron Data<br><br></strong>',
      ],
      '#url' => Url::fromUri('internal:/srl-climb-credit-api/get-loandata'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (strlen($form_state->getValue('srl_api_key')) > 15 ) {
      $form_state->setErrorByName('srl_api_key', $this->t('The API key is not correct if you created new api give any name for ex:- aurbun, domain etc.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $key = md5($form_state->getValue('srl_api_key'));
    $this->config('srl_climb_credit_api_key.settings')
      ->set('srl_api_key', $key)
      ->save();
    parent::submitForm($form, $form_state);
  }

}
