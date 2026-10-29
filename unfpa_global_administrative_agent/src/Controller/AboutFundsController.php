<?php

namespace Drupal\unfpa_global_administrative_agent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Language\LanguageManager;
use Drupal\domain\DomainNegotiatorInterface;
use Drupal\Core\Theme\ThemeManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Path\AliasManagerInterface;

/**
 * Defines AboutFundsController class.
 */
class AboutFundsController extends ControllerBase {

  /**
   * The Config Factory variable.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The domain variable.
   *
   * @var \Drupal\domain\DomainNegotiatorInterface
   */
  protected $negotiator;

  /**
   * The theme variable.
   *
   * @var \Drupal\Core\Theme\ThemeManager
   */
  protected $themeManager;

  /**
   * The Dashboard data.
   *
   * @var \Drupal\unfpa_global_administrative_agent\DashboardData
   */
  protected $dashboardData;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactory $config_factory, CurrentPathStack $current_path, LanguageManager $language_manager, DomainNegotiatorInterface $negotiator, ThemeManager $theme_manager, DashboardData $dashboard_data, AliasManagerInterface $alias_manager) {
    $this->configFactory = $config_factory;
    $this->currentPathStack = $current_path;
    $this->languageManager = $language_manager;
    $this->negotiator = $negotiator;
    $this->themeManager = $theme_manager;
    $this->dashboardData = $dashboard_data;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'), $container->get('path.current'), $container->get('language_manager'), $container->get('domain.negotiator'), $container->get('theme.manager'), $container->get('unfpa_global_administrative_agent.dashboard_data'), $container->get('path.alias_manager')
    );
  }

  /**
   * Function for dashboard page content.
   */
  public function aboutFundsData() {
    $config = $this->configFactory->get('unfpa_global_administrative_agent.headerconfig');
    $current_path = ltrim($this->currentPathStack->getPath(), "/");
    $current_language_id = $this->languageManager->getCurrentLanguage()->getId();
    $active_id = $this->negotiator->getActiveId();
    $theme_path = $this->themeManager->getActiveTheme()->getPath();
    $bottom_header_text = $config->get($current_path . '-bottom_header_text_' . $current_language_id . '_' . $active_id);
    $final_result = [];
    $cache_list = ['tags' => []];
    $result = $this->dashboardData->getActiveFunds(NULL, NULL);

    foreach ($result as $k => $v) {
      $final_result[$v->field_fund_status_value][$k]['title'] = $v->title;
      $final_result[$v->field_fund_status_value][$k]['nid'] = $v->nid;
      $final_result[$v->field_fund_status_value][$k]['path'] = $this->aliasManager->getAliasByPath('/node/' . $v->nid);
      $final_result[$v->field_fund_status_value][$k]['image_url'] = $v->uri;
      $cache_list['tags'][] = 'node:' . $v->nid;
    }
    $data['cache'] = $cache_list;
    return [
      '#theme' => 'about_fund_page',
      '#bottom_header_text' => $bottom_header_text,
      '#theme_path' => $theme_path,
      '#past_funds' => $final_result['inactive'],
      '#active_funds' => $final_result['active'],
      '#cache' => $data['cache'],
    ];
  }

}
