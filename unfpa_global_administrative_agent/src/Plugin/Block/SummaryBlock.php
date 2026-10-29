<?php

namespace Drupal\unfpa_global_administrative_agent\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Language\LanguageManager;

/**
 * Block to display Summary Block.
 *
 * @Block(
 *   id = "aa_summary_block",
 *   admin_label = @Translation("AA:Summary Block"),
 *   category = @Translation("Custom")
 * )
 */
class SummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactory $configFactory, CacheBackendInterface $cache_backend, LanguageManager $language_manager, DashboardData $dashboard_data) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dashboardData = $dashboard_data;
    $this->configFactory = $configFactory;
    $this->cacheBackend = $cache_backend;
    $this->languageManager = $language_manager;
    $this->configFactory->getEditable('unfpa_global_administrative_agent.settings')
      ->set('summary_default_year', 2020)
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('config.factory'), $container->get('cache.data'), $container->get('language_manager'), $container->get('unfpa_global_administrative_agent.dashboard_data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_language_id = $this->languageManager->getCurrentLanguage()->getId();
    $summary_block_data = [];
    $current_year = date("Y");
    $summary_block_data = $this->dashboardData->getSummaryData();

    $default_year = $this->configFactory->get('unfpa_global_administrative_agent.settings')->get('summary_default_year');
    $summary_block_format_data = $this->dashboardData->getNumberFormat($summary_block_data['amount']);
    if (isset($summary_block_data['amount']['commitment'])) {
      unset($summary_block_data['amount']['commitment'][$current_year]);
      $commitment_past_year = array_sum($summary_block_data['amount']['commitment']);
      $data['commitment_past_year'] = !empty($commitment_past_year) ? $this->dashboardData->getNumberFormat($commitment_past_year) : '';
    }
    if (isset($summary_block_data['amount']['deposit'])) {
      unset($summary_block_data['amount']['deposit'][$current_year]);
      $deposit_past_year = array_sum($summary_block_data['amount']['deposit']);
      $data['deposit_past_year'] = !empty($deposit_past_year) ? $this->dashboardData->getNumberFormat($deposit_past_year) : '';
    }
    if (isset($summary_block_data['amount']['disbursement'])) {
      unset($summary_block_data['amount']['disbursement'][$current_year]);
      $disbursement_past_year = array_sum($summary_block_data['amount']['disbursement']);
      $data['disbursement_past_year'] = !empty($disbursement_past_year) ? $this->dashboardData->getNumberFormat($disbursement_past_year) : '';
    }
    $data['commit_amount_dashboard'] = !empty($summary_block_data['amount']['commitment']) ? $summary_block_format_data['commitment'][$default_year] : '';
    $data['commit_amount_dashboard'] = ($data['commit_amount_dashboard'] != '') ? $data['commit_amount_dashboard'] : '$0';

    $data['disbursement_amount_dashboard'] = !empty($summary_block_data['amount']['disbursement']) ? $summary_block_format_data['disbursement'][$default_year] : '';
    $data['disbursement_amount_dashboard'] = ($data['disbursement_amount_dashboard'] != '') ? $data['disbursement_amount_dashboard'] : '$0';

    $data['expenditure_amount_dashboard'] = !empty($summary_block_data['amount']['expenditure']) ? $summary_block_format_data['expenditure'][$default_year] : '';
    $data['expenditure_amount_dashboard'] = ($data['expenditure_amount_dashboard'] != '') ? $data['expenditure_amount_dashboard'] : '$0';

    $data['deposit_amount_dashboard'] = !empty($summary_block_data['amount']['deposit']) ? $summary_block_format_data['deposit'][$default_year] : '';
    $data['deposit_amount_dashboard'] = ($data['deposit_amount_dashboard'] != '') ? $data['deposit_amount_dashboard'] : '$0';

    return [
      '#theme' => 'summary_block',
      '#summary_block_data' => $data,
      '#attached' => [
        'drupalSettings' => [
          'summary_block_data' => $summary_block_format_data,
        ],
      ],
      '#cache' => $summary_block_data['cache'],
    ];
  }

}
