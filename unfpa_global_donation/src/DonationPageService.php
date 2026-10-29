<?php

namespace Drupal\unfpa_global_donation;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Donation Page Service.
 */
class DonationPageService {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, Connection $connection, RequestStack $request) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('language.manager'),
      $container->get('database'),
      $container->get('request_stack')
    );
  }

  /**
   * Callback function to generate options for price.
   *
   * @return array
   *   Returns Price options.
   */
  public function getPriceOptions() {
    $options = [];

    $country_name_result = $this->connection->query("DESCRIBE `currencies_conversion`")->fetchAll();
    unset($country_name_result[0]);
    unset($country_name_result[1]);
    if (is_array($country_name_result) && count($country_name_result) > 0) {
      foreach ($country_name_result as $value) {
        $options[$value->Field] = $value->Field;
      }
    }

    return $options;
  }

  /**
   * Return Currency Symbol by country name.
   *
   * @return array
   *   Returns array containing currency name and symbol.
   */
  public function getCurrencySymbolByCountry($country_code) {
    $currency_code = [];

    if (!empty($country_code)) {
      $currency_code = $this->connection->select('currencies', 'c')
        ->fields('c', ['currency', 'symbol'])
        ->condition('c.code', $country_code)
        ->execute()
        ->fetchAll();
    }

    return $currency_code;
  }

  /**
   * Return Country & Currency Details.
   *
   * @return array
   *   Returns array containing Currency Name, Country Name, Country Code and Symbol.
   */
  public function getCurrencyDetails() {
    $query = $this->connection->select('currencies', 'c')->fields('c', []);
    $currency_code = $query->execute()->fetchAllAssoc('code');

    return $currency_code;
  }

  /**
   * Return Amount and Currency Code.
   *
   * @price integer
   *   Amount to be converted.
   * @currency_code string
   *   Currency code of the country.
   *
   * @return array
   *   Returns array containing amount and currency code.
   */
  public function getConversionRate($price, $currency_code) {
    $result = [];

    if ($price) {
      $currency_conversion = $this->connection->query("SELECT currencies_conversion.$price as conversion_rate FROM currencies_conversion WHERE currencies_conversion.currency = '$currency_code'")->fetchAll();
      if (is_array($currency_conversion) && count($currency_conversion) > 0) {
        $result['amount'] = $currency_conversion[0]->conversion_rate;
        $result['currency_code'] = '';
      }
      else {
        $result['amount'] = '0';
        $result['currency_code'] = '';
      }

    }

    return $result;
  }

  /**
   * Get UN Country List.
   *
   * @return array
   *   Returns array of country list.
   */
  public function getUNCountryList() {
    $exclude_reg_code = $this->getExcludedRegions();

    $result = $this->connection->select('country_code', 'cc')
      ->fields('cc')
      ->condition('cc.cc', $exclude_reg_code, 'NOT IN')
      ->orderBy('c_name')
      ->execute()
      ->fetchAll();

    $final_country_array = [];
    foreach ($result as $country_data) {
      // Remove '.' from country name if exist.
      if (strrpos($country_data->c_name, '.')) {
        $country_name = substr($country_data->c_name, 0, strrpos($country_data->c_name, '.'));
      }
      else {
        $country_name = $country_data->c_name;
      }
      $final_country_array[$country_data->cc] = $country_name;
    }
    asort($final_country_array);

    return $final_country_array;
  }

  /**
   * Get Excluded regions List.
   *
   * @return array
   *   Returns array of excluded regions.
   */
  public function getExcludedRegions() {
    return [
      'ASIA',
      'EECA',
      'WCA',
      'ARAB',
      'LAC',
      'ESA',
      'WORLD',
      'MDREGION',
      'LDREGION',
      'LEDREGION',
    ];
  }

  /**
   * Get User IP Address.
   */
  public function get_user_ip() {
    $client = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote = $this->request->getCurrentRequest()->getClientIp();
    if (filter_var($client, FILTER_VALIDATE_IP)) {
      $ip = $client;
    }
    elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
      $ip = $forward;
    }
    else {
      $ip = $remote;
    }
    return $ip;
  }

  /**
   * Get IP Address details.
   */
  public function ip_details($IPaddress) {
    $json = file_get_contents("https://ipinfo.io/{$IPaddress}");
    $details = json_decode($json);
    return $details;
  }

}
