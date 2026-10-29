<?php

namespace Drupal\unfpa_global_administrative_agent\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Language\LanguageManager;

/**
 * Block to display Dashboard Map.
 *
 * @Block(
 *   id = "aa_dashboard_map_block",
 *   admin_label = @Translation("AA:Dashboard Map Block"),
 *   category = @Translation("Custom")
 * )
 */
class DashboardMap extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The Dashboard data.
   *
   * @var \Drupal\unfpa_global_administrative_agent\DashboardData
   */
  protected $dashboardData;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheBackendInterface $cache_backend, LanguageManager $language_manager, DashboardData $dashboard_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cacheBackend = $cache_backend;
    $this->languageManager = $language_manager;
    $this->dashboardData = $dashboard_data;
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
    $current_language_id = $this->languageManager->getCurrentLanguage()->getId();
    $data = $donor_data = [];
    $cache_list = ['tags' => []];
    $data['donor_dropdown'] = $this->dashboardData->getDonorDropdown();
    $data['partner_dropdown'] = $this->dashboardData->getPartnerDropdown();
    $map_data = $this->dashboardData->getMapData();

    foreach ($map_data['source'] as $key => $val) {
      $date_year = date('Y', strtotime($val->date));
      $date = (string) format_date(strtotime($date_year . '-04-09'), 'custom', 'Y-m-d');
      $donor_data[$val->country . '-' . $date_year]['country'] = trim($val->country);
      $donor_data[$val->country . '-' . $date_year]['type'] = 'origin';
      $donor_data[$val->country . '-' . $date_year]['date'] = $date;
      if (isset($donor_data[$val->country . '-' . $date_year]['commitement'])) {
        $donor_data[$val->country . '-' . $date_year]['commitement'] += is_numeric($val->amount) ? round($val->amount) : 0;
      }
      else {
        $donor_data[$val->country . '-' . $date_year]['commitement'] = is_numeric($val->amount) ? round($val->amount) : 0;
      }
      $donor_data[$val->country . '-' . $date_year]['active_fund'][] = $val->fund_name;
      $fund_data = array_unique($donor_data[$val->country . '-' . $date_year]['active_fund']);
      $donor_data[$val->country . '-' . $date_year]['active_fund'] = $fund_data;
      $donor_date_dropdown[$date_year] = $date_year;
      $cache_list['tags'][] = 'node:' . $val->nid;
    }

    foreach ($map_data['destination'] as $key => $des_val) {
      $exp_date_year = date('Y', strtotime($des_val->date));
      $exp_date = (string) format_date(strtotime($exp_date_year . '-04-09'), 'custom', 'Y-m-d');
      $exp_data[$des_val->country . '-' . $exp_date_year]['country'] = trim($des_val->country);
      $exp_data[$des_val->country . '-' . $exp_date_year]['type'] = 'destination';
      $exp_data[$des_val->country . '-' . $exp_date_year]['date'] = $exp_date;
      if (isset($exp_data[$des_val->country . '-' . $exp_date_year]['expenditure'])) {
        $exp_data[$des_val->country . '-' . $exp_date_year]['expenditure'] += is_numeric($des_val->amount) ? round($des_val->amount) : 0;
      }
      else {
        $exp_data[$des_val->country . '-' . $exp_date_year]['expenditure'] = is_numeric($des_val->amount) ? round($des_val->amount) : 0;
      }
      $exp_data[$des_val->country . '-' . $exp_date_year]['active_fund'][] = $des_val->fund_name;
      $fund_exp_data = array_unique($exp_data[$des_val->country . '-' . $exp_date_year]['active_fund']);
      $exp_data[$des_val->country . '-' . $exp_date_year]['active_fund'] = $fund_exp_data;
      $date_dropdown[$exp_date_year] = $exp_date_year;

      $cache_list['tags'][] = 'node:' . $des_val->nid;
    }
    $final_data = array_merge($donor_data, $exp_data);
    $dashboard_data = array_values($final_data);
    $date_result = array_unique(array_merge($date_dropdown, $donor_date_dropdown));
    arsort($date_result);
    $data['map_details']['start_date'] = max($date_result);
    $data['map_details']['date_dropdown'] = $date_result;
    $data['map_details']['map_data'] = $dashboard_data;
    $additional_amount_hq = $this->dashboardData->getAdditionalAmountVars();
    $start_year = $data['map_details']['start_date'];

    return [
      '#theme' => 'dashboard_map',
      '#data' => $data,
      '#additional_amount_hq_all' => $additional_amount_hq['2010-2015'],
      '#start_year' => $start_year,
      '#start_year_amount' => $additional_amount_hq[$start_year],
      '#attached' => [
        'library' => [
          'unfpa_global_administrative_agent/unfpa_global_administrative_agent.maps',
        ],
        'drupalSettings' => [
          'map_data' => Json::encode($data['map_details']['map_data']),
          'additional_amount_hq' => $additional_amount_hq,
        ],
      ],
      '#cache' => $cache_list,
    ];
  }

}
