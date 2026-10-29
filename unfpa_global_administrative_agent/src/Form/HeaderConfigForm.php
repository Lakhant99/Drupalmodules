<?php

namespace Drupal\unfpa_global_administrative_agent\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\file\FileUsage\FileUsageInterface;

/**
 * Class HeaderConfigForm.
 */
class HeaderConfigForm extends ConfigFormBase {

  use StringTranslationTrait;

  /**
   * The language manager variable.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The domain variable.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $negotiator;

  /**
   * The FileUsageInterface service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManager $language_manager, DomainNegotiatorInterface $negotiator, EntityTypeManagerInterface $entity_type_manager, FileUsageInterface $file_usage) {
    $this->setConfigFactory($config_factory);
    $this->languageManager = $language_manager;
    $this->negotiator = $negotiator;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'), $container->get('language_manager'), $container->get('domain.negotiator'), $container->get('entity_type.manager'), $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'unfpa_global_administrative_agent.headerconfig',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'header_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $lan = '';
    $languages = $this->languageManager->getLanguages();
    $current_language_id = $this->languageManager->getCurrentLanguage()->getId();
    $active_id = $this->negotiator->getActiveId();
    $pages = $this->getPagesList();
    unset($languages['zh-hans']);
    unset($languages['ar']);

    $config = $this->config('unfpa_global_administrative_agent.headerconfig');

    $form['#tree'] = TRUE;

    $form['language_config'] = [
      '#type' => 'details',
      '#title' => t('Language configuration'),
      '#open' => TRUE,
    ];
    // Set language list.
    foreach ($languages as $key => $value) {
      if ($key != 'en') {
        $lan .= '<a href="' . $base_url . '/' . $key . '/admin/unfpa_global_site_config/aa-homepage" class="lang_redirect">' . $value->getName() . '</a>  |  ';
      }
      else {
        $lan .= '<a href="' . $base_url . '/admin/unfpa_global_site_config/aa-homepage" class="lang_redirect">' . $value->getName() . '</a>  |  ';
      }
    }
    $form['language_config']['language'] = [
      '#type' => 'markup',
      '#markup' => $lan,
    ];

    $form['header_config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('AA Portal Header Configuration'),
      '#prefix' => '<div id="aa-lang-form">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    foreach ($pages as $key => $val) {
      if ($key == 'home') {
        $dimention = '375x660';
      }
      else {
        $dimention = '375x450';
      }
      $form['header_config'][$key] = [
        '#type' => 'details',
        '#title' => $this->t('@name page', ['@name' => $val]),
        '#open' => FALSE,
      ];
      $title = $config->get($key . '-title_' . $current_language_id . '_' . $active_id);
      $form['header_config'][$key]['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#maxlength' => 64,
        '#size' => 64,
        '#default_value' => $title ? $title : '',
      ];
      $header_image_fid = $config->get($key . '-header_image_' . $current_language_id . '_' . $active_id);
      $form['header_config'][$key]['header_image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Header Image'),
        '#description' => $this->t('The uploaded image will be displayed on home page. Uploads limited to .png .gif .jpg .jpeg extensions.  <br/>Image resolution must be <strong> 1600x750px </strong>'),
        '#upload_validators' => [
          'file_validate_extensions' => ['gif png jpg jpeg'],
          'file_validate_image_resolution' => ['1600x750', '1600x750'],
        ],
       // '#required' => TRUE,
        '#upload_location' => 'public://aa_header/desktop/',
        '#default_value' => $header_image_fid ? [$header_image_fid] : NULL,
      ];

      $mobile_header_image = $config->get($key . '-mobile_header_image_' . $current_language_id . '_' . $active_id);
      $form['header_config'][$key]['mobile_header_image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Mobile Header Image'),
        '#description' => $this->t('The uploaded mobile image will be displayed on home page. <br/>Image resolution must be <strong> @dimension </strong>', ['@dimension' => $dimention]),
        '#upload_validators' => [
          'file_validate_extensions' => ['gif png jpg jpeg'],
          'file_validate_image_resolution' => [$dimention, $dimention],
        ],
      //  '#required' => TRUE,
        '#upload_location' => 'public://aa_header/mobile/',
        '#default_value' => $mobile_header_image ? [$mobile_header_image] : NULL,
      ];

      $bottom_header_text = $config->get($key . '-bottom_header_text_' . $current_language_id . '_' . $active_id);
      $form['header_config'][$key]['bottom_header_text'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Bottom Header Text'),
        '#default_value' => $bottom_header_text ? $bottom_header_text : '',
      ];
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $active_id = $this->negotiator->getActiveId();
    $values = $form_state->getValues();
    $current_language_id = $this->languageManager->getCurrentLanguage()->getId();
    $pages = $this->getPagesList();
    foreach ($pages as $key => $val) {
      $header_image_fid = $values['header_config'][$key]['header_image'];
      if (!empty($header_image_fid)) {
        $header_image_file = $this->entityTypeManager->getStorage('file')->load($header_image_fid[0]);
        $this->fileUsage->add($header_image_file, 'unfpa_global_administrative_agent', 'header_image', $header_image_fid[0]);
        if(!empty($header_image_file)) {
          $header_image_file->setPermanent();
          $header_image_file->save();
          $this->config('unfpa_global_administrative_agent.headerconfig')
            ->set($key . '-header_image_' . $current_language_id . '_' . $active_id, $header_image_fid[0])
            ->save();
        }
      }
      else {
        $this->config('unfpa_global_administrative_agent.headerconfig')
          ->set($key . '-header_image_' . $current_language_id . '_' . $active_id, 0)
          ->save();
      }

      $mobile_header_image_fid = $values['header_config'][$key]['mobile_header_image'];
      if (!empty($mobile_header_image_fid)) {
        $mobile_header_image_file = $this->entityTypeManager->getStorage('file')->load($mobile_header_image_fid[0]);
        $this->fileUsage->add($mobile_header_image_file, 'unfpa_global_administrative_agent', 'mobile_header_image', $mobile_header_image_fid[0]);
        if(!empty($mobile_header_image_file)) {
          $mobile_header_image_file->setPermanent();
          $mobile_header_image_file->save();
          $this->config('unfpa_global_administrative_agent.headerconfig')
            ->set($key . '-mobile_header_image_' . $current_language_id . '_' . $active_id, $mobile_header_image_fid[0])
            ->save();
        }
      }
      else {
        $this->config('unfpa_global_administrative_agent.headerconfig')
          ->set($key . '-mobile_header_image_' . $current_language_id . '_' . $active_id, 0)
          ->save();
      }

      $this->config('unfpa_global_administrative_agent.headerconfig')
        ->set($key . '-title_' . $current_language_id . '_' . $active_id, $values['header_config'][$key]['title'])
        ->set($key . '-bottom_header_text_' . $current_language_id . '_' . $active_id, $values['header_config'][$key]['bottom_header_text'])
        ->save();
    }
  }

  /**
   * Get list of pages for banner image.
   */
  public function getPagesList() {
    return [
      'home' => $this->t("Home"),
      'about-funds' => $this->t("About Us"),
      'dashboard' => $this->t("Dashboard"),
      'news-and-publications' => $this->t("News And Publications"),
    ];
  }

  /**
   * Check for the access of config form.
   */
  public function checkAccess() {
    $activeId = $this->negotiator->getActiveId();
    // Later change it to aa domain.
    if ($activeId == 'aa_unfpa_org') {
      return AccessResult::allowed();
    }
    else {
      return AccessResult::forbidden();
    }
  }

}
