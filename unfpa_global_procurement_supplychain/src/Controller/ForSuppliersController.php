<?php

namespace Drupal\unfpa_global_procurement_supplychain\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * For Suppliers Page for Procurement & Supply Chain.
 */
class ForSuppliersController extends ControllerBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Language Manager.
   *
   * @var Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManagerInterface $entity_type_manager, LanguageManager $language_manager) {
    $this->config = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * Returns For Suppliers page content.
   */
  public function content() {
    $currentLanguage = $this->languageManager->getCurrentLanguage()->getId();
    $config = $this->config('unfpa_global_procurement_supplychain.settings')->get("config_{$currentLanguage}");

    // Get config values.
    $suppliersBannerText = $config["suppliers_banner_text"];

    return [
      '#theme' => 'for_suppliers',
      '#suppliers_banner_text' => $suppliersBannerText,
      '#attached' => [
        'library' => [
          'unfpa_global_procurement_supplychain/procurement_supplychain',
        ],
      ],
    ];
  }

}
