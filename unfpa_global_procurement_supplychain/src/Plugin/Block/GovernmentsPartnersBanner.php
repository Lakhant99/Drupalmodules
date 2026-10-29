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
 *   id = "govt_part_banner",
 *   admin_label = @Translation("For Governments & Partners Banner"),
 *   category = @Translation("Custom")
 * )
 */
class GovernmentsPartnersBanner extends BlockBase implements ContainerFactoryPluginInterface {
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
    $govt_part_banner_details = $this->configFactory->get('unfpa_global_procurement_supplychain.settings')->get("config_{$lang_code}");
    $govt_part_banner_title = $govt_part_banner_details['govt_part_banner_text'];

    $govtPartBannerImage = $govtPartMobileBannerImage = NULL;
    if ($govt_part_banner_details['govt_part_banner_image'][0]) {
      $file = $this->entityTypeManager->getStorage('file')->load($govt_part_banner_details['govt_part_banner_image'][0]);
      //$cache_list['tags'][] = 'file:' . $file->id();
      $govtPartBannerImage = $file->url();
    }

    if ($govt_part_banner_details['govt_part_mobile_banner_image'][0]) {
      $file = $this->entityTypeManager->getStorage('file')->load($govt_part_banner_details['govt_part_mobile_banner_image'][0]);
      //$cache_list['tags'][] = 'file:' . $file->id();
      $govtPartMobileBannerImage = $file->url();
    }

    return [
      '#theme' => 'govt_part_banner',
      '#govt_part_banner_title' => $govt_part_banner_title,
      '#govt_part_banner_image' => $govtPartBannerImage,
      '#govt_part_mobile_banner_image' => $govtPartMobileBannerImage,
      '#attached' => [
        'library' => [
          'unfpa_global_procurement_supplychain/what_we_do_banner',
        ],
      ],
    ];
  }

}
