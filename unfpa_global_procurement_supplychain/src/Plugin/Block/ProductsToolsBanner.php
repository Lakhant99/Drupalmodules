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
 *   id = "prod_tools_banner",
 *   admin_label = @Translation("Products & Tools Banner"),
 *   category = @Translation("Custom")
 * )
 */
class ProductsToolsBanner extends BlockBase implements ContainerFactoryPluginInterface {
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
    $prod_tools_banner_details = $this->configFactory->get('unfpa_global_procurement_supplychain.settings')->get("config_{$lang_code}");
    $prod_tools_banner_title = $prod_tools_banner_details['prod_banner_text'];

    $productsBannerImage = $productsMobileBannerImage = NULL;
    if ($prod_tools_banner_details['prod_banner_image'][0]) {
      $file = $this->entityTypeManager->getStorage('file')->load($prod_tools_banner_details['prod_banner_image'][0]);
      //$cache_list['tags'][] = 'file:' . $file->id();
      $productsBannerImage = $file->url();
    }

    if ($prod_tools_banner_details['prod_mobile_banner_image'][0]) {
      $file = $this->entityTypeManager->getStorage('file')->load($prod_tools_banner_details['prod_mobile_banner_image'][0]);
      //$cache_list['tags'][] = 'file:' . $file->id();
      $productsMobileBannerImage = $file->url();
    }

    return [
      '#theme' => 'prod_tools_banner',
      '#prod_tools_banner_title' => $prod_tools_banner_title,
      '#prod_tools_banner_image' => $productsBannerImage,
      '#prod_tools_mobile_banner_image' => $productsMobileBannerImage,
      '#attached' => [
        'library' => [
          'unfpa_global_procurement_supplychain/what_we_do_banner',
        ],
      ],
    ];
  }

}
