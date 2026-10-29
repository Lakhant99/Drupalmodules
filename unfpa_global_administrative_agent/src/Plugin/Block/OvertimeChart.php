<?php

namespace Drupal\unfpa_global_administrative_agent\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Block to display Overtime Chart.
 *
 * @Block(
 *   id = "aa_overtime_chart",
 *   admin_label = @Translation("AA:Overtime Chart"),
 *   category = @Translation("Custom")
 * )
 */
class OvertimeChart extends BlockBase implements ContainerFactoryPluginInterface {

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
    if (!empty($node)) {
      if ($node->getType() == 'ct_donor') {
        $overtime_chart_data = $this->dashboardData->getFinanceData($node->id(), '');
      }
      if ($node->getType() == 'aa_partners') {
        $overtime_chart_data = $this->dashboardData->getFinanceData('', $node->id());
      }
      if ($node->getType() == 'ct_funds') {
        $overtime_chart_data = $this->dashboardData->getFinanceData('', '', $node->id());
      }
    }
    else {
      $overtime_chart_data = $this->dashboardData->getFinanceData();
    }
    return [
      '#theme' => 'overtime_chart',
      '#node_type' => !empty($node) ? $node->getType() : '',
      '#node_title' => !empty($node) ? $node->getTitle() : '',
      '#attached' => [
        'library' => [
          'unfpa_global_administrative_agent/unfpa_global_administrative_agent.charts',
        ],
        'drupalSettings' => [
          'overtime_chart_data' => !empty($overtime_chart_data) ? $overtime_chart_data['overtime_dashboard'] : "",
        ],
      ],
      '#cache' => $overtime_chart_data['cache'],
    ];
  }

}
