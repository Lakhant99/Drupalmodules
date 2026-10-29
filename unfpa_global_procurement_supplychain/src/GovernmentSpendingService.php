<?php

namespace Drupal\unfpa_global_procurement_supplychain;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\unfpa_global_executive_board\CPDService;

/**
 *  Provides data for Government Spending.
 */

class GovernmentSpendingService {
  
 use StringTranslationTrait;

  protected $moduleHandler;
  protected $connection;
  protected $entityFieldManager;
  protected $aliasManager;
  protected $contributionsData;
  protected $state;

  /**
   * The CountryProgrammeDocuments Service.
   *
   * @var \Drupal\unfpa_global_executive_board\CPDService
   */
  protected $cpdService;

  /**
   * Initializes variables.
   */
  public function __construct(ModuleHandlerInterface $module_handler, Connection $connection, EntityFieldManager $entity_field_manager, AliasManagerInterface $alias_manager, CPDService $cpd_service) {
    $this->moduleHandler = $module_handler;
    $this->connection = $connection;
    $this->entityFieldManager = $entity_field_manager;
    $this->aliasManager = $alias_manager;
    $this->cpdService = $cpd_service;
  }


  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('database'),
      $container->get('entity_field.manager'),
      $container->get('path.alias_manager'),
      $container->get('unfpa_global_executive_board.cpd')
    );
  }
  
  /**
   * Returns data on Government Spending.
   *
   * @return array
   *   Array contains all the data to be shown on donor section.
   */
  public function getGovernmentSpendingData() {
    $query = $this->connection
      ->select('government_spending', 'gs')
      ->fields('gs', ['country', 'code']);

    $query->addExpression("REPLACE(spending_amount, ',', '')", 'spending_amount');
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    return $result ? $result : NULL;
  }

  public function getGovernmentSpendingColorCodes() {
    return [
      90 => '#9a4f01',
      80 => '#a3560b',
      70 => '#ac5e15',
      60 => '#b5651d',
      50 => '#be6c25',
      40 => '#c7742d',
      30 => '#d07c34',
      20 => '#c67d43',
      10 => '#d59667',
      0 => '#e2b08c'
    ];
  }

  /**
   * Returns color code by Amount 
   */
  public function getColorCodeByAmount($amount, $max_amount) {
    $final_cc = '';
    if ($amount && $max_amount) {
      // Divide max amount by 5 to group colocodes
      $divided_amount = $max_amount/10;
      $group = ceil($amount/$divided_amount);

      $color_arr = $this->getGovernmentSpendingColorCodes();

      if ($group !== NULL && $color_arr) {
        $color_values = array_values(array_reverse($color_arr));

        $final_cc = $color_values[($group - 1)];
      }
    }
    return $final_cc;
  }

  /**
   * Returns Procurement Map.
   */
  public function getGovernmentSpendingMapData() {
    // Get Static Locations.
    $static_locations_latlong = $this->cpdService->getStaticLocations();
    $static_locations = array_keys($static_locations_latlong);

    // Get government spending data for map
    $gs_data = $this->getGovernmentSpendingData();
    $max_spent = max(array_column($gs_data, 'spending_amount'));
    $cnt = 0;
    $location_arr = [];
    $country_arr = [];

    foreach ($gs_data as $value) {
      $color_code = $this->getColorCodeByAmount($value['spending_amount'], $max_spent);

      if (!in_array($value['country'], $static_locations)) {
        $country_arr[$value['code']]['name'] = (string) $this->t(isset($value['country']) ? $value['country'] : $value['code']);
        $country_arr[$value['code']]['description'] = 'Spent Amount $ ' . number_format($value['spending_amount']);
        $country_arr[$value['code']]['color'] = ($color_code != '') ? $color_code : 'default';
        $country_arr[$value['code']]['hover_color'] = 'default';
        $country_arr[$value['code']]['inactive'] = 'no';
        $country_arr[$value['code']]['hide'] = 'no';        
      }
      else {
        $location_arr[$cnt]['name'] = (string) $this->t(isset($value['country']) ? $value['country'] : $value['code']);
        $location_arr[$cnt]['description'] = 'Spent Amount $ ' . number_format($value['spending_amount']);
        $location_arr[$cnt]['color'] = ($color_code != '') ? $color_code : 'default';
        $location_arr[$cnt]['hover_color'] = 'default';
        $location_arr[$cnt]['display'] = 'all';
        $location_arr[$cnt]['type'] = 'circle';
        $location_arr[$cnt]['size'] = $static_locations_latlong[$value['country']]['size'];
        $location_arr[$cnt]['lat'] = $static_locations_latlong[$value['country']]['lat'];
        $location_arr[$cnt]['lng'] = $static_locations_latlong[$value['country']]['lng'];
        $cnt++;
      }
    }

    return [
      'location' => $location_arr,
      'country' => $country_arr,
    ];
  }
  
}