<?php

namespace Drupal\unfpa_global_administrative_agent\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Block to display Key Figures Chart.
 *
 * @Block(
 *   id = "aa_keyfigures_chart",
 *   admin_label = @Translation("AA:Key Figures Chart"),
 *   category = @Translation("Custom")
 * )
 */
class KeyFiguresChart extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Dashboard data.
   *
   * @var \Drupal\unfpa_global_administrative_agent\DashboardData
   */
  protected $dashboardData;

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $route_match, CacheBackendInterface $cache_backend, LanguageManager $language_manager, DashboardData $dashboard_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dashboardData = $dashboard_data;
    $this->routeMatch = $route_match;
    $this->cacheBackend = $cache_backend;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('current_route_match'), $container->get('cache.data'), $container->get('language_manager'), $container->get('unfpa_global_administrative_agent.dashboard_data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $node = $this->routeMatch->getParameter('node');
    $key_figures_data = [];
    if (!empty($node)) {
      if ($node->getType() == 'ct_donor') {
        $key_figures_data = $this->dashboardData->getFinanceData($node->id(), '');
      }
      if ($node->getType() == 'aa_partners') {
        $key_figures_data = $this->dashboardData->getFinanceData('', $node->id());
      }
      if ($node->getType() == 'ct_funds') {
        $key_figures_data = $this->dashboardData->getFinanceData('', '', $node->id());
      }
    }
    else {
      $key_figures_data = $this->dashboardData->getFinanceData();
    }

    return [
      '#theme' => 'key_figures_chart',
      '#key_figures_data' => $key_figures_data['finance_chart'],
      '#date_dropdown' => $key_figures_data['date_dropdown'],
      '#start_date' => $key_figures_data['start_date'],
      '#attached' => [
        'drupalSettings' => [
          'key_figures_data' => Json::encode($key_figures_data['finance_chart']),
        ],
      ],
      '#cache' => $key_figures_data['cache'],
    ];
  }

}
