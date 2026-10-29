<?php

namespace Drupal\unfpa_global_custom_funds\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Theme\ThemeManager;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Url;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Form\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Database\Connection;

/**
 * World Population Dashboard Page Content.
 */
class IncomingfundsChartDashboard extends ControllerBase {

  use StringTranslationTrait;

  /**
   * @var Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The ModuleHandlerInterface service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The Theme Manager service.
   *
   * @var \Drupal\Core\Theme\ThemeManager
   */
  protected $themeManager;

  /**
   * Indicators filter variable.
   */
  protected $filters;

  /**
   * The HttpRequest Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Language Manager.
   *
   * @var Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The FormBuilder Manager.
   *
   * @var Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritdoc}
   */
  public function __construct(ModuleHandlerInterface $module_handler, RequestStack $request_stack, LanguageManager $language_manager, CacheBackendInterface $cache_backend, FormBuilder $form_builder, ThemeManager $theme_manager, Connection $connection) {
    $this->filters = [];
    $this->moduleHandler = $module_handler;
    $this->themeManager = $theme_manager;
    $this->requestStack = $request_stack;
    $this->languageManager = $language_manager;
    $this->cacheBackend = $cache_backend;
    $this->formBuilder = $form_builder;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('request_stack'),
      $container->get('language_manager'),
      $container->get('cache.default'),
      $container->get('form_builder'),
      $container->get('theme.manager'),
      $container->get('database')
    );
  }


  /**
   * Returns title for world-population dashboard page.
   */
  public function getTitle() {
    return $this->t('World Population Dashboard');
  }

  /**
   * Return json map data for wp dashboard.
   *
   * @return Symfony\Component\HttpFoundation\JsonResponse
   *   The JsonResponse object.
   */
  public function fundsChartUpdate() {
    
    $final_data = [];
    $data = array();
    $color_array = array();
    $color_array = $this->unique_colors();
    $year = $_POST['year'];
    $query = $this->connection->select('incoming_funds', 'i');
    $query->fields('i')->condition('i.year', $year);
    $query->orderBy('i.country_name', 'ASC');
    $result = $query->execute()->fetchAll();
    $i = 0;
    $total_array = array();
    $total_fund = array();
    $array_org = array();
    $total_org = array();
    foreach ($result as $ifund) {
      $cname = $ifund->country_name;
      $year = $ifund->year;
      $deposit = $ifund->deposit;
      $fund_name = $ifund->fund_name;
      $total_array[$cname][$i] = $deposit;
      $array_org[$fund_name][$i] = $deposit;
      $total_fund[$cname][$fund_name][$i] = $deposit;
      $total_org[$fund_name][$cname][$i] = $deposit;
      $i++;
    }

    $data['incoming_funds']['total_array'] = $total_array;
    $data['incoming_funds']['total_fund'] = $total_fund;
    $data['incoming_funds']['array_org'] = $array_org;
    $data['incoming_funds']['total_org'] = $total_org;
    $final_data['DonutChartIncFunds'] = $this->ajaxprepare_donutchart_inc_funds($data['incoming_funds']['total_array'], $color_array);
    $final_data['BarChartIncFunds'] = $this->ajax_bar_chart_from_donut_inc_funds($data['incoming_funds']['total_fund'], $color_array);
    $final_data['DonutChartFundsIncFunds'] = $this->ajax_donut_chart_funds_inc_funds($data['incoming_funds']['array_org'], $color_array);
    $final_data['BarChartFundsIncFunds'] = $this->ajax_bar_chart_from_donut_funds_inc_funds($data['incoming_funds']['total_org'], $color_array);
    // For disbursement
    $data = array();
    $query = $this->connection->select('disbursement', 'd');
    $query->fields('d')->condition('d.year', $year);
    $query->orderBy('d.org_name', 'ASC');
    $result = $query->execute()->fetchAll();
    $i = 0;
    $total_array = array();
    $total_fund = array();
    $array_org = array();
    $total_org = array();
    foreach ($result as $ifund) {
      $org_name = $ifund->org_name;
      $disbursement = $ifund->disbursement;
      $disb_name = $ifund->disb_name;
      $ifund->fund_code;
      $total_array[$org_name][$i] = $disbursement;
      $array_org[$disb_name][$i] = $disbursement;
      $total_fund[$org_name][$disb_name][$i] = $disbursement;
      $total_org[$disb_name][$org_name][$i] = $disbursement;
      $i++;
    }

    $data['disbursement']['total_array'] = $total_array;
    $data['disbursement']['total_fund'] = $total_fund;
    $data['disbursement']['array_org'] = $array_org;
    $data['disbursement']['total_org'] = $total_org;
    $final_data['DonutChartDisbursement'] = $this->ajax_donut_chart_disbursement($data['disbursement']['total_array'], $color_array);
    $final_data['BarChartDisbursement'] = $this->ajax_bar_chart_from_donut_disbursement($data['disbursement']['total_fund'], $color_array);
    $final_data['DonutChartOrgDisbursement'] = $this->ajax_donut_chart_funds_disbursement($data['disbursement']['array_org'], $color_array);
    $final_data['BarChartOrgDisbursement'] = $this->ajax_bar_chart_from_donut_funds_disbursement($data['disbursement']['total_org'], $color_array);
    //For expenditure

    $data = array();
    $query = $this->connection->select('expenditure', 'e');
    $query->fields('e')->condition('e.year', $year);
    ;
    $query->orderBy('e.org_name', 'ASC');
    $result = $query->execute()->fetchAll();
    $i = 0;
    $total_array = array();
    $total_fund = array();
    $array_org = array();
    $total_org = array();
    foreach ($result as $ifund) {
      $org_name = $ifund->org_name;
      $expenditure = $ifund->expenditure;
      $fund_name = $ifund->fund_name;
      $ifund->fund_code;
      $total_expenditure[$org_name][$i] = $expenditure;
      $array_organization[$fund_name][$i] = $expenditure;
      $total_funds[$org_name][$fund_name][$i] = $expenditure;
      $total_organization[$fund_name][$org_name][$i] = $expenditure;
      $i++;
    }
    $data['expenditure']['total_expenditure'] = $total_expenditure;
    $data['expenditure']['total_funds'] = $total_funds;
    $data['expenditure']['array_organization'] = $array_organization;
    $data['expenditure']['total_organization'] = $total_organization;
    $final_data['DonutChartExpenditure'] = $this->ajax_donut_chart_expenditure($data['expenditure']['total_expenditure'], $color_array);
    $final_data['BarChartExpenditure'] = $this->ajax_bar_chart_from_donut_expenditure($data['expenditure']['total_funds'], $color_array);
    $final_data['DonutChartFundsExpenditure'] = $this->ajax_donut_chart_funds_expenditure($data['expenditure']['array_organization'], $color_array);
    $final_data['BarChartFundsExpenditure'] = $this->ajax_bar_chart_from_donut_funds_expenditure($data['expenditure']['total_organization'], $color_array);

    return new JsonResponse($final_data);
  }

  /**
   * Checks access for Map.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function isfundsChartAccessible(AccountInterface $account) {
    // Cant's use DependencyInjection here... AccessResult not available as service.
    if (isset($_POST['year'])) {
      return TRUE;
    }
    return AccessResult::allowedIf($account->hasPermission('access content'));
  }

  /**
   * This function is used to return the distinct colors for color array used in google charts.
   * @return array
   */
  public function unique_colors() {
    $funds = array();
    $unique_colors = array();
    $color_array = array('#D24D57', '#DB0A5B', '#DCC6E0', '#446CB3', '#4ECDC4', '#F5D76E', '#FDE3A7', '#F22613', '#F64747', '#663399', '#A2DED0', '#F7CA18', '#F89406', '#D91E18', '#F1A9A0', '#674172', '#4183D7', '#87D37C', '#F4D03F', '#EB9532', '#96281B', '#D2527F', '#AEA8D3', '#59ABE3', '#90C695', '#E87E04', '#EF4836', '#E08283', '#913D88', '#81CFE0', '#26A65B', '#F4B350', '#D64541', '#F62459', '#9A12B3', '#52B3D9', '#03C9A9', '#F2784B', '#C0392B', '#F8991D', '#BF55EC', '#1CACE2', '#68C3A3', '#6AB9E6', '#0A294E', '#BE90D4', '#22A7F0', '#65C6BB', '#F5AB35', '#E74C3C', '#8E44AD', '#3498DB', '#1BBC9B', '#D35400', '#9B59B6', '#2C3E50', '#1BA39C', '#F39C12', '#19B5FE', '#66CC99', '#F9690E', '#336E7B', '#36D7B7', '#F9BF3B', '#22313F', '#C8F7C5', '#F27935', '#6BB9F0', '#86E2D5', '#E67E22', '#1E8BC3', '#2ECC71', '#3A539B', '#16A085', '#34495E', '#3FC380', '#67809F', '#019875', '#2574A9', '#03A678', '#1F3A93', '#4DAF7C', '#89C4F4', '#2ABB9B', '#4B77BE', '#00B16A', '#5C97BF', '#1E824C', '#049372', '#26C281');
    $i = 0;
    $result = $this->connection->query('SELECT DISTINCT country_name FROM {incoming_funds} ORDER BY country_name ASC');
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $funds[$i] = $row['country_name'];
        $i++;
      }
    }
    $result = $this->connection->query('SELECT DISTINCT org_name FROM {disbursement} ORDER BY org_name ASC');
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $funds[$i] = $row['org_name'];
        $i++;
      }
    }
    $result = $this->connection->query('SELECT DISTINCT org_name FROM {expenditure} ORDER BY org_name ASC');
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $funds[$i] = $row['org_name'];
        $i++;
      }
    }
    $result = $this->connection->query('SELECT DISTINCT fund_name FROM {incoming_funds} ORDER BY fund_name ASC');
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $funds[$i] = $row['fund_name'];
        $i++;
      }
    }
    $result = $this->connection->query('SELECT DISTINCT disb_name FROM {disbursement} ORDER BY disb_name ASC');
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $funds[$i] = $row['disb_name'];
        $i++;
      }
    }
    $result = $this->connection->query('SELECT DISTINCT fund_name FROM {expenditure} ORDER BY fund_name ASC');
    if ($result) {
      while ($row = $result->fetchAssoc()) {
        $funds[$i] = $row['fund_name'];
        $i++;
      }
    }
    $funds = array_unique($funds);
    $i = 0;
    foreach ($funds as $value) {
      $unique_colors[$value] = $color_array[$i];
      $i++;
    }
    return $unique_colors;
  }

  /**
   * Incoming Funds Donut chart
   * Returns array for Donut chart for Incoming Funds for "By Donor" tab.
   * @param array $total_array
   * @param array $fund_colors
   * @return array
   */
  public function ajaxprepare_donutchart_inc_funds($total_array, $fund_colors) {
    $color_array = array('#f44336', '#E91E63', '#9C27B0', '#673AB7', '#3F51B5', '#2196F3', '#03A9F4', '#00BCD4', '#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722', '#795548', '#9E9E9E', '#607D8B');
    $do_chart_rows_arr = array();
    $do_chart_slices_arr = array();
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Percentage', 'label' => 'Percentage', 'pattern' => '', 'type' => 'number')
    );
    $i = 0;
    foreach ($total_array as $co => $graph_activity) {
      $total_year_budget = array_sum($graph_activity); // Total Year Array Budget
      array_push($do_chart_rows_arr, array('c' => array(
          array('v' => $co),
          array('v' => $total_year_budget)
        )
      ));
      $do_chart_slices_arr[$i]['color'] = $fund_colors[$co];
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr;
    $final_arr['color_array'] = $color_array;
    $final_arr['do_chart_slices_arr'] = $do_chart_slices_arr;
    return $final_arr;
  }

  /**
   * Incoming Funds Bar chart
   * For "By Donor" tab.
   * Returns array for Bar chart from Donut chart for Incoming Funds.
   * @param array $total_funds
   * @param array $fund_colors
   * @return array
   */
  public function ajax_bar_chart_from_donut_inc_funds($total_funds, $fund_colors) {
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Expenses', 'label' => 'Income in 1000 USD', 'pattern' => '', 'type' => 'number'),
      array('id' => 'Style', 'label' => 'Style', 'role' => 'style', 'type' => 'string')
    );
    $do_chart_rows_arr_bar = array();
    $i = 0;
    foreach ($total_funds as $fund_country) {
      $do_chart_rows_arr_bar_temp = array();
      $j = 0;
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $j++;
      }
      if ($j % 2 == 1) {
        $j++;
      }
      $j = -($j / 20);
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $total_fund = 0;
        foreach ($fund_name_arr as $val) {
          $total_fund += $val;
        }
        //income in thousand USD
        $total_fund = $total_fund / 1000;
        array_push($do_chart_rows_arr_bar_temp, array('c' => array(
            array('v' => $fund_name),
            array('v' => $total_fund),
            array('v' => $fund_colors[$fund_name]),
          )
        ));
        $j += 0.1;
      }
      array_push($do_chart_rows_arr_bar, $do_chart_rows_arr_bar_temp);
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr_bar;
    return $final_arr;
  }

  /**
   * Incoming Funds Donut chart
   * Returns array for Donut chart for Incoming Funds for "By Funds" tab.
   * @param array $total_array
   * @param array $fund_colors
   * @return array
   */
  public function ajax_donut_chart_funds_inc_funds($total_array, $fund_colors) {
    $do_chart_rows_arr = array();
    $do_chart_slices_arr = array();
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Percentage', 'label' => 'Percentage', 'pattern' => '', 'type' => 'number')
    );
    $i = 0;
    foreach ($total_array as $co => $graph_activity) {
      $total_year_budget = array_sum($graph_activity); // Total Year Array Budget
      array_push($do_chart_rows_arr, array('c' => array(
          array('v' => $co),
          array('v' => $total_year_budget)
        )
      ));
      $do_chart_slices_arr[$i]['color'] = $fund_colors[$co];
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr;
    $final_arr['do_chart_slices_arr'] = $do_chart_slices_arr;
    return $final_arr;
  }

  /**
   * Returns array for Bar chart from Donut chart for Incoming Funds for "By Funds" tab.
   * @param array $total_funds
   * @param array $fund_colors
   * @return array
   */
  public function ajax_bar_chart_from_donut_funds_inc_funds($total_funds, $fund_colors) {
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Expenses', 'label' => 'Income in 1000 USD', 'pattern' => '', 'type' => 'number'),
      array('id' => 'Style', 'label' => 'Style', 'role' => 'style', 'type' => 'string')
    );
    $do_chart_rows_arr_bar = array();
    $i = 0;
    foreach ($total_funds as $fund_country) {
      $do_chart_rows_arr_bar_temp = array();
      $j = 0;
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $j++;
      }
      if ($j % 2 == 1) {
        $j++;
      }
      $j = -($j / 20);
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $total_fund = 0;
        foreach ($fund_name_arr as $val) {
          $total_fund += $val;
        }
        //income in thousand USD
        $total_fund = $total_fund / 1000;
        array_push($do_chart_rows_arr_bar_temp, array('c' => array(
            array('v' => $fund_name),
            array('v' => $total_fund),
            array('v' => $fund_colors[$fund_name]),
          )
        ));
        $j += 0.1;
      }
      array_push($do_chart_rows_arr_bar, $do_chart_rows_arr_bar_temp);
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr_bar;
    return $final_arr;
  }

  /**
   * Disbursement
   * Returns array for Donut chart for Disbursement by "By Donor" tab.
   * @param array $total_array
   * @param array $fund_colors
   * @return array
   */
  public function ajax_donut_chart_disbursement($total_array, $fund_colors) {
    $do_chart_rows_arr = array();
    $do_chart_slices_arr = array();
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Percentage', 'label' => 'Percentage', 'pattern' => '', 'type' => 'number')
    );
    $i = 0;
    $j = 0;
    foreach ($total_array as $co => $graph_activity) {
      $total_year_budget = array_sum($graph_activity); // Total Year Array Budget
      array_push($do_chart_rows_arr, array('c' => array(
          array('v' => $co),
          array('v' => $total_year_budget)
        )
      ));
      $do_chart_slices_arr[$i]['color'] = $fund_colors[$co];
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr;
    $final_arr['do_chart_slices_arr'] = $do_chart_slices_arr;
    return $final_arr;
  }

  /**
   * This function returns array for Bar chart based on Donut chart in Disbursurement section
   * For "By Organisaion" tab
   * @param array $total_funds
   * @param array $fund_colors
   * @return array
   */
  public function ajax_bar_chart_from_donut_disbursement($total_funds, $fund_colors) {
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Expenses', 'label' => 'Disbursement in 1000 USD', 'pattern' => '', 'type' => 'number'),
      array('id' => 'Style', 'label' => 'Style', 'role' => 'style', 'type' => 'string')
    );
    $do_chart_rows_arr_bar = array();
    $i = 0;
    foreach ($total_funds as $fund_country) {
      $do_chart_rows_arr_bar_temp = array();
      $j = 0;
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $j++;
      }
      if ($j % 2 == 1) {
        $j++;
      }
      $j = -($j / 20);
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $total_fund = 0;
        foreach ($fund_name_arr as $val) {
          $total_fund += $val;
        }
        //Disbursement in thousand USD
        $total_fund = $total_fund / 1000;
        array_push($do_chart_rows_arr_bar_temp, array('c' => array(
            array('v' => $fund_name),
            array('v' => $total_fund),
            array('v' => $fund_colors[$fund_name]),
          )
        ));
        $j += 0.1;
      }
      array_push($do_chart_rows_arr_bar, $do_chart_rows_arr_bar_temp);
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr_bar;
    return $final_arr;
  }

  /**
   * This function returns array for donut chart in Disbursement section
   * For "By Funds" tab.
   * @param array $total_array
   * @param array $fund_colors
   * @return array
   */
  public function ajax_donut_chart_funds_disbursement($total_array, $fund_colors) {
    $do_chart_rows_arr = array();
    $do_chart_slices_arr = array();
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Percentage', 'label' => 'Percentage', 'pattern' => '', 'type' => 'number')
    );
    $i = 0;
    $j = 0;
    foreach ($total_array as $co => $graph_activity) {
      $total_year_budget = array_sum($graph_activity); // Total Year Array Budget
      array_push($do_chart_rows_arr, array('c' => array(
          array('v' => $co),
          array('v' => $total_year_budget)
        )
      ));
      $do_chart_slices_arr[$i]['color'] = $fund_colors[$co];
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr;
    $final_arr['do_chart_slices_arr'] = $do_chart_slices_arr;
    return $final_arr;
  }

  /**
   * This function returns array for Bar chart based on Donut chart in Disbursurement section
   * For "By Funds" tab
   * @param array $total_funds
   * @param array $fund_colors
   * @return array
   */
  public function ajax_bar_chart_from_donut_funds_disbursement($total_funds, $fund_colors) {
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Expenses', 'label' => 'Disbursement in 1000 USD', 'pattern' => '', 'type' => 'number'),
      array('id' => 'Style', 'label' => 'Style', 'role' => 'style', 'type' => 'string')
    );
    $do_chart_rows_arr_bar = array();
    $i = 0;
    foreach ($total_funds as $fund_country) {
      $do_chart_rows_arr_bar_temp = array();
      $j = 0;
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $j++;
      }
      if ($j % 2 == 1) {
        $j++;
      }
      $j = -($j / 20);
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $total_fund = 0;
        foreach ($fund_name_arr as $val) {
          $total_fund += $val;
        }
        //Disbursement in thousand USD
        $total_fund = $total_fund / 1000;
        array_push($do_chart_rows_arr_bar_temp, array('c' => array(
            array('v' => $fund_name),
            array('v' => $total_fund),
            array('v' => $fund_colors[$fund_name]),
          )
        ));
        $j += 0.1;
      }
      array_push($do_chart_rows_arr_bar, $do_chart_rows_arr_bar_temp);
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr_bar;
    return $final_arr;
  }

  /**
   * Expenditure
   * This function returns array for donut chart in Expenditure section
   * For By organisation
   * @param array $total_array
   * @param array $fund_colors
   * @return array
   */
  public function ajax_donut_chart_expenditure($total_array, $fund_colors) {
    $do_chart_rows_arr = array();
    $do_chart_slices_arr = array();
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Percentage', 'label' => 'Percentage', 'pattern' => '', 'type' => 'number')
    );
    $i = 0;
    $j = 0;
    foreach ($total_array as $co => $graph_activity) {
      $total_year_budget = array_sum($graph_activity); // Total Year Array Budget
      array_push($do_chart_rows_arr, array('c' => array(
          array('v' => $co),
          array('v' => $total_year_budget)
        )
      ));
      $do_chart_slices_arr[$i]['color'] = $fund_colors[$co];
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr;
    $final_arr['do_chart_slices_arr'] = $do_chart_slices_arr;
    return $final_arr;
  }

  /**
   * This function returns array for Bar chart by Donut chart for Expenditure section
   * By Organisation
   * @param array $total_funds
   * @param array $fund_colors
   * @return array
   */
  public function ajax_bar_chart_from_donut_expenditure($total_funds, $fund_colors) {
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Expenses', 'label' => 'Expenditure in 1000 USD', 'pattern' => '', 'type' => 'number'),
      array('id' => 'Style', 'label' => 'Style', 'role' => 'style', 'type' => 'string')
    );
    $do_chart_rows_arr_bar = array();
    $i = 0;
    foreach ($total_funds as $fund_country) {
      $do_chart_rows_arr_bar_temp = array();
      $j = 0;
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $j++;
      }
      if ($j % 2 == 1) {
        $j++;
      }
      $j = -($j / 20);
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $total_fund = 0;
        foreach ($fund_name_arr as $val) {
          $total_fund += $val;
        }
        //Expenditure in thousand USD
        $total_fund = $total_fund / 1000;
        array_push($do_chart_rows_arr_bar_temp, array('c' => array(
            array('v' => $fund_name),
            array('v' => $total_fund),
            array('v' => $fund_colors[$fund_name]),
          )
        ));
        $j += 0.1;
      }
      array_push($do_chart_rows_arr_bar, $do_chart_rows_arr_bar_temp);
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr_bar;
    return $final_arr;
  }

  /**
   * Returns array to prepare Donut chart for Expenditure section
   * By Funds
   * @param array $total_array
   * @param array $fund_colors
   * @return array
   */
  public function ajax_donut_chart_funds_expenditure($total_array, $fund_colors) {
    $do_chart_rows_arr = array();
    $do_chart_slices_arr = array();
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Percentage', 'label' => 'Percentage', 'pattern' => '', 'type' => 'number')
    );
    $i = 0;
    $j = 0;
    foreach ($total_array as $co => $graph_activity) {
      $total_year_budget = array_sum($graph_activity); // Total Year Array Budget
      array_push($do_chart_rows_arr, array('c' => array(
          array('v' => $co),
          array('v' => $total_year_budget)
        )
      ));
      $do_chart_slices_arr[$i]['color'] = $fund_colors[$co];
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr;
    $final_arr['do_chart_slices_arr'] = $do_chart_slices_arr;
    return $final_arr;
  }

  /**
   * Returns array to prepare Bar chart fron Donut chart
   * By Funds
   * @param array $total_funds
   * @param array $fund_colors
   * @return array
   */
  public function ajax_bar_chart_from_donut_funds_expenditure($total_funds, $fund_colors) {
    $do_chart_cols_arr = array(
      array('id' => 'Sector', 'label' => 'Sector', 'pattern' => '', 'type' => 'string'),
      array('id' => 'Expenses', 'label' => 'Expenditure in 1000 USD', 'pattern' => '', 'type' => 'number'),
      array('id' => 'Style', 'label' => 'Style', 'role' => 'style', 'type' => 'string')
    );
    $do_chart_rows_arr_bar = array();
    $i = 0;
    foreach ($total_funds as $fund_country) {
      $do_chart_rows_arr_bar_temp = array();
      $j = 0;
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $j++;
      }
      if ($j % 2 == 1) {
        $j++;
      }
      $j = -($j / 20);
      foreach ($fund_country as $fund_name => $fund_name_arr) {
        $total_fund = 0;
        foreach ($fund_name_arr as $val) {
          $total_fund += $val;
        }
        //Expenditure in thousand USD
        $total_fund = $total_fund / 1000;
        array_push($do_chart_rows_arr_bar_temp, array('c' => array(
            array('v' => $fund_name),
            array('v' => $total_fund),
            array('v' => $fund_colors[$fund_name]),
          )
        ));
        $j += 0.1;
      }
      array_push($do_chart_rows_arr_bar, $do_chart_rows_arr_bar_temp);
      $i++;
    }
    $final_arr = array();
    $final_arr['do_chart_cols_arr'] = $do_chart_cols_arr;
    $final_arr['do_chart_rows_arr'] = $do_chart_rows_arr_bar;
    return $final_arr;
  }

}
