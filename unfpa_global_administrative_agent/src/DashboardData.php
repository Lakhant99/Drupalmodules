<?php

namespace Drupal\unfpa_global_administrative_agent;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Theme\ThemeManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Cache\Cache;
use Drupal\Core\State\State;

define('AA_FINANCE_TYPE', ['commitment',
  'deposit',
  'disbursement',
  'expenditure',
]);

/**
 * Logic for results page.
 */
class DashboardData {

  use StringTranslationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The cache variable.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cache;

  /**
   * The theme variable.
   *
   * @var \Drupal\Core\Theme\ThemeManager
   */
  protected $themeManager;

  /**
   * The File system variable.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * The Date format variable.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormat;

  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The state store.
   *
   * @var Drupal\Core\State\State
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entity_type_manager, AliasManagerInterface $alias_manager, CacheBackendInterface $cache, ThemeManager $theme_manager, FileSystem $file_system, DateFormatter $date_format, LanguageManager $language_manager, State $state) {
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->aliasManager = $alias_manager;
    $this->cache = $cache;
    $this->themeManager = $theme_manager;
    $this->fileSystem = $file_system;
    $this->dateFormat = $date_format;
    $this->languageManager = $language_manager;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'), $container->get('entity_type.manager'), $container->get('path.alias_manager'), $container->get('cache.data'), $container->get('theme.manager'), $container->get('file_system'), $container->get('date.formatter'), $container->get('language_manager'), $container->get('config.factory')
    );
  }

  /**
   * Retrieves Donor Dropdown Options.
   */
  public function getDonorDropdown() {
    global $base_path;
    $donor_dropdown = [];
    $query = $this->connection->select('node_field_data', 'n');
    $query->addField('n', 'nid');
    $query->addField('n', 'title');
    $query->condition('n.type', 'ct_donor');
    $query->condition('n.status', 1);
    $nodes = $query->execute()->fetchAll();
    foreach ($nodes as $n) {
      $donor_dropdown[$n->title] = $this->aliasManager->getAliasByPath($base_path . 'node/' . $n->nid);
    }
    return $donor_dropdown;
  }

  /**
   * Retrieves Partner Dropdown Options.
   */
  public function getPartnerDropdown() {
    global $base_path;
    $partner_dropdown = [];
    $query = $this->connection->select('node_field_data', 'n');
    $query->addField('n', 'nid');
    $query->addField('n', 'title');
    $query->condition('n.type', 'aa_partners');
    $query->condition('n.status', 1);
    $nodes = $query->execute()->fetchAll();
    foreach ($nodes as $n) {
      $partner_dropdown[$n->title] = $this->aliasManager->getAliasByPath($base_path . 'node/' . $n->nid);
    }
    return $partner_dropdown;
  }

  /**
   * Get map data.
   */
  public function getMapData($agency = '', $donor = '') {
    $current_year = date("Y");
    $query = $this->connection->select('node_field_data', 'n');
    $query->join('node__field_finance_fund_code', 'ffc', 'ffc.entity_id = n.nid');
    $query->join('node__field_accounting_date', 'ad', 'n.nid=ad.entity_id');
    $query->join('node__field_fund_code', 'fc', 'fc.entity_id = ffc.field_finance_fund_code_target_id');
    $query->join('node__field_finance_type', 'ft', 'ft.entity_id = n.nid');
    $query->join('node__field_donor', 'd', 'd.entity_id = n.nid');
    $query->join('node__field_finance_amount', 'fca', 'fca.entity_id = n.nid');
    $query->join('node_field_data', 'nd', 'd.field_donor_target_id = nd.nid');
    $query->leftJoin('node__field_fund_shortened_name', 'fn', 'fc.entity_id = fn.entity_id');
    $query->addField('ffc', 'field_finance_fund_code_target_id', 'fund_id');
    $query->addField('ad', 'field_accounting_date_value', 'date');
    $query->addField('d', 'field_donor_target_id', 'donor_id');
    $query->addField('fca', 'field_finance_amount_value', 'amount');
    $query->addField('nd', 'title', 'country');
    $query->addField('n', 'nid', 'nid');
    $query->addField('fn', 'field_fund_shortened_name_value', 'fund_name');
    $query->condition('ft.field_finance_type_value', 'commitment');
    $query->condition('fca.field_finance_amount_value', 0, '>');
    $query->where('YEAR(field_accounting_date_value) !=' . $current_year);
    $query->condition('n.status', '1');
    $query->condition('n.type', 'financial_data');
    $source = $query->execute()->fetchAll();

    // Query for destinations.
    $queryDest = $this->connection->select('node_field_data', 'n');
    $queryDest->join('node__field_finance_fund_code', 'ffc', 'ffc.entity_id = n.nid');
    $queryDest->join('node__field_accounting_date', 'ad', 'n.nid=ad.entity_id');
    $queryDest->join('node__field_fund_code', 'fc', 'fc.entity_id = ffc.field_finance_fund_code_target_id');
    $queryDest->join('node__field_finance_type', 'ft', 'ft.entity_id = n.nid');
    $queryDest->join('node__field_programme_country', 'pc', 'pc.entity_id = n.nid');
    $queryDest->join('node__field_fund_status', 'fa', 'fa.entity_id = ffc.field_finance_fund_code_target_id');
    $queryDest->join('node__field_finance_amount', 'fca', 'fca.entity_id = n.nid');
    $queryDest->leftJoin('node__field_fund_shortened_name', 'fn', 'fc.entity_id = fn.entity_id');
    $queryDest->leftJoin('node__field_country', 'co', 'co.entity_id=pc.field_programme_country_target_id');
    $queryDest->join('node_field_data', 'co_n', 'co_n.nid=co.field_country_target_id');
    $queryDest->addField('ffc', 'field_finance_fund_code_target_id', 'fund_id');
    $queryDest->addField('ad', 'field_accounting_date_value', 'date');
    $queryDest->addField('fca', 'field_finance_amount_value', 'amount');
    $queryDest->addField('co_n', 'title', 'country');
    $queryDest->addField('n', 'nid', 'nid');
    $queryDest->addField('fn', 'field_fund_shortened_name_value', 'fund_name');
    $queryDest->condition('ft.field_finance_type_value', 'expenditure');
    $queryDest->where('YEAR(field_accounting_date_value) !=' . $current_year);
    $queryDest->condition('ad.field_accounting_date_value', '2010-01-01 00:00:00', '>=');
    $queryDest->condition('n.status', '1');
    $queryDest->condition('n.type', 'financial_data');
    $destination = $queryDest->execute()->fetchAll();
    $result['source'] = $source;
    $result['destination'] = $destination;
    return $result;
  }

  /**
   * Get country code & name by nid.
   */
  public function getCountryDetails($nid) {
    $current_language_id = $this->languageManager->getCurrentLanguage()->getId();
    $query = $this->connection->select('node__field_country', 'fc');
    $query->join('node_field_data', 'n', 'fc.field_country_target_id = n.nid');
    $query->fields('n', ['title']);
    $query->condition('fc.entity_id', $nid);
    $query->condition('n.langcode', $current_language_id);
    $country = $query->execute()->fetchField();
    return $country;
  }

  /**
   * Count how many fund active.
   */
  public function countActiveFund($id) {
    $title = [];
    $query = $this->connection->select('node_field_data', 'n');
    $query->join('node__field_programme_country', 'pc', 'pc.entity_id = n.nid');
    $query->join('node__field_finance_fund_code', 'fc', 'fc.entity_id = n.nid');
    $query->fields('fc');
    $query->condition('pc.bundle', 'financial_data');
    $query->condition('pc.field_programme_country_target_id', $id);
    $result = $query->execute()->fetchAll();

    foreach ($result as $v) {
      $fund_short_name = $this->getFundShortName($v->field_finance_fund_code_target_id);
      $title[$v->field_finance_fund_code_target_id] = $fund_short_name;
    }
    $title = array_values($title);

    return $title;
  }

  /**
   * Formatting of a number for displaying amount.
   *
   * @param mixed $summary_data
   *   Formats the amount.
   *
   * @return string
   *   Returns a formatted amount value.
   */
  public function getNumberFormat($summary_data, $type = NULL) {
    if (is_array($summary_data)) {
      $format_amount = [];
      foreach ($summary_data as $finance_type => $year_values) {
        foreach ($year_values as $year => $number) {
          $number = (0 + str_replace(",", "", $number));
          // is this a number?
          if (!is_numeric($number)) {
            return false;
          }
          $amount_value = ($type == 'commitment') ? (round(($number / 1000000), 3) . ' ' . $this->t('million')) : ('$' . round(($number / 1000000), 3) . ' ' . $this->t('million'));
          $format_amount[$finance_type][$year] = $amount_value;
        }
      }
    }
    else {
      $number = (0 + str_replace(",", "", $summary_data));
      // is this a number?
      if (!is_numeric($number)) {
        return false;
      }
      $format_amount = ($type == 'commitment') ? (round(($number / 1000000), 3) . ' ' . $this->t('million')) : '$' . round(($number / 1000000), 3) . ' ' . $this->t('million');
    }
    return $format_amount;
  }

  /**
   * Data calculation for summary block.
   */
  public function getSummaryData($donor = NULL, $agency = NULL) {
    $result_amount = $result_data = [];
    $cache_list = ['tags' => []];
    $query = $this->connection->select('node_field_data', 'n');
    $query->join('node__field_finance_fund_code', 'ffc', 'ffc.entity_id = n.nid');
    $query->join('node__field_fund_code', 'fc', 'fc.entity_id = ffc.field_finance_fund_code_target_id');
    $query->join('node__field_finance_type', 'ft', 'ft.entity_id = n.nid');
    $query->join('node__field_fund_status', 'fa', 'fa.entity_id = ffc.field_finance_fund_code_target_id');
    $query->join('node__field_finance_amount', 'fca', 'fca.entity_id = n.nid');
    $query->addField('ffc', 'field_finance_fund_code_target_id', 'fund_id');
    $query->addField('fca', 'field_finance_amount_value', 'amount');
    $query->addField('ft', 'field_finance_type_value', 'finance_type');
    $query->addField('n', 'nid', 'nid');
    $query->leftJoin('node__field_accounting_date', 'ad', 'n.nid=ad.entity_id');
    $query->addField('ad', 'field_accounting_date_value', 'accounting_date');
    $query->condition('ft.field_finance_type_value', ['commitment', 'deposit', 'disbursement',
      'expenditure',
      ], 'IN');
    $query->leftjoin('node__field_donor', 'd', 'd.entity_id = n.nid');
    $query->leftjoin('node__field_agency', 'ag', 'ag.entity_id = n.nid');
    if (!empty($donor)) {
      $query->condition('d.field_donor_target_id', $donor);
    }
    if (!empty($agency)) {
      $query->condition('ag.field_agency_target_id', $agency);
    }

    $query->condition('n.status', '1');
    $query->condition('n.type', 'financial_data');

    $result = $query->execute()->fetchAll();

    foreach ($result as $val) {
      $date_year = date('Y', strtotime($val->accounting_date));
      if (isset($result_amount[$val->finance_type][$date_year])) {
        $result_amount[$val->finance_type][$date_year] += $val->amount;
      }
      else {
        $result_amount[$val->finance_type][$date_year] = $val->amount;
      }
      $cache_list['tags'][] = 'node:' . $val->nid;
    }
    $result_data['amount'] = $result_amount;
    $result_data['cache'] = $cache_list;
    return $result_data;
  }

  /**
   * Function to generate finance data for charts.
   *
   * @return array
   *   Return array of Financial Data.
   */
  public function getFinanceData($donor_id = NULL, $agency_id = NULL, $fund_id = NULL) {
    $record = $this->getFundsData($donor_id, $agency_id, $fund_id);
    $finance_data = $overtime_dashboard = $result = $date_dropdown = [];
    $cache_list = ['tags' => []];
    foreach ($record as $key => $val) {
      if (in_array($val->finance_type, AA_FINANCE_TYPE)) {
        $date = $val->accounting_date;

        if ($val->finance_type == 'commitment') {
          $finance_type = 'Commitments';
        }
        elseif ($val->finance_type == 'deposit') {
          $finance_type = 'Deposits';
        }
        elseif ($val->finance_type == 'disbursement') {
          $finance_type = 'Transfers';
        }
        elseif ($val->finance_type == 'expenditure') {
          $finance_type = 'Expenditure';
        }

        $date_year = date('Y', strtotime($date));
        $date_dropdown[$date_year] = $date_year;
        $date = date('Y-m-d', strtotime($date_year . '-04-09'));

        $overtime_dashboard[$val->finance_type][$date_year]['date'] = $date_year;
        $overtime_dashboard[$val->finance_type][$date_year]['type'] = $finance_type;
        if (isset($overtime_dashboard[$val->finance_type][$date_year]['value'])) {
          $overtime_dashboard[$val->finance_type][$date_year]['value'] += is_numeric($val->finance_amount) ? intval($val->finance_amount) : 0;
        }
        else {
          $overtime_dashboard[$val->finance_type][$date_year]['value'] = is_numeric($val->finance_amount) ? intval($val->finance_amount) : 0;
        }

        $finance_data[$date_year][$finance_type]['type'] = $finance_type;
        if (isset($finance_data[$date_year][$finance_type]['sum'])) {
          $finance_data[$date_year][$finance_type]['sum'] += is_numeric($val->finance_amount) ? intval($val->finance_amount) : 0;
        }
        else {
          $finance_data[$date_year][$finance_type]['sum'] = is_numeric($val->finance_amount) ? intval($val->finance_amount) : 0;
        }
        $finance_data[$date_year][$finance_type]['date'] = $date;
        $cache_list['tags'][] = 'node:' . $val->nid;
      }
    }
    if (empty($donor_id) && empty($agency_id)) {
      $finance_types_array = ['Commitments',
        'Deposits',
        'Transfers',
        'Expenditure',
      ];
    }
    else {
      if (!empty($donor_id)) {
        $finance_types_array = ['Commitments', 'Deposits'];
      }
      if (!empty($agency_id)) {
        $finance_types_array = ['Transfers', 'Expenditure'];
      }
    }
    foreach ($finance_data as $key => $val) {
      foreach ($finance_types_array as $fin_arr) {
        if (isset($val[$fin_arr])) {
          $new_val[$fin_arr] = $val[$fin_arr];
        }
        else {
          $date = $key . '-04-09';
          $new_val[$fin_arr]['type'] = $fin_arr;
          $new_val[$fin_arr]['sum'] = 0;
          $new_val[$fin_arr]['date'] = (string) $this->dateFormat->format(strtotime($date), 'custom', 'Y-m-d');
        }
      }
      foreach ($new_val as $v) {
        $finance[] = $v;
      }
    }
    $overtime_dashboard_final = [];
    foreach ($overtime_dashboard as $over_key => $overtime) {
      ksort($overtime);
      $overtime_dashboard_final[$over_key] = array_values($overtime);
    }
    arsort($date_dropdown);
    $result['overtime_dashboard'] = $overtime_dashboard_final;
    $result['finance_chart'] = $finance;
    $result['date_dropdown'] = $date_dropdown;
    $result['start_date'] = array_shift($date_dropdown);
    $result['cache'] = $cache_list;
    return $result;
  }

  /**
   * Function to get financial data.
   *
   * @param mixed $donor
   *   Donor entity id.
   * @param mixed $agency
   *   Agency entity id.
   * @param mixed $fund_id
   *   Fund entity id.
   *
   * @return mixed
   *   Returns Funds data
   */
  public function getFundsData($donor = NULL, $agency = NULL, $fund_id = NULL) {
    $query = $this->connection->select('node_field_data', 'n');
    $query->leftJoin('node__field_finance_amount', 'fa', 'n.nid=fa.entity_id');
    $query->leftJoin('node__field_finance_type', 'ft', 'n.nid=ft.entity_id');
    $query->leftJoin('node__field_accounting_date', 'ad', 'n.nid=ad.entity_id');
    $query->leftJoin('node__field_finance_fund_code', 'fc', 'n.nid=fc.entity_id');
    $query->leftJoin('node__field_programme_country', 'pc', 'n.nid=pc.entity_id');
    $query->condition('n.type', 'financial_data');
    $query->condition('n.status', 1);
    $query->leftjoin('node__field_donor', 'd', 'd.entity_id = n.nid');
    $query->leftjoin('node__field_agency', 'ag', 'ag.entity_id = n.nid');
    if (!empty($donor)) {
      $query->condition('d.field_donor_target_id', $donor);
    }
    if (!empty($agency)) {
      $query->condition('ag.field_agency_target_id', $agency);
    }
    if (!empty($fund_id)) {
      $query->condition('fc.field_finance_fund_code_target_id', $fund_id);
    }
    $query->condition('ad.field_accounting_date_value', '2010-01-01', '>=');
    $query->fields('n', ['nid']);
    $query->addField('ad', 'field_accounting_date_value', 'accounting_date');
    $query->addField('fa', 'field_finance_amount_value', 'finance_amount');
    $query->addField('ft', 'field_finance_type_value', 'finance_type');
    $query->addField('fc', 'field_finance_fund_code_target_id', 'fund_id');
    $query->addField('pc', 'field_programme_country_target_id', 'pc_id');
    $query->addField('d', 'field_donor_target_id', 'donor_id');
    $query->addField('ag', 'field_agency_target_id', 'agency_id');
    $funds_data = $query->execute()->fetchAll();
    return $funds_data;
  }

  /**
   * Gets Sankey chart data.
   */
  public function getSankeyChartData($donor = NULL, $agency = NULL) {
    $data_record = $donut_chart_data = $date_dropdown = [];
    $cache_list = ['tags' => []];
    $finance_type_array = ['commitment',
      'deposit',
      'disbursement',
      'expenditure',
    ];
    $record = $this->getFundsData($donor, $agency);
    $theme_path = $this->themeManager->getActiveTheme()->getPath();
    if (!empty($record)) {
      foreach ($record as $val) {
        $source = $target = '';
        $date = $val->accounting_date;
        $fund_name = !empty($val->fund_id) ? $this->getFundShortName($val->fund_id) : '';
        switch ($val->finance_type) {
          case 'commitment':
            $finance_type = 'commitments';
            if (!empty($val->donor_id)) {
              $giver = $val->donor_id;
              $source = $this->getTitle($val->donor_id);
            }
            $target = $fund_name;
            $verb = 'committed';
            break;

          case 'deposit':
            $verb = 'deposited';
            $finance_type = 'deposits';
            if (!empty($val->donor_id)) {
              $giver = $val->donor_id;
              $source = $this->getTitle($val->donor_id);
            }
            $target = $fund_name;
            break;

          case 'disbursement':
            $verb = 'disbursed';
            $finance_type = 'transfers';
            if (!empty($val->agency_id)) {
              $giver = $val->agency_id;
              $target = $this->getTitle($val->agency_id);
            }
            $source = $fund_name;
            break;

          case 'expenditure':
            $verb = 'spent';
            $finance_type = 'expenditure';
            if (!empty($val->agency_id)) {
              $giver = $val->agency_id;
              $target = $this->getTitle($val->agency_id);
            }
            $source = $fund_name;
            break;

          default:
            break;
        }
        if (in_array($val->finance_type, $finance_type_array)) {
          $date_year = date('Y', strtotime($date));
          $date = $this->dateFormat->format(strtotime($date . '-04-09'), 'custom', 'Y-m-d');
          if (!empty($val->finance_amount)) {
            if (!empty($source) && !empty($target)) {
              $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['nid'] = $val->nid;
              $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['source'] = $source;
              $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['target'] = $target;
              if (isset($data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['value'])) {
                $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['value'] += is_numeric($val->finance_amount) ? (int) $val->finance_amount : 0;
              }
              else {
                $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['value'] = is_numeric($val->finance_amount) ? (int) $val->finance_amount : 0;
              }
              $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['type'] = $finance_type;
              $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['date'] = $date;
              $data_record[$date_year . '-' . $val->finance_type . '-' . $giver . '-' . $val->fund_id]['verb'] = $verb;

              $donut_data[$val->finance_type][$date_year][$fund_name]['name'] = $fund_name;
              if (isset($donut_data[$val->finance_type][$date_year][$fund_name]['value'])) {
                $donut_data[$val->finance_type][$date_year][$fund_name]['value'] += is_numeric($val->finance_amount) ? intval($val->finance_amount) : 0;
              }
              else {
                $donut_data[$val->finance_type][$date_year][$fund_name]['value'] = is_numeric($val->finance_amount) ? intval($val->finance_amount) : 0;
              }
              $donut_data[$val->finance_type][$date_year][$fund_name]['date'] = $date_year;
              $donut_data[$val->finance_type][$date_year][$fund_name]['verb'] = $verb;
              $cache_list['tags'][] = 'node:' . $val->nid;
            }
          }
        }
      }
    }

    $data_array = [];
    $i = 0;
    if (!empty($data_record)) {
      foreach ($data_record as $dat_rec) {
        $data_array[$i]['source'] = $dat_rec['source'];
        $data_array[$i]['target'] = $dat_rec['target'];
        $data_array[$i]['value'] = $dat_rec['value'];
        $data_array[$i]['type'] = $dat_rec['type'];
        $data_array[$i]['date'] = $dat_rec['date'];
        $data_array[$i]['verb'] = $dat_rec['verb'];
        $format_value = $this->getNumberFormat($dat_rec['value']);
        $date = date('Y', strtotime($dat_rec['date']));
        $date_dropdown[$date] = $date;
        $image_path = $theme_path . '/images/arrow-right_dark.svg';
        $tooltip = $dat_rec['source'] . '<img src=' . $image_path . '>' . $dat_rec['target'] . '<br><strong>' . $format_value . ' ' . $dat_rec['verb'] . ' in ' . $date . '<strong>';
        $data_array[$i]['tooltip'] = $tooltip;
        $i++;
      }
    }

    if (!empty($donut_data)) {
      foreach ($donut_data as $fin_key => $don_data) {
        foreach ($don_data as $fin_year => $d_data) {
          $i = 0;
          foreach ($d_data as $d_d) {
            $donut_chart_data[$fin_key][$fin_year][$i]['name'] = $d_d['name'];
            $donut_chart_data[$fin_key][$fin_year][$i]['value'] = $d_d['value'];
            $format_value = $this->getNumberFormat($d_d['value']);
            $tooltip = $d_d['name'] . '<br><strong>' . $format_value . ' ' . $d_d['verb'] . ' in ' . $d_d['date'] . '<strong>';
            $donut_chart_data[$fin_key][$fin_year][$i]['tooltip'] = $tooltip;
            $i++;
          }
        }
      }
    }
    arsort($date_dropdown);
    $data['sankey'] = $data_array;
    $data['date_dropdown'] = $date_dropdown;
    $data['start_date'] = array_shift($date_dropdown);
    $data['donut'] = $donut_chart_data;
    $data['cache'] = $cache_list;
    return $data;
  }

  /**
   * Retrieves Transaction Register Data.
   */
  public function getTransactionRegisterData($finance_type, $donor = NULL, $agency = NULL, $offset = NULL, $fund_id = NULL) {
    $finance_type_array = ['commitment', 'deposit', 'disbursement'];
    $cache_list = ['tags' => []];
    $query = $this->connection->select('node_field_data', 'n');
    $query->leftJoin('node__field_finance_amount', 'fa', 'n.nid=fa.entity_id');
    $query->leftJoin('node__field_finance_type', 'ft', 'n.nid=ft.entity_id');
    $query->leftJoin('node__field_accounting_date', 'ad', 'n.nid=ad.entity_id');
    $query->leftJoin('node__field_finance_fund_code', 'fc', 'n.nid=fc.entity_id');
    $query->leftJoin('node__field_programme_country', 'pc', 'n.nid=pc.entity_id');
    $query->leftJoin('node__field_donor', 'fd', 'n.nid=fd.entity_id');
    $query->leftJoin('node__field_agency', 'fag', 'n.nid=fag.entity_id');
    $query->where('YEAR(field_accounting_date_value) = 2019');
    $query->condition('ft.field_finance_type_value', $finance_type_array, 'IN');
    $query->condition('n.type', 'financial_data');
    $query->condition('n.status', 1);
    if ($donor) {
      $query->condition('fd.field_donor_target_id', $donor);
    }
    if ($agency) {
      $query->condition('fag.field_agency_target_id', $agency);
    }
    if ($finance_type) {
      $query->condition('ft.field_finance_type_value', $finance_type);
    }
    if ($fund_id) {
      $query->condition('fc.field_finance_fund_code_target_id', $fund_id);
    }
    $query->condition('fa.field_finance_amount_value', 0, '>');
    $query->orderBy('ad.field_accounting_date_value', 'DESC');
    $query->fields('n', ['nid']);
    $query->fields('fd', ['field_donor_target_id']);
    $query->fields('fag', ['field_agency_target_id']);
    $query->addField('fa', 'field_finance_amount_value', 'finance_amount');
    $query->addField('ft', 'field_finance_type_value', 'finance_type');
    $query->addField('ad', 'field_accounting_date_value', 'accounting_date');
    $query->addField('fc', 'field_finance_fund_code_target_id', 'fund_id');
    $query->addField('pc', 'field_programme_country_target_id', 'pc_id');
    $num_rows = $query->countQuery()->execute()->fetchField();
    if (empty($offset)) {
      // Added range for load more functionality.
      $query->range(0, 5);
    }
    else {
      // Added range for load more functionality.
      $query->range($offset, 6);
    }

    $record = $query->execute()->fetchAll();
    $transaction = [];
    foreach ($record as $key => $val) {

      $fund_name = !empty($val->fund_id) ? $this->getFundShortName($val->fund_id) : '';
      $date = $val->accounting_date;

      if ($val->finance_type == 'commitment') {
        $finance_type = 'commitments';
        $donors = !empty($val->field_donor_target_id) ? $this->getTitle($val->field_donor_target_id) : '';
      }
      elseif ($val->finance_type == 'deposit') {
        $finance_type = 'deposits';
        $donors = !empty($val->field_donor_target_id) ? $this->getTitle($val->field_donor_target_id) : '';
      }
      elseif ($val->finance_type == 'disbursement') {
        $finance_type = 'transfer';
        $donors = !empty($val->field_agency_target_id) ? $this->getTitle($val->field_agency_target_id) : '';
      }
      $transaction_date = $this->dateFormat->format(strtotime($date), 'custom', 'd M Y');
      $transaction[$finance_type][$key]['nid'] = $val->nid;
      $transaction[$finance_type][$key]['type'] = $finance_type;
      $transaction[$finance_type][$key]['amount'] = $this->getNumberFormat(intval($val->finance_amount));
      $transaction[$finance_type][$key]['date'] = $transaction_date;
      $transaction[$finance_type][$key]['fund'] = $fund_name;
      $transaction[$finance_type][$key]['donar'] = $donors;
      $count[$finance_type] = $num_rows;
      $transaction['count'] = $count;
      if (empty($offset)) {
        $cache_list['tags'][] = 'node:' . $val->nid;
        $transaction['cache'] = $cache_list;
      }
    }
    return $transaction;
  }

  /**
   * Function to give Active funds data.
   */
  public function getActiveFunds($donor = NULL, $agency = NULL) {
    $finance_data = $this->getFundsData($donor, $agency);
    $fund_ids = $result = [];
    if (!empty($donor) || !empty($agency)) {
      foreach ($finance_data as $fin_data) {
        $fund_ids[$fin_data->fund_id] = $fin_data->fund_id;
      }
    }
    $query = $this->connection->select('node_field_data', 'n');
    $query->join('node__field_fund_status', 'fs', 'fs.entity_id = n.nid');
    $query->join('node__field_image', 'fi', 'fi.entity_id = n.nid');
    $query->leftJoin('file_managed', 'fm', 'fm.fid=fi.field_image_target_id');
    $query->fields('n', ['title', 'nid']);
    $query->fields('fi', ['field_image_target_id']);
    $query->fields('fm', ['uri']);
    $query->fields('fs', ['field_fund_status_value']);
    if (!empty($fund_ids)) {
      $query->condition('n.nid', $fund_ids, 'IN');
    }
    $result = $query->execute()->fetchAll();

    return $result;
  }

  /**
   * Data of block for Home page Counter.
   */
  public function homePageCounter() {
    $result = [];
    $donor_nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'ct_donor',
      'status' => '1',
    ]);
    $result['Donors'] = count($donor_nodes);

    $fund_nodes = $this->entityTypeManager->getStorage('node')->loadByProperties([
      'type' => 'ct_funds',
      'status' => '1',
      'field_fund_status' => 'active',
    ]);
    $result['Funds'] = count($fund_nodes);

    $country = $this->connection->select('node_field_data', 'n');
    $country->join('node__field_programme_country', 'pc', 'pc.entity_id = n.nid');
    $country->condition('n.type', 'ct_funds');
    $country->condition('n.status', 1);
    $country->fields('pc', ['field_programme_country_target_id']);
    $result['Countries'] = $country->countQuery()->execute()->fetchField();
    return $result;
  }

  /**
   * Function for getting homepage map data.
   */
  public function getHomeMapData() {
    if ($home_map_data = $this->cache->get('aa_home_map_data')) {
      $result = $home_map_data->data;
    }
    else {
      $result = $date_dropdown = [];
      $cache_list = ['tags' => []];
      $donorNameArray = ['UN Women'];
      $query = $this->connection->select('node_field_data', 'n');
      $query->join('node__field_finance_fund_code', 'ffc', 'ffc.entity_id = n.nid');
      $query->join('node__field_accounting_date', 'ad', 'n.nid=ad.entity_id');
      $query->join('node__field_fund_code', 'fc', 'fc.entity_id = ffc.field_finance_fund_code_target_id');
      $query->join('node__field_finance_type', 'ft', 'ft.entity_id = n.nid');
      $query->join('node__field_donor', 'd', 'd.entity_id = n.nid');
      $query->join('node__field_fund_status', 'fa', 'fa.entity_id = ffc.field_finance_fund_code_target_id');
      $query->addField('ffc', 'field_finance_fund_code_target_id', 'fund_id');
      $query->addField('ad', 'field_accounting_date_value', 'date');
      $query->addField('n', 'nid', 'nid');
      $query->addField('d', 'field_donor_target_id', 'donor_id');
      $query->condition('ft.field_finance_type_value', 'commitment');
      $query->condition('ad.field_accounting_date_value', '2010-01-01 00:00:00', '>=');
      $query->condition('n.status', '1');
      $query->condition('n.type', 'financial_data');
      $source = $query->execute()->fetchAll();

      if (is_array($source) && count($source)) {
        foreach ($source as $val) {
          $fund_data = [];
          $fund_cache_id = "aa_home_map_fund_{$val->fund_id}_{$val->donor_id}";
          if ($home_fund_data = $this->cache->get($fund_cache_id)) {
            $cache_home_fund_data = $home_fund_data->data;
            $date_dropdown = $cache_home_fund_data['date_dropdown'];
            $home_map = $cache_home_fund_data['home_map'];
          }
          else {
            $query_d = $this->connection->select('node_field_data', 'n');
            $query_d->leftJoin('node__field_accounting_date', 'ad', 'n.nid=ad.entity_id');
            $query_d->join('node__field_finance_type', 'ft', 'ft.entity_id = n.nid');
            $query_d->join('node__field_agency', 'ag', 'ag.entity_id = n.nid');
            $query_d->join('node__field_programme_country', 'pc', 'pc.entity_id = n.nid');
            $query_d->join('node__field_finance_fund_code', 'fc', 'fc.entity_id = n.nid');
            $query_d->join('node__field_finance_amount', 'fa', 'fa.entity_id = n.nid');
            $query_d->addField('ad', 'field_accounting_date_value', 'date');
            $query_d->addField('ag', 'field_agency_target_id', 'agency_id');
            $query_d->addField('fa', 'field_finance_amount_value', 'amount');
            $query_d->addField('pc', 'field_programme_country_target_id', 'pc_id');
            $query_d->addField('n', 'nid', 'nid');
            $query_d->condition('ft.field_finance_type_value', 'expenditure');
            $query_d->condition('fc.field_finance_fund_code_target_id', $val->fund_id);
            //$query_d->condition('fa.field_finance_amount_value', 0, '>');
            $query_d->condition('ad.field_accounting_date_value', '2010-01-01 00:00:00', '>=');
            $query_d->condition('n.status', '1');
            $query_d->condition('n.type', 'financial_data');
            $destination = $query_d->execute()->fetchAll();

            if (is_array($destination) && count($destination)) {

              foreach ($destination as $v) {
                if($v->amount > 0){
                  $country_name = $this->getCountryDetails($v->pc_id);
                  $date_year = date('Y', strtotime($v->date));
                  $donor_name = $this->getTitle($val->donor_id);
                  if (!in_array($donor_name, $donorNameArray)) {
                    $home_map[$date_year . '-' . $donor_name . '-' . $country_name]['origin'] = $donor_name;
                    $home_map[$date_year . '-' . $donor_name . '-' . $country_name]['destination'] = $country_name;
                    $home_map[$date_year . '-' . $donor_name . '-' . $country_name]['date'] = (string) date('Y-m-d', strtotime($date_year . '-04-09'));

                    if (isset($home_map[$date_year . '-' . $donor_name . '-' . $country_name]['amount'])) {
                      $home_map[$date_year . '-' . $donor_name . '-' . $country_name]['amount'] += is_numeric($v->amount) ? intval($v->amount) : 0;
                    }
                    else {
                      $home_map[$date_year . '-' . $donor_name . '-' . $country_name]['amount'] = is_numeric($v->amount) ? intval($v->amount) : 0;
                    }
                    $date_year = (string) date('Y', strtotime($v->date));
                    $date_dropdown[$date_year] = $date_year;
                    $cache_list['tags'][] = 'node:' . $v->nid;
                    $fund_data['date_dropdown'] = $date_dropdown;
                    $fund_data['home_map'] = $home_map;
                  }
                }
              }
            }

            if (!empty($fund_data)) {
              $this->cache->set($fund_cache_id, $fund_data);
            }
            $cache_list['tags'][] = 'node:' . $val->nid;
          }
        }

        $result['map_data'] = array_values($home_map);
        sort($date_dropdown);
        $date_dropdown = array_reverse($date_dropdown);
        $result['start_date'] = $date_dropdown[0];
        $result['date_dropdown'] = $date_dropdown;
        $result['cache'] = $cache_list;

        $this->cache->set('aa_home_map_data', $result, CacheBackendInterface::CACHE_PERMANENT, $cache_list['tags']);
      }
    }
    return $result;
  }

  /**
   * Get the title by passing id.
   */
  public function getTitle($id) {
    $query = $this->connection->select('node_field_data', 'n');
    $query->fields('n', ['title']);
    $query->condition('n.nid', $id);
    $title = $query->execute()->fetchField();
    return $title;
  }

  /**
   * Get shortname for Fund.
   *
   * Fund id.
   *
   * @param mixed $fund_id
   *
   *   Short name.
   *
   * @return mixed
   */
  public function getFundShortName($fund_id) {
    $query = $this->connection->select('node_field_data', 'n');
    $query->join('node__field_fund_shortened_name', 'fsn', 'fsn.entity_id = n.nid');
    $query->fields('fsn', ['field_fund_shortened_name_value']);
    $query->condition('n.nid', $fund_id);
    $fund_short_name = $query->execute()->fetchField();
    return $fund_short_name;
  }

  /**
   * Get list of pages for banner image.
   */
  public function getPagesList() {
    return [
      'home' => $this->t("Home"),
      'about-funds' => $this->t("About Us"),
      'dashboard' => $this->t("Dashboard"),
      'news-and-publications' => $this->t("News And Publications"),
    ];
  }

  /**
   * Function to set static variable for additional amount.
   */
  public function getAdditionalAmountVars() {
    return [
      '2014' => $this->state->get('aa_hq_2014', '4,124,677.68'),
      '2015' => $this->state->get('aa_hq_2015', '13,391,770.73'),
      '2016' => $this->state->get('aa_hq_2016', '14,231,723.31'),
      '2017' => $this->state->get('aa_hq_2017', '8,120,658.88'),
      '2018' => $this->state->get('aa_hq_2018', '6,020,327.20'),
      '2019' => $this->state->get('aa_hq_2019', '7,697,739.20'),
      '2020' => $this->state->get('aa_hq_2020', '7,087,913.06'),
      '2010-2015' => $this->state->get('aa_hq_2010_2015', '65,837,131.28'),
    ];
  }

}
