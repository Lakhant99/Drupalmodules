<?php

namespace Drupal\unfpa_global_administrative_agent\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Block to display Home Page Map.
 *
 * @Block(
 *   id = "aa_homepage_map",
 *   admin_label = @Translation("AA:Homepage Map"),
 *   category = @Translation("Custom")
 * )
 */
class HomePageMap extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Dashboard data.
   *
   * @var \Drupal\unfpa_global_administrative_agent\DashboardData
   */
  protected $dashboardData;

  /**
   * The cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheBackendInterface $cache_backend, LanguageManager $language_manager, DashboardData $dashboard_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dashboardData = $dashboard_data;
    $this->cacheBackend = $cache_backend;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('cache.data'), $container->get('language_manager'), $container->get('unfpa_global_administrative_agent.dashboard_data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $homePageData = $this->dashboardData->getHomeMapData();
    $start_year = $homePageData['start_date'];
    $additional_amount_hq = $this->dashboardData->getAdditionalAmountVars();

    return [
      '#theme' => 'homepage_map',
      '#homepage_map_data' => $homePageData,
      '#additional_amount_hq_all' => $additional_amount_hq['2010-2015'],
      '#start_year' => $start_year,
      '#start_year_amount' => $additional_amount_hq[$start_year],
      '#attached' => [
        'library' => [
          'unfpa_global_administrative_agent/unfpa_global_administrative_agent.maps',
        ],
        'drupalSettings' => [
          'map_data' => Json::encode($homePageData["map_data"]),
          'additional_amount_hq' => $additional_amount_hq,
        ],
      ],
      '#cache' => $homePageData['cache'],
    ];
  }

}
