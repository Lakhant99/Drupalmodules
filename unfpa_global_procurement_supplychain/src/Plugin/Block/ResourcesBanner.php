<?php

namespace Drupal\unfpa_global_procurement_supplychain\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 *
 * @Block(
 *   id = "resources_banner",
 *   admin_label = @Translation("Resources Banner"),
 *   category = @Translation("Custom")
 * )
 */
class ResourcesBanner extends BlockBase implements ContainerFactoryPluginInterface {
  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Config Factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $config_factory, LanguageManagerInterface $language_manager, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lang_code = $this->languageManager->getCurrentLanguage()->getId();
    //$cache_list = ['tags' => ['config:unfpa_global_procurement_supplychain.settings']];
    $resources_banner_details = $this->configFactory->get('unfpa_global_procurement_supplychain.settings')->get("config_{$lang_code}");
    $resources_banner_title = $resources_banner_details['res_banner_text'];

    $resourcesBannerImage = $resourcesMobileBannerImage = NULL;
    if ($resources_banner_details['res_banner_image'][0]) {
      $file = $this->entityTypeManager->getStorage('file')->load($resources_banner_details['res_banner_image'][0]);
      //$cache_list['tags'][] = 'file:' . $file->id();
      $resourcesBannerImage = $file->url();
    }

    if ($resources_banner_details['res_mobile_banner_image'][0]) {
      $file = $this->entityTypeManager->getStorage('file')->load($resources_banner_details['res_mobile_banner_image'][0]);
      //$cache_list['tags'][] = 'file:' . $file->id();
      $resourcesMobileBannerImage = $file->url();
    }

    return [
      '#theme' => 'resources_banner',
      '#resources_banner_title' => $resources_banner_title,
      '#resources_banner_image' => $resourcesBannerImage,
      '#resources_mobile_banner_image' => $resourcesMobileBannerImage,
      '#attached' => [
        'library' => [
          'unfpa_global_procurement_supplychain/what_we_do_banner',
        ],
      ],
    ];
  }

}
