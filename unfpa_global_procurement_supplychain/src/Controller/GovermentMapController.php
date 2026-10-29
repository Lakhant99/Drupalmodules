<?php

namespace Drupal\unfpa_global_procurement_supplychain\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * For Suppliers Page for Procurement & Supply Chain.
 */
class GovermentMapController extends ControllerBase {
  /**
   * Returns For Suppliers page content.
   */
  public function content() {
    $data = array();
    $postValue = (!empty($_POST['data'])) ? \Drupal\Component\Utility\Html::escape($_POST['data']) : "";
    $cache_id = "unfpa_global_partners_government_world_map_{$_POST['lang']}_" . $postValue;
    // If data is cached, get from cache.
    if ($cache = \Drupal::cache()->get($cache_id)) {
      $location_arr = $cache->data['location'];
      $country_arr = $cache->data['country'];
    }    
    else {
      // Get Static Locations.
      $static_locations_latlong = $this->unfpa_global_supplychain_get_static_locations();
      $static_locations = array_keys($static_locations_latlong);
      // Get government spending data for map.
      $gs_data = $this->unfpa_global_supplychain_get_govt_spendinginfo($postValue);
      $max_spent = max(array_column($gs_data, 'spending_amount'));
      $cnt = 0;
      $location_arr = [];
      $country_arr = [];
      foreach ($gs_data as $value) {
        $color_code = $this->unfpa_global_supplychain_get_color_code_by_amount($value['spending_amount'], $max_spent, $postValue);

        if (!in_array($value['country'], $static_locations)) {
            if(empty($value['spending_amount'])) {
              $value['spending_amount'] = '0.00';
            }
            $country_arr[$value['code']]['name'] = isset($value['country']) ? $value['country'] : $value['code'];
            $country_arr[$value['code']]['description'] = 'Spent Amount $ ' . number_format($value['spending_amount']);
            $country_arr[$value['code']]['color'] = ($color_code != '') ? $color_code : 'default';
            $country_arr[$value['code']]['hover_color'] = 'default';
            $country_arr[$value['code']]['inactive'] = 'no';
            $country_arr[$value['code']]['hide'] = 'no';
        }
        else {
          $location_arr[$cnt]['name'] = isset($value['country']) ? $value['country'] : $value['code'];
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

      $data['location'] = $location_arr;
      $data['country'] = $country_arr;
      // Set cache data for page_world_map.
      \Drupal::cache()->set($cache_id, $data);
    }

    $variables = array(
      'location' => $location_arr,
      'country' => $country_arr,
      'map_title' => $this->t('Partner Procurement in Numbers <span> Governments, NGOs, Global Fund & UN Agencies </span> 2011-2020'),
    );

    echo json_encode($variables); exit;

    return new JsonResponse([
      'data' => $variables,
      'method' => 'POST',
    ]);
    
  }

  /**
   * Function to get static locations.
   */
  public function unfpa_global_supplychain_get_static_locations() {
    return [
      "Comoros" => [
        "lat" => "-12.1667",
        "lng" => "44.25",
        "size" => "16",
      ],
      "Maldives" => [
        "lat" => "-0.6",
        "lng" => "73.1",
        "size" => "16",
      ],
      "Sao Tome & Principe" => [
        "lat" => "0.333",
        "lng" => "6.73",
        "size" => "16",
      ],
      "Seychelles" => [
        "lat" => "-4.5833",
        "lng" => "55.6667",
        "size" => "16",
      ],
      "Cape Verde" => [
        "lat" => "15.1111",
        "lng" => "-23.6167",
        "size" => "16",
      ],
      "Pacific-SRO" => [
        "lat" => "-18.0000",
        "lng" => "179.0000",
        "size" => "16",
      ],
      "Caribbean SRO" => [
        "lat" => "18.1824",
        "lng" => "-77.3218",
        "size" => "16",
      ],
    ];
  }

  /**
   * Returns data on government spending.
   */
  public function unfpa_global_supplychain_get_govt_spendinginfo($data) {
    $database = \Drupal::database();
    if($data == 'data1') {      
      $query = $database->select('government_spending', 'gs')
      ->fields('gs', ['country', 'code']);
    }
    else {
      $query = $database->select('partners_spending', 'gs')
      ->fields('gs', ['country', 'code']);
    }

    $query->addExpression("REPLACE(spending_amount, ',', '')", 'spending_amount');
    $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

    return $result ? $result : NULL;
  }

  /**
   * Returns color code by Amount.
   */
  public function unfpa_global_supplychain_get_color_code_by_amount($amount, $max_amount, $postData) {
    $final_cc = '';
    if ($amount && $max_amount) {
      // Divide max amount by 5 to group colocodes.
      $divided_amount = $max_amount / 10;
      $group = ceil($amount / $divided_amount);

      $color_arr = $this->unfpa_global_supplychain_get_government_spending_color_codes($postData);

      if ($group !== NULL && $color_arr) {
        $color_values = array_values(array_reverse($color_arr));

        $final_cc = $color_values[($group - 1)];
      }
    }
    return $final_cc;
  }

  /**
   * Returns color code.
   */
  public function unfpa_global_supplychain_get_government_spending_color_codes($postData) {  
    if($postData == 'data2') {
      $color =  [
        90 => '#102e3c',
        80 => '#153e51',
        70 => '#205d79',
        60 => '#256c8d',
        50 => '#308bb5',
        40 => '#4aa5cf',
        30 => '#72b9da',
        20 => '#86c3df',
        10 => '#aed7ea',
        0 => '#d7ebf4',
      ];
    }
    else {
      $color =  [
        90 => '#9a4f01',
        80 => '#a3560b',
        70 => '#ac5e15',
        60 => '#b5651d',
        50 => '#be6c25',
        40 => '#c7742d',
        30 => '#d07c34',
        20 => '#c67d43',
        10 => '#d59667',
        0 => '#e2b08c',
      ];
    }  
    
    return $color;
  }

}
