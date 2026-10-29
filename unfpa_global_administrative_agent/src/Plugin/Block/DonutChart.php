<?php

namespace Drupal\unfpa_global_administrative_agent\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Block to display Donut Chart.
 *
 * @Block(
 *   id = "aa_donut_chart",
 *   admin_label = @Translation("AA:Donut Chart"),
 *   category = @Translation("Custom"),
 * )
 */
class DonutChart extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Dashboard data.
   *
   * @var \Drupal\unfpa_global_administrative_agent\DashboardData
   */
  protected $dashboardData;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, CurrentRouteMatch $route_match, CacheBackendInterface $cache_backend, LanguageManager $language_manager, DashboardData $dashboard_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dashboardData = $dashboard_data;
    $this->entityTypeManager = $entity_type_manager;
    $this->routeMatch = $route_match;
    $this->cacheBackend = $cache_backend;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('current_route_match'), $container->get('cache.data'), $container->get('language_manager'), $container->get('unfpa_global_administrative_agent.dashboard_data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $donut_data = [];
    $node = $this->routeMatch->getParameter('node');
    $current_language_id = $this->languageManager->getCurrentLanguage()->getId();
    if ($node->getType() == 'ct_donor') {
      $donut_data = $this->dashboardData->getSankeyChartData($node->id(), '');
    }
    if ($node->getType() == 'aa_partners') {
      $donut_data = $this->dashboardData->getSankeyChartData('', $node->id());
    }

    return [
      '#theme' => 'donut_chart',
      '#node_type' => isset($node) ? $node->getType() : '',
      '#date_dropdown' => isset($donut_data['date_dropdown']) ? $donut_data['date_dropdown'] : '',
      '#start_date' => isset($donut_data['start_date']) ? $donut_data['start_date'] : '',
      '#attached' => [
        'library' => [
          'unfpa_global_administrative_agent/unfpa_global_administrative_agent.charts',
        ],
        'drupalSettings' => [
          'donut_chart_data' => isset($donut_data['donut']) ? $donut_data['donut'] : '',
        ],
      ],
      '#cache' => $donut_data['cache'],
    ];
  }

}
