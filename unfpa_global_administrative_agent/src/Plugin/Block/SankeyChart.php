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
 * Block to display Sankey Chart.
 *
 * @Block(
 *   id = "aa_sankey_chart",
 *   admin_label = @Translation("AA:Sankey Chart"),
 *   category = @Translation("Custom")
 * )
 */
class SankeyChart extends BlockBase implements ContainerFactoryPluginInterface {

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
    $sankey_chart_data = [];
    $sankey_chart_data = $this->dashboardData->getSankeyChartData();
    return [
      '#theme' => 'sankey_chart',
      '#date_dropdown' => $sankey_chart_data['date_dropdown'],
      '#start_date' => $sankey_chart_data['start_date'],
      '#attached' => [
        'drupalSettings' => [
          'sankey_chart_data' => Json::encode($sankey_chart_data['sankey']),
        ],
      ],
      '#cache' => $sankey_chart_data['cache'],
    ];
  }

}
