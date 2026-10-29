<?php

namespace Drupal\unfpa_global_administrative_agent\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Drupal\Core\Theme\ThemeManager;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Block to display Transaction Register.
 *
 * @Block(
 *   id = "aa_transaction_register",
 *   admin_label = @Translation("AA:Transaction Register"),
 *   category = @Translation("Custom")
 * )
 */
class TransactionRegister extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Dashboard data.
   *
   * @var \Drupal\unfpa_global_administrative_agent\DashboardData
   */
  protected $dashboardData;

  /**
   * Access theme functions.
   *
   * @var Drupal\Core\Theme\ThemeManager
   */
  protected $themeManager;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ThemeManager $theme_manager, CurrentRouteMatch $route_match, CacheBackendInterface $cache_backend, LanguageManager $language_manager, DashboardData $dashboard_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dashboardData = $dashboard_data;
    $this->themeManager = $theme_manager;
    $this->routeMatch = $route_match;
    $this->cacheBackend = $cache_backend;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('theme.manager'), $container->get('current_route_match'), $container->get('cache.data'), $container->get('language_manager'), $container->get('unfpa_global_administrative_agent.dashboard_data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $theme_path = $this->themeManager->getActiveTheme()->getPath();
    $node = $this->routeMatch->getParameter('node');
    return [
      '#theme' => 'transaction_register',
      '#node_type' => !empty($node) ? $node->getType() : '',
      '#node_id' => !empty($node) ? $node->id() : '',
      '#theme_path' => $base_url . "/" . $theme_path,
      '#cache' => $cache,
    ];
  }

}
