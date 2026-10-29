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
 * Class UniversityConfigForm.
 *
 * @package Drupal\university_info\Form
 */
class UniversityConfigForm extends ConfigFormBase{
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
   const SETTINGS = 'university.config';
   /**
    * {@inheritdoc}
   */
   public function getFormId() {
      return 'university_config_form';
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
         if($config->get($id.'_domain_id')){
            $attr = ['disabled' => ['disabled']];
         }else{
            $attr = ['class' => ['no-domain-id']];
         }
         $form[$id.'_fieldset'] = [
            '#type' => 'fieldset',
            '#title' => $name.' information',
            $form[$id.'_fieldset'][$id.'_domain_id'] = [
               '#type'                 => 'textfield',
               '#default_value'        => explode(',' , $config->get($id.'_domain_id')),
               '#title'                => t("University Body Style ID."),
               '#attributes'           => $attr,
            ],
            $form[$id.'_fieldset'][$id.'_logo'] = [
               '#type' => 'fieldset',
               '#title' => $name.' logo config',
               $id.'_logo_header' => [
                  '#type'                 => 'managed_file',
                  '#upload_location'      => 'public://',
                  '#default_value'        => explode(',' , $config->get($id.'_logo_header')),
                  '#multiple'             => FALSE,
                  '#description'          => t('Allowed extensions: png jpg jpeg'),
                  '#upload_validators' => [
                     'file_validate_extensions' => [
                     'png jpg jpeg',
                     ],
                  ],
                  '#title'                => t("Upload an $name university header logo"),
                  
               ],
               $id.'_logo_footer' => [
                  '#type'                 => 'managed_file',
                  '#upload_location'      => 'public://',
                  '#default_value'        => explode(',' , $config->get($id.'_logo_footer')),
                  '#multiple'             => FALSE,
                  '#description'          => t('Allowed extensions: png jpg jpeg'),
                  '#upload_validators' => [
                     'file_validate_extensions' => [
                     'png jpg jpeg',
                     ],
                  ],
                  '#title'                => t("Upload an $name university footer logo"),
                  
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
               $id.'_phone' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => explode(',' , $config->get($id.'_phone')),
                  '#title'                => t("Phone No."),
                  
               ],
               $id.'_facebook' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => explode(',' , $config->get($id.'_facebook')),
                  '#title'                => t("Facebook."),
                  
               ],
               $id.'_linkedin' => [
                  '#type'                 => 'textfield',
                  '#default_value'        => explode(',' , $config->get($id.'_linkedin')),
                  '#title'                => t("Linkedin."),
                  
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
         $config->set($id.'_domain_id', $form_values[$id.'_domain_id']);
         $config->set($id.'_phone', $form_values[$id.'_phone']);
         $config->set($id.'_facebook', $form_values[$id.'_facebook']);
         $config->set($id.'_linkedin', $form_values[$id.'_linkedin']);
      }
      $config->save();
      parent::submitForm($form, $form_state); 
 }
}