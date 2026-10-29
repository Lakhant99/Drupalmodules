<?php

namespace Drupal\srl_common\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


class OnlinePaymentConfigForm extends ConfigFormBase{
    /**
    * Config.
    *
    * @var string
    */
    const SETTINGS = 'onlinepayment.config';
    /**
    * {@inheritdoc}
    */
    public function getFormId() {
        return 'onlinepayment_config_form';	
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

        $form['upfront_payment_heading'] = [
          '#type' => 'text_format',
          '#title' => $this->t('Upfront Payment'),
          '#default_value' => $config->get('upfront_payment_heading.value'),
          '#format' => $config->get('upfront_payment_heading.format'),
        ];
        $form['climb_loan_heading'] = [
          '#type' => 'text_format',
          '#title' => $this->t('Apply for a Climb Loan'),
          '#default_value' => $config->get('climb_loan_heading.value'),
          '#format' => $config->get('climb_loan_heading.format'),
        ];
        $form['interest_financing_heading'] = [
          '#type' => 'text_format',
          '#title' => $this->t('Apply for 0% Interest Financing '),
          '#default_value' => $config->get('interest_financing_heading.value'),
          '#format' => $config->get('interest_financing_heading.format'),
        ];
        $form['ascent_payment_text_heading'] = [
          '#type' => 'text_format',
          '#title' => $this->t('Ascent Payment Link'),
          '#default_value' => $config->get('ascent_payment_text_heading.value'),
          '#format' => $config->get('ascent_payment_text_heading.format'),
        ];
        $form['meritize_credit_text_heading'] = [
          '#type' => 'text_format',
          '#title' => $this->t('Meritize Single Payment Link.'),
          '#default_value' => $config->get('meritize_credit_text_heading.value'),
          '#format' => $config->get('meritize_credit_text_heading.format'),
        ];
        return parent::buildForm($form, $form_state);
    }
      /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $upfront_payment_heading = $form_state->getValue('upfront_payment_heading');
    $climb_loan_heading = $form_state->getValue('climb_loan_heading');
    $interest_financing_heading = $form_state->getValue('interest_financing_heading');
    $interest_ascent_payment = $form_state->getValue('ascent_payment_text_heading');
    $interest_meritize_credit = $form_state->getValue('meritize_credit_text_heading');
    
    $this->configFactory->getEditable(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('upfront_payment_heading', $upfront_payment_heading)
      ->set('climb_loan_heading', $climb_loan_heading)
      ->set('interest_financing_heading', $interest_financing_heading)
      ->set('ascent_payment_text_heading', $interest_ascent_payment)
      ->set('meritize_credit_text_heading', $interest_meritize_credit)
      ->save();

    

    parent::submitForm($form, $form_state);
  }
}