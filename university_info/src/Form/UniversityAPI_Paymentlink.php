<?php

namespace Drupal\university_info\Form;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\domain\DomainStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Class UniversityInfoForm.
 *
 * @package Drupal\university_info\Form
 */
class UniversityAPI_Paymentlink extends ConfigFormBase{
   /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;
  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;
  /**
   * The MIME type guesser.
   *
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
   */
  protected $mimeTypeGuesser;
  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;
   /**
   * Domain Storage.
   *
   * @var \Drupal\domain\DomainStorageInterface
   */
  protected $domainStorage;

  /**
   * Associative array of domains $id => $name.
   *
   * @var array
   */
  protected $domainOptionsList;

  /**
   * LibrariesAttachConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config Factory.
   * @param \Drupal\domain_libraries_attach\DomainLibrariesManager $libraries_manager
   *   Libraries Manager.
   * @param \Drupal\domain\DomainStorageInterface $domain_storage
   *   Domain Storage.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler instance to use.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $mime_type_guesser
   *   The MIME type guesser instance to use.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   */
  public function __construct(ConfigFactoryInterface $config_factory, DomainStorageInterface $domain_storage, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler, MimeTypeGuesserInterface $mime_type_guesser, ThemeManagerInterface $theme_manager, FileSystemInterface $file_system = NULL) {
    parent::__construct($config_factory);
    $this->domainStorage = $domain_storage;
    $this->domainOptionsList = $this->domainStorage->loadOptionsList();
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->mimeTypeGuesser = $mime_type_guesser;
    $this->themeManager = $theme_manager;
    if (!$file_system) {
      @trigger_error('The file_system service must be passed to ThemeSettingsForm::__construct(), it is required before Drupal 9.0.0', E_USER_DEPRECATED);
      $file_system = \Drupal::service('file_system');
    }
    $this->fileSystem = $file_system;
  }
    /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
   return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('domain'),
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('file.mime_type.guesser'),
      $container->get('theme.manager'),
      $container->get('file_system')
   );
 }
   /**
   * Config.
   *
   * @var string
   */
   const SETTINGS = 'universityapi_paymentlink.config';
   /**
    * {@inheritdoc}
   */
   public function getFormId() {
      return 'universityapi_paymentlink_form';	
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
      foreach ($this->domainOptionsList as $id => $name) {
         $form[$id] = [
            '#type' => 'fieldset',
            '#title' => $name .' Payment Links',
         ];
         $form[$id][$id.'_fieldset_climb_credit'] = [
            '#type' => 'fieldset',
            '#title' => 'Climb Credit Payment Links',
            $id.'_upfront_payment' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_upfront_payment'),
               '#title'                => t("Upfront Payment link."),
            ],
            $id.'_climb_loan_payment' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_climb_loan_payment'),
               '#title'                => t(" Climb Loan Payment Link."),
            ],
            $id.'_financing_through_climb_payment' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_financing_through_climb_payment'),
               '#title'                => t("Financing Through Climb Payment Link."),
            ],
            $id.'_ascent_payment' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_ascent_payment'),
               '#title'                => t("Ascent Payment Link."),
            ],
            $id.'_fieldset_meritize_credit' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_fieldset_meritize_credit'),
               '#title'                => t("Meritize Payment Link."),
            ],
            $id.'_ascent_payment_text' => [
               '#type'                  => 'text_format',
               '#default_value' => $config->get($id.'_ascent_payment_text.value'),
               '#format' => $config->get($id.'_ascent_payment_text.format'),
               '#title'                => t("Ascent Payment Link."),
            ],
            $id.'_fieldset_meritize_credit_text' => [
               '#type'                  => 'text_format',
               '#default_value' => $config->get($id.'_fieldset_meritize_credit_text.value'),
               '#format' => $config->get($id.'_fieldset_meritize_credit_text.format'),
               '#title'                => t("Meritize Single Payment Link."),
            ],
            $id.'_upfront_payment_text' => [
               '#type'                  => 'text_format',
               '#default_value' => $config->get($id.'_upfront_payment_text.value'),
               '#format' => $config->get($id.'_upfront_payment_text.format'),
            ],
            
            $id.'_climb_loan_text' => [
               '#type' => 'text_format',
               '#default_value' => $config->get($id.'_climb_loan_text.value'),
               '#format' => $config->get($id.'_climb_loan_text.format'),
            ],
             $id.'_interest_financing_text'=> [
               '#type' => 'text_format',
               '#title' => $this->t('Apply for 0% Interest Financing '),
               '#default_value' => $config->get($id.'_interest_financing_text.value'),
               '#format' => $config->get($id.'_interest_financing_text.format'),
             ],

         ];
         $form[$id][$id.'_fieldset_meritize'] = [
            '#type' => 'fieldset',
            '#title' => 'Meritize Payment Links',
            // $id.'_fieldset_meritize_credit' => [
            //    '#type'                 => 'textfield',
            //    '#default_value'        => $config->get($id.'_fieldset_meritize_credit'),
            //    '#title'                => t("Meritize Payment Link."),
            // ],
         ];
         $programs_list = setUniversityProgramsOptions($id);
         foreach($programs_list as $key => $value){
            $form[$id][$id . '_fieldset_meritize'][$id. '_meritize_payment_'.$key] = [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id . '_meritize_payment_' . $key),
               '#title'                => t($value. " Payment Link."),
            ];
         }

         $form[$id][$id.'_fieldset_upfront'] = [
            '#type' => 'fieldset',
            '#title' => 'Upfront Payment Links Coursewise',
         ];
         $programs_list = setUniversityProgramsOptions($id);
         foreach($programs_list as $key => $value){
            $form[$id][$id . '_fieldset_upfront'][$id. '_upfront_payment_link_coursewise'.$key] = [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id . '_upfront_payment_link_coursewise' . $key),
               '#title'                => t($value. " Payment Link."),
            ];
         }
         // $form[$id][$id.'_fieldset_ascent'] = [
         //    '#type' => 'fieldset',
         //    '#title' => 'Ascent Payment Links',
         //    $id.'_ascent_payment' => [
         //       '#type'                 => 'textfield',
         //       '#default_value'        => $config->get($id.'_ascent_payment'),
         //       '#title'                => t("Ascent Payment Link."),
         //    ],
       
         // ];

      }
      return parent::buildForm($form, $form_state);
   }
   /**
   * {@inheritdoc} 
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

      $domain_list = $this->domainOptionsList;
      $form_values = $form_state->getValues();
      $config = $this->configFactory->getEditable(static::SETTINGS);
      
      foreach($domain_list as $id => $fid){
         $config->set($id.'_upfront_payment', $form_values[$id.'_upfront_payment']);
         $config->set($id.'_climb_loan_payment', $form_values[$id.'_climb_loan_payment']);
         $config->set($id.'_financing_through_climb_payment', $form_values[$id.'_financing_through_climb_payment']);
         $config->set($id.'_upfront_payment_text', $form_values[$id.'_upfront_payment_text']);
         $config->set($id.'_climb_loan_text', $form_values[$id.'_climb_loan_text']);
         $config->set($id.'_interest_financing_text', $form_values[$id.'_interest_financing_text']);
         $config->set($id.'_ascent_payment', $form_values[$id.'_ascent_payment']);
         $config->set($id.'_ascent_payment_text', $form_values[$id.'_ascent_payment_text']);
         $config->set($id.'_fieldset_meritize_credit', $form_values[$id.'_fieldset_meritize_credit']);
         $config->set($id.'_fieldset_meritize_credit_text', $form_values[$id.'_fieldset_meritize_credit_text']);
         $programs_list = setUniversityProgramsOptions($id);
         foreach($programs_list as $key => $value){
            $config->set($id . '_meritize_payment_' . $key, $form_values[$id . '_meritize_payment_' . $key]);
         }
         foreach($programs_list as $key => $value){
            $config->set($id . '_upfront_payment_link_coursewise' . $key, $form_values[$id . '_upfront_payment_link_coursewise' . $key]);
         }
      }
     
      $config->save();
      parent::submitForm($form, $form_state);
 }
}