<?php

namespace Drupal\unfpa_global_procurement_supplychain\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\unfpa_global_procurement_supplychain\GovernmentSpendingService;
use Drupal\unfpa_global_executive_board\CPDService;


/**
 * For Governments & Partners Page for Procurement & Supply Chain.
 */
class ForGovernmentsAndPartnersController extends ControllerBase {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The Language Manager.
   *
   * @var Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;
  
  
  /**
   * The GovernmentSpending Service.
   *
   * @var \Drupal\unfpa_global_procurement_supplychain\GovernmentSpendingService
   */
  protected $governmentSpending;

  /**
   * The CountryProgrammeDocuments Service.
   *
   * @var \Drupal\unfpa_global_executive_board\CPDService
   */
  protected $cpdService;

  /**
   * Total Spending Amount.
   */
  protected $total_spending_amount;

  /**
   * {@inheritdoc}
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(ConfigFactory $config_factory, EntityTypeManagerInterface $entity_type_manager, LanguageManager $language_manager, GovernmentSpendingService $governmentSpending, CPDService $cpd_service) {
    $this->config = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->governmentSpending = $governmentSpending;
    $this->cpdService = $cpd_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('language_manager'),
      $container->get('unfpa_global_procurement_supplychain.govt_spending'),
      $container->get('unfpa_global_executive_board.cpd')
    );
  }

  /**
   * Returns For Governments & Partners page content.
   */
  public function content() {
    // Get Static Locations.
    $static_locations_latlong = $this->cpdService->getStaticLocations();
    $static_locations = array_keys($static_locations_latlong);

    // Get government spending data for map
    $gs_data = $this->getGovtSpendingInfo();
    $max_spent = max(array_column($gs_data, 'spending_amount'));
    $cnt = 0;
    $location_arr = [];
    $country_arr = [];

    foreach ($gs_data as $value) {
      $color_code = $this->governmentSpending->getColorCodeByAmount($value['spending_amount'], $max_spent);

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
      '#theme' => 'for_governments_and_partners',
      '#attached' => [
        'library' => [
          'unfpa_global_procurement_supplychain/procurement_supplychain',
          'unfpa_global_procurement_supplychain/government_partners',
        ],
        'drupalSettings' => [
          'location' => $location_arr,
          'country' => $country_arr,
        ],
      ],
    ];
  }
  
  /**
   * Returns data on government spending.
   */
  public function getGovtSpendingInfo() {
    $map_data = $this->governmentSpending->getGovernmentSpendingData();
    return $map_data;
  }
}
