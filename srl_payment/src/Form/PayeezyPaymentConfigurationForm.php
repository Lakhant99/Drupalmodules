<?php

namespace Drupal\srl_payment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class PayeezyPaymentConfigurationForm.
 */
class PayeezyPaymentConfigurationForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'srl_payment.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payeezy_settings_form';
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
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['x_login'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Login ID'),
      '#attributes' => [
        'placeholder' => t('Login ID...'),
      ],
      '#default_value' => $config->get('x_login'),
    ];

    $form['payeezy_payment_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Payment URL'),
      '#attributes' => [
        'placeholder' => t('Payment URL...'),
      ],
      '#default_value' => $config->get('payeezy_payment_url'),
    ];

    $form['payeezy_transaction_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Transaction Key'),
      '#attributes' => [
        'placeholder' => t('Transaction Key...'),
      ],
      '#default_value' => $config->get('payeezy_transaction_key'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $x_login = $form_state->getValue('x_login');
    $payment_url = $form_state->getValue('payeezy_payment_url');
    $payeezy_transaction_key = $form_state->getValue('payeezy_transaction_key');

    // Retrieve the configuration.
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('x_login', $x_login)
      ->set('payeezy_payment_url', $payment_url)
      ->set('payeezy_transaction_key', $payeezy_transaction_key)
      ->save();

    parent::submitForm($form, $form_state);
  }

}
