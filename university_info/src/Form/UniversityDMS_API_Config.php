<?php

namespace Drupal\university_info\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;


/**
 * Class UniversityInfoForm.
 *
 * @package Drupal\university_info\Form
 */
class UniversityDMS_API_Config extends ConfigFormBase{
   
  /**
   * LibrariesAttachConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    
  }
    /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
   return new static($container->get('config.factory'));
}
   /**
   * Config.
   *
   * @var string
   */
   const SETTINGS = 'university_dmsapi.config';
   /**
    * {@inheritdoc}
   */
   public function getFormId() {
      return 'university_dmsapi_form';	
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
   public function buildForm(array $form, FormStateInterface $form_state){
      $config = $this->config(static::SETTINGS);
      $form['Container_fieldset'] = [
         '#type' => 'fieldset',
         '#title' => 'DMS API information',
      ];
      $form['Container_fieldset']['Container_fieldset_wrapper'] = [
         '#type' => 'fieldset',
      ];
      $form['Container_fieldset']['Container_fieldset_wrapper']['API_get_token'] = [
         '#type' => 'textfield',
         '#title' => 'Generate Token API Link',
         '#default_value' => $config->get('API_get_token'),
      ];
      $form['Container_fieldset']['Container_fieldset_wrapper']['API_get_batch'] = [
         '#type' => 'textfield',
         '#title' => 'Get Batch Detail API Link',
         '#default_value' => $config->get('API_get_batch'),
      ];
      $form['Container_fieldset']['Container_fieldset_wrapper']['API_save_learner'] = [
         '#type' => 'textfield',
         '#title' => 'Save Learner Info API Link',
         '#default_value' => $config->get('API_save_learner'),
      ];
      /**Climb Credit card API implemantation  */
      $form['Container_fieldset_payment'] = [
         '#type' => 'fieldset',
         '#title' => 'Climb Credit API Information',
      ];
      $form['Container_fieldset_payment']['Container_fieldset_payment_wrapper'] = [
         '#type' => 'fieldset',
      ];
      $form['Container_fieldset_payment']['Container_fieldset_payment_wrapper']['CC_API_online_client_url'] = [
         '#type' => 'textfield',
         '#title' => 'Generate Climb Credit Payment Link',
         '#default_value' => $config->get('CC_API_online_client_url'),
      ];
      $form['Container_fieldset_payment']['Container_fieldset_payment_wrapper']['CC_API_loan_client_url'] = [
         '#type' => 'textfield',
         '#title' => 'Generate Climb Credit loan Payment Link',
         '#default_value' => $config->get('CC_API_loan_client_url'),
      ];
      $form['Container_fieldset_payment']['Container_fieldset_payment_wrapper']['cc_API_key'] = [
         '#type' => 'textfield',
         '#title' => 'Climb Credit API Key',
         '#default_value' => $config->get('cc_API_key'),
      ];
      $form['Container_fieldset_payment']['Container_fieldset_payment_wrapper']['cc_accunt_id'] = [
         '#type' => 'textfield',
         '#title' => 'Climb Credit Account ID',
         '#default_value' => $config->get('cc_accunt_id'),
      ];

       /**Climb Credit card Loan API implemantation  */
       $form['Container_fieldset_payment_loan'] = [
         '#type' => 'fieldset',
         '#title' => 'Climb Credit LOAN API Information',
      ];
      $form['Container_fieldset_payment_loan']['Container_fieldset_payment_wrapper_loan'] = [
         '#type' => 'fieldset',
      ];
      $form['Container_fieldset_payment_loan']['Container_fieldset_payment_wrapper_loan']['CC_API_loan_key'] = [
         '#type' => 'textfield',
         '#title' => 'Climb Credit Loan API Key',
         '#default_value' => $config->get('CC_API_loan_key'),
      ];
      
      return parent::buildForm($form, $form_state);
   }
   /**
   * {@inheritdoc} 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

      $form_values = $form_state->getValues();
      $config = $this->configFactory->getEditable(static::SETTINGS);
      /** DMS api configuration */
      $config->set('API_get_token', $form_values['API_get_token']);
      $config->set('API_get_batch', $form_values['API_get_batch']);
      $config->set('API_save_learner', $form_values['API_save_learner']);
      /** climb credit api configuration */
      $config->set('CC_API_loan_client_url', $form_values['CC_API_loan_client_url']);
      $config->set('CC_API_online_client_url', $form_values['CC_API_online_client_url']);
      $config->set('cc_API_key', $form_values['cc_API_key']);
      $config->set('cc_accunt_id', $form_values['cc_accunt_id']);
      /** climb credit api configuration */
      $config->set('CC_API_loan_key', $form_values['CC_API_loan_key']);

      $config->save();
      parent::submitForm($form, $form_state);
 }
}