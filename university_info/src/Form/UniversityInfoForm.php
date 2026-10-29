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
class UniversityInfoForm extends ConfigFormBase{
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
   const SETTINGS = 'university_info.config';
   /**
    * {@inheritdoc}
   */
   public function getFormId() {
      return 'university_info_form';	
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
         if($config->get($id.'_html_style_id')){
            $attr = ['disabled' => ['disabled']];
         }else{
            $attr = ['class' => ['no-domain-id']];
         }
         $form[$id.'_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => $name.' information',
            $id.'_domain_type' => [
               '#type'                 => 'select',
               '#default_value'        => $config->get($id.'_domain_type'),
               '#title'                => $this->t("Registerd domain type."),
               '#options'              => [
                  'domain_landing' => 'Domain Landing university',
                  'domain_microsite' => 'Domain microsite university',
                  'domain_microsite_landing' => 'Domain microsite & Landing university'
               ],
               '#required'             => true,
            ],
            $id.'_html_style_id' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_html_style_id'),
               '#title'                => t("University HTML Style ID."),
               '#attributes'           => $attr,
            ],
            $id.'_climb_credit_schoolID' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_climb_credit_schoolID'),
               '#title'                => t("Climb Credit School ID."),
            ],
            $id.'_climb_credit_campus_name' => [
               '#type'                 => 'textfield',
               '#default_value'        => $config->get($id.'_climb_credit_campus_name'),
               '#title'                => t("Climb Credi Campus name."),
            ],
            $form[$id.'_fieldset'][$id.'_logo'] = [
               '#type' => 'fieldset',
               '#title' => $name.' logo config',
               $id.'_logo_header' => [
                  '#type'                 => 'managed_file',
                  '#title'                => t("Upload an $name university header logo"),
                  '#upload_location'      => 'public://',
                  '#default_value'        => explode(',' , $config->get($id.'_logo_header')),
                  '#multiple'             => FALSE,
                  '#description'          => t('Allowed extensions: png jpg jpeg'),
                  '#upload_validators' => [
                     'file_validate_extensions' => [
                     'png jpg jpeg',
                     ],
                  ],
               ],
               $id.'_logo_footer' => [
                  '#type'                 => 'managed_file',
                  '#title'                => t("Upload an $name university footer logo"),
                  '#upload_location'      => 'public://',
                  '#default_value'        => explode(',' , $config->get($id.'_logo_footer')),
                  '#multiple'             => FALSE,
                  '#description'          => t('Allowed extensions: png jpg jpeg'),
                  '#upload_validators' => [
                     'file_validate_extensions' => [
                     'png jpg jpeg',
                     ],
                  ],
               ],
               $id.'_logo2_header' => [
                  '#type'                 => 'managed_file',
                  '#title'                => t("Upload an $name university landing page header logo"),
                  '#upload_location'      => 'public://',
                  '#default_value'        => explode(',' , $config->get($id.'_logo2_header')),
                  '#multiple'             => FALSE,
                  '#description'          => t('Allowed extensions: png jpg jpeg'),
                  '#upload_validators' => [
                     'file_validate_extensions' => [
                     'png jpg jpeg',
                     ],
                  ],
               ],
               $id.'_logo2_footer' => [
                  '#type'                 => 'managed_file',
                  '#title'                => t("Upload an $name university landing page footer logo"),
                  '#upload_location'      => 'public://',
                  '#default_value'        => explode(',' , $config->get($id.'_logo2_footer')),
                  '#multiple'             => FALSE,
                  '#description'          => t('Allowed extensions: png jpg jpeg'),
                  '#upload_validators' => [
                     'file_validate_extensions' => [
                     'png jpg jpeg',
                     ],
                  ],
               ]
            ],

            $form[$id.'_fieldset'][$id.'_contact_info'] = [
               '#type' => 'fieldset',
               '#title' => $name.' Social links and contact info',
               $id.'_client_id' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => $config->get($id.'_client_id'),
                  '#title'                => t("University ClientID."),
               ],
               $id.'_show_aboutus' => [
                  '#type'                 => 'checkboxes',
                  '#default_value'        => $config->get($id.'_show_aboutus'),
                  '#options'              => array(
                     'Yes' => 'Yes'
                  ),
                  '#title'                => t("Show About Us Page"),
                 
               ],
               $id.'_header_logo' => [
                  '#type'                 => 'select',
                  '#default_value'        => $config->get($id.'_header_logo'),
                  '#options'              => array(
                     'center' => t('Header Center'),
                     'left'  => t('Left Side Align Menu')
                  ),
                  '#title'                => t("Header Logo Positon"),
                 
               ], 
               $id.'_chatbot_link' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => $config->get($id.'_chatbot_link'),
                  '#title'                => t("Chatbot link."),
                  
               ],
               $id.'_schedule_call_with_financing' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => $config->get($id.'_schedule_call_with_financing'),
                  '#title'                => t("Schedule Call with Financing Consultant."),
                  
               ],
               $id.'_phone' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => $config->get($id.'_phone'),
                  '#title'                => t("Phone No."),
                  
               ],
               $id.'_facebook' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => $config->get($id.'_facebook'),
                  '#title'                => t("Facebook."),
                  
               ],
               $id.'_linkedin' => [
                  '#type'                 => 'textfield',
                  '#default_value'        =>  $config->get($id.'_linkedin'),
                  '#title'                => t("Linkedin."),
                  
               ],
               $id.'_meta_name' => [
                  '#type'                 => 'textfield',
                  '#default_value'        =>  $config->get($id.'_meta_name'),
                  '#title'                => t("Meta Name."),
                  '#prefix'               => '<div class="meta-tag header-content">'
                  
               ],
               $id.'_meta_content' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => $config->get($id.'_meta_content'),
                  '#title'                => t("Meta Content."),
                  '#suffix'               => '</div>'
                  
               ],
               $id.'_gtm_head' => [
                  '#type'                 => 'textarea',
                  '#default_value'        => $config->get($id.'_gtm_head'),
                  '#title'                => t("GTM script Head."),
                  '#prefix'               => '<div class="gtm-tag header-content">'
               ],
               $id.'_gtm_body' => [
                  '#type'                 => 'textarea',
                  // '#type'                 => 'text_format',
                  // '#allowed_formats'               => 'full_html',
                  '#default_value'        => $config->get($id.'_gtm_body'),
                  '#title'                => t("GTM script Body."),
                  
                  
               ],
               $id.'_hubspot_university_name' => [
                  '#type'                 => 'textfield',
                  // '#type'                 => 'text_format',
                  // '#allowed_formats'               => 'full_html',
                  '#default_value'        => $config->get($id.'_hubspot_university_name'),
                  '#title'                => t("Hubspot University Name."),
                 
                  
              
               ],
               $id.'_financing_scholarship_url' => [
                  '#type'                 => 'textfield',
                   '#default_value'        => $config->get($id.'_financing_scholarship_url'),
                  '#title'                => t("Financing Student Scholarship URL."),
                  
                  
               ],
               $id.'_fullpay_scholarship_url' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => $config->get($id.'_fullpay_scholarship_url'),
                  '#title'                => t("Full Pay Student Scholarship URL."),
                  '#suffix'               => '</div>'
                  
               ]
            ],
         ];
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
         if($form_values[$id.'_logo_header'][0]){
            $file = \Drupal\file\Entity\File::load($form_values[$id.'_logo_header'][0]);
            $file->setPermanent();
            $file->save();
            $config->set($id.'_logo_header', $form_values[$id.'_logo_header'][0]);
         }
         if($form_values[$id.'_logo_footer'][0]){
            $file = \Drupal\file\Entity\File::load($form_values[$id.'_logo_footer'][0]);
            $file->setPermanent();
            $file->save();
            $config->set($id.'_logo_footer', $form_values[$id.'_logo_footer'][0]);
         }
         if($form_values[$id.'_logo2_header'][0]){
            $file = \Drupal\file\Entity\File::load($form_values[$id.'_logo2_header'][0]);
            $file->setPermanent();
            $file->save();
            $config->set($id.'_logo2_header', $form_values[$id.'_logo2_header'][0]);
         }
         if($form_values[$id.'_logo2_footer'][0]){
            $file = \Drupal\file\Entity\File::load($form_values[$id.'_logo2_footer'][0]);
            $file->setPermanent();
            $file->save();
            $config->set($id.'_logo2_footer', $form_values[$id.'_logo2_footer'][0]);
         }
         $config->set($id.'_climb_credit_schoolID', $form_values[$id.'_climb_credit_schoolID']);
         $config->set($id.'_climb_credit_campus_name', $form_values[$id.'_climb_credit_campus_name']);

         $config->set($id.'_domain_type', $form_values[$id.'_domain_type']);
         $config->set($id.'_html_style_id', $form_values[$id.'_html_style_id']);
         $config->set($id.'_client_id', $form_values[$id.'_client_id']);
         $config->set($id.'_show_aboutus', $form_values[$id.'_show_aboutus']);
         $config->set($id.'_chatbot_link', $form_values[$id.'_chatbot_link']);
         $config->set($id.'_schedule_call_with_financing', $form_values[$id.'_schedule_call_with_financing']);
         $config->set($id.'_phone', $form_values[$id.'_phone']);
         $config->set($id.'_facebook', $form_values[$id.'_facebook']);
         $config->set($id.'_linkedin', $form_values[$id.'_linkedin']);
         $config->set($id.'_meta_name', $form_values[$id.'_meta_name']);
         $config->set($id.'_meta_content', $form_values[$id.'_meta_content']);
         $config->set($id.'_gtm_head', $form_values[$id.'_gtm_head']);
         $config->set($id.'_gtm_body', $form_values[$id.'_gtm_body']);
         $config->set($id.'_header_logo', $form_values[$id.'_header_logo']);
         $config->set($id.'_hubspot_university_name', $form_values[$id.'_hubspot_university_name']);
         $config->set($id.'_financing_scholarship_url', $form_values[$id.'_financing_scholarship_url']);
         $config->set($id.'_fullpay_scholarship_url', $form_values[$id.'_fullpay_scholarship_url']);
         
      }
     
      $config->save();
      parent::submitForm($form, $form_state);
 }
}