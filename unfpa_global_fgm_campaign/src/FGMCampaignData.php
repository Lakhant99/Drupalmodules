<?php

namespace Drupal\unfpa_global_fgm_campaign;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Language\LanguageInterface;

/**
 * Get data for FGM configuration.
 */
class FGMCampaignData {

  use StringTranslationTrait;

  /**
   * The module handler interface service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The Json service.
   *
   * @var \Drupal\Component\Serialization\Json
   */
  protected $json;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $currentLanguage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $language_manager;

  /**
   * Constructs for categories configuration.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The Database services.
   * @param \Drupal\Component\Serialization\Json $serialization_json
   *   The Json service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, Connection $connection, Json $serialization_json, LanguageManagerInterface $language_manager) {
    global $base_url;
    $this->moduleHandler = $module_handler;
    $this->connection = $connection;
    $this->module_path = $base_url . '/' . $this->moduleHandler->getModule('unfpa_global_fgm_campaign')->getPath();
    $this->json = $serialization_json;
    $this->language_manager = $language_manager;
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'), $container->get('database'), $container->get('serialization.json'), $container->get('language_manager')
    );
  }

  /**
   * Function to get gm_annual_nav data.
   */
  public function unfpa_global_fgm_campaign_nav($share_id) {

    // Front page link.
    if ($this->currentLanguage != 'en') {
      $variables['front_page'] = $GLOBALS['base_url'] . '/' . $this->currentLanguage;
    }
    else {
      $variables['front_page'] = $GLOBALS['base_url'];
    }

    // Nav language links.
    $language_links = $this->unfpa_global_fgm_campaign_get_lang_links($share_id);

    $variables['language_links'] = $language_links['#markup'];
    $variables['language_links_reversed'] = array_reverse($language_links['#markup'], TRUE);

    // Main page url.
    $variables['main_page_url'] = Url::fromRoute('unfpa_global_fgm_campaign.unfpa_global_fgm_campaign_data');

    // Donate link url.
    $lang = $this->currentLanguage;

    // Donate and Learn urls come from variables.
    $donate_url = 'https://secure2.convio.net/unfpa/site/Donation2?df_id=1941&1941.donation=form1&mfc_pref=T';

    $variables['donate_url'] = $donate_url;

    // Add main page sharelink to nav bar.
    $variables['sharelink'] = FALSE;
    $variables['location'] = $share_id;
    $share_data = $this->unfpa_global_fgm_campaign_share_get_data(FALSE);
    $sharelink = [
      'share_url' => $this->unfpa_global_fgm_campaign_share_get_href(FALSE),
      'share_img' => $share_data['img'],
      'share_description' => $share_data['description'],
      'color' => '#fff',
    ];
    $variables['sharelink'] = $this->unfpa_global_fgm_campaign_sharelink($sharelink);

    // Menu links (for subpages).
    $urls = [
      'en' => [
        1 => '/born-complete/wounds-and-scars',
        2 => '/born-complete/harmful-norms',
        3 => '/born-complete/intact',
      ],
      'es' => [
        1 => '/nacidas-enteras/heridas-y-cicatrices',
        2 => '/nacidas-enteras/normas-nocivas',
        3 => '/nacidas-enteras/intactas',
      ],
      'fr' => [
        1 => '/nees-entieres-et-completes/plaies-et-cicatrices',
        2 => '/nees-entieres-et-completes/normes-prejudiciables',
        3 => '/nees-entieres-et-completes/intactes',
      ],
      'ar' => [
        1 => '/born-complete/wounds-and-scars-arabic',
        2 => '/born-complete/harmful-norms-arabic',
        3 => '/born-complete/intact-arabic',
      ],
    ];

    $lang_prefix = '';
    if ($this->currentLanguage != 'en') {
      $lang_prefix = $GLOBALS['base_url'] . '/' . $this->currentLanguage;
    }
    else {
      $lang_prefix = $GLOBALS['base_url'];
    }
    $menu_links[1] = '<a href="' . $lang_prefix . '/born-complete#entry" xml:lang="' . $this->currentLanguage . '" hreflang="' . $this->currentLanguage . '">' . $this->t('Born complete') . '</a>';
    $menu_links[2] = '<a href="' . $lang_prefix . $urls[$lang][1] . '" xml:lang="' . $this->currentLanguage . '" hreflang="' . $this->currentLanguage . '">' . $this->t('Wounds and scars') . '</a>';
    $menu_links[3] = '<a href="' . $lang_prefix . $urls[$lang][2] . '" xml:lang="' . $this->currentLanguage . '" hreflang="' . $this->currentLanguage . '">' . $this->t('Harmful norms') . '</a>';
    $menu_links[4] = '<a href="' . $lang_prefix . $urls[$lang][3] . '" xml:lang="' . $this->currentLanguage . '" hreflang="' . $this->currentLanguage . '">' . $this->t('Intact') . '</a>';
    $menu_links[5] = '<a href="' . $lang_prefix . '/born-complete#map" xml:lang="' . $this->currentLanguage . '" hreflang="' . $this->currentLanguage . '">' . $this->t('Pledge now') . '</a>';
    $variables['menu_links'] = $menu_links;
    $variables['module_path'] = $this->module_path;
    $variables['base_url_wln'] = $lang_prefix;
    return [
      '#theme' => 'unfpa_global_fgm_campaign_nav',
      '#data' => $variables,
    ];
  }

  /**
   * Function to get gm_annual_nav data.
   */
  public function unfpa_global_fgm_campaign_entry() {
    $variables['module_path'] = $this->module_path;
    return [
      '#theme' => 'unfpa_global_fgm_campaign_entry',
      '#data' => $variables,
    ];
  }

  /**
   * Function to get gm_annual_nav data.
   */
  public function unfpa_global_fgm_campaign_panels($panels) {
    $variables['module_path'] = $this->module_path;
    $variables['panels'] = $panels;
    return [
      '#theme' => 'unfpa_global_fgm_campaign_panels',
      '#data' => $variables,
    ];
  }

  /**
   * Function to get gm_annual_nav data.
   */
  public function unfpa_global_fgm_campaign_map($take_action_form, $share_id = FALSE) {
    $variables['module_path'] = $this->module_path;
    $include_share = TRUE;

    // Add pledge form to template.
    $variables['pledge_form'] = $take_action_form;

    // Attach js with pledges count, used by the map.
    $pledge_count = $this->unfpa_global_fgm_campaign_pledge_get_count();

    // In the pledge form, the sharelink is added in form constructor
    // function (unfpa_global_fgm_campaign_pledge_form). Here we are adding same
    // sharelink for use within the thank you message.
    $variables['sharelink'] = '';

    if ($include_share) {
      $share_data = $this->unfpa_global_fgm_campaign_share_get_data($share_id);
      $sharelink = [
        'share_url' => $this->unfpa_global_fgm_campaign_share_get_href($share_id),
        'share_img' => $share_data['img'],
        'share_description' => $share_data['description'],
        'color' => '#9956cc',
      ];
      $variables['sharelink'] = $this->unfpa_global_fgm_campaign_sharelink($sharelink);
    }
    return [
      '#theme' => 'unfpa_global_fgm_campaign_map',
      '#data' => $variables,
      '#attached' => [
        'drupalSettings' => [
          'unfpa_global_fgm_campaign' => ['pledge_count' => $pledge_count],
        ],
      ],
    ];
  }

  /**
   * Function to get gm_annual_nav data.
   */
  public function unfpa_global_fgm_campaign_actions($show_pledge = FALSE) {

    // Get language key.
    $lang = $this->currentLanguage;

    // Learn URL is language dependant.
    $learn_urls = [
      'en' => 'female-genital-mutilation',
      'es' => 'mutilación-genital-femenina',
      'fr' => 'mutilations-génitales-féminines',
      'ar' => '#',
    ];

    // Donate and Learn urls come from variables.
    $donate_url = 'https://secure2.convio.net/unfpa/site/Donation2?df_id=1941&1941.donation=form1&mfc_pref=T';
    if($lang == 'en') {
      $learn_url = $learn_urls[$lang];
    }
    else {
      $learn_url = $lang . '/' . $learn_urls[$lang];
    }

    // Provide urls for all 3 actions.
    $variables['url_learn'] = Url::fromUri('base:/' . $learn_url, ['absolute' => TRUE])->toString();
    $variables['url_pledge'] = Url::fromRoute('unfpa_global_fgm_campaign.unfpa_global_fgm_campaign_data')->toString() . '#map';
    $variables['url_donate'] = $donate_url;

    // CSS class.
    $variables['class'] = 'fgm-actions-2';
    $variables['show_pledge'] = !empty($show_pledge) ? TRUE : FALSE;
    if ($variables['show_pledge']) {
      $variables['class'] = 'fgm-actions-3';
    }

    return [
      '#theme' => 'unfpa_global_fgm_campaign_actions',
      '#data' => $variables,
    ];
  }

  /**
   * Function to get gm_annual_nav data.
   */
  public function unfpa_global_fgm_campaign_sharemodal($panels = []) {
    $variables['module_path'] = $this->module_path;
    $variables['panels'] = $panels;

    return [
      '#theme' => 'unfpa_global_fgm_campaign_sharemodal',
      '#data' => $variables,
    ];
  }

  /**
   * Get share href full url for a share id.
   *
   * If no id is provided then the share url will be for the whole current page.
   * If id is provided then it will be added to the end of url.
   *
   * @param $share_id
   *   The number that is a unique id of piece of page that is being shared.
   *
   * @return string
   *   Returns full url of page, including share id one was provided.
   */
  public function unfpa_global_fgm_campaign_share_get_href($share_id = FALSE) {
    $lang_prefix = '';
    if (!empty($this->currentLanguage) && $this->currentLanguage != 'en') {
      $lang_prefix = $this->currentLanguage . '/';
    }
    $url = $GLOBALS['base_url'] . '/' . $lang_prefix . 'born-complete';
    $argument = '';
    if ($share_id) {
      $argument .= '/' . $share_id;
    }

    return $url . $argument;
  }

  /**
   * Wrapper function for easier getting of share link.
   *
   * Allows to get share link without writing repeated calls to theme function.
   */
  public function unfpa_global_fgm_campaign_sharelink($share) {
    $url = urlencode($share['share_url']);
    $share['share_url_fb'] = 'https://www.facebook.com/sharer.php?u=' . $url;
    $share['share_url_tw'] = 'https://twitter.com/share?url=' . $url;

    return [
      '#theme' => 'unfpa_global_fgm_campaign_sharelink',
      '#data' => $share,
    ];
  }

  /**
   * Returns share datqa for specified share id.
   *
   * @param mixed $share_id
   *   Share id of shared page part or FALSE if sharing whole page.
   *
   * @return array
   *   Array with data about shared item: url, image, title and description.
   */
  public function unfpa_global_fgm_campaign_share_get_data($share_id = FALSE) {

    // Urls depend on language prefix.
    $lang_prefix = '';
    if ($this->currentLanguage != 'en') {
      $lang_prefix = $this->currentLanguage . '/';
    }

    // Absolute urls.
    $base_url = $GLOBALS['base_url'] . '/';
    $images_url = $this->module_path . '/share/' . $lang_prefix;
    // Values used if user shares the whole site.
    $data = [
      'url' => $base_url . $lang_prefix . 'born-complete',
      'img' => $images_url . 'share-default.jpg',
      'title' => $this->t('Born complete'),
      'description' => $this->t("200 million girls & women have undergone female genital mutilation, with 68 million more at risk unless we #EndFGM: unfpa.org/EndFGM"),
    ];
    // Return default data if no shared item id.
    if (empty($share_id)) {
      return $data;
    }
    // If we have an item id then try to get share data for this id.
    switch ($share_id) {
      case 'map':
        $data['url'] = '/map';
        $data['img'] = $images_url . 'share-map.jpg';
        $data['description'] = $this->t('I pledge to become a champion to #EndFGM. Unless we end it, 68 million girls will be cut by 2030. Learn more and join me: unfpa.org/EndFGM');
        break;
    }

    return $data;
  }

  /**
   * Get language switching links.
   *
   * This is similar logic to language switcher block from 'locale' module
   * except it doesn't print link to current language, and link css class
   * is customized.
   *
   * @return array
   *   Array of language switching links.
   */
  public function unfpa_global_fgm_campaign_get_lang_links($share_id = FALSE) {
    global $base_url;
    // Return array will contain our links.
    $result = [];
    $links = $this->language_manager->getLanguageSwitchLinks(LanguageInterface::TYPE_URL, Url::fromRoute('unfpa_global_fgm_campaign.unfpa_global_fgm_campaign_data'));

    // If no links then return empty array.
    if (!isset($links->links)) {
      return $result;
    }
    // Get current FGM Annual 2018 page language.
    $current_lang = $this->currentLanguage;    
    unset($links->links['zh-hans']);
    unset($links->links['pcm']);
    unset($links->links['ha']);
    unset($links->links['ru']);
    foreach ($links->links as $lang => $link) {
      // Skip link to current language.
      if ($lang == $current_lang) {
        continue;
      }
      if($share_id == 'page') {
        $node = \Drupal::routeMatch()->getParameter('node');
        if ($node instanceof \Drupal\node\NodeInterface) {
          // You can get nid and anything else you need from the node object.
         $node = $node->getTranslation($link['language']->getId());
         $node_url = $node->toUrl()->toString();
        }
        $node_url_red = $base_url . $node_url;
        if ($link['language']->getId() == 'en') {
          $result[$lang] .= '<a href="' . $node_url_red . '" class="fgm-nav-lang" xml:lang="' . $link['language']->getId() . '" hreflang="' . $link['language']->getId() . '">' . $link['title'] . '</a>';
        }
        else {
          $result[$lang] .= '<a href="' . $node_url_red . '" class="fgm-nav-lang" xml:lang="' . $link['language']->getId() . '" hreflang="' . $link['language']->getId() . '">' . $link['title'] . '</a>';
        }

      }
      else {
        if ($link['language']->getId() == 'en') {
          $result[$lang] .= '<a href="/born-complete" class="fgm-nav-lang" xml:lang="' . $link['language']->getId() . '" hreflang="' . $link['language']->getId() . '">' . $link['title'] . '</a>';
        }
        else {
          $result[$lang] .= '<a href="/' . $link['language']->getId() . '/born-complete" class="fgm-nav-lang" xml:lang="' . $link['language']->getId() . '" hreflang="' . $link['language']->getId() . '">' . $link['title'] . '</a>';
        }
      }
    }    

    return [
      '#type' => 'markup',
      '#markup' => $result,
    ];
  }

  /**
   * Gets array of pledges count per each country.
   *
   * @return array
   *   Array with keys being country codes, and values being pledges count.
   */
  public function unfpa_global_fgm_campaign_pledge_get_count() {
    $countries = $this->unfpa_global_fgm_campaign_pledge_get_countries();
    $pledges_count = [];
    foreach ($countries as $country_key => $country_name) {
      $count = $this->connection->select('unfpa_global_fgm_campaign_pledges', 'p')
        ->fields('p', [])
        ->condition('country', $country_key)
        ->countQuery()
        ->execute()
        ->fetchFIeld();

      $pledges_count[$country_key] = (int) $count;
    }

    return $pledges_count;
  }

  /**
   * Gets list of all contries that can be selected in the pledge form.
   *
   * @return array
   *   Array with all countries names to be used in the pledge form, keyed by
   *   countries international ISO codes.
   */
  public function unfpa_global_fgm_campaign_pledge_get_countries() {
    return [
      'AF' => t('Afghanistan'),
      'AL' => t('Albania'),
      'DZ' => t('Algeria'),
      'AO' => t('Angola'),
      'AG' => t('Antigua and Barbuda'),
      'AR' => t('Argentina'),
      'AM' => t('Armenia'),
      'AW' => t('Aruba'),
      'AU' => t('Australia'),
      'AT' => t('Austria'),
      'AZ' => t('Azerbaijan'),
      'BS' => t('Bahamas'),
      'BH' => t('Bahrain'),
      'BD' => t('Bangladesh'),
      'BB' => t('Barbados'),
      'BY' => t('Belarus'),
      'BE' => t('Belgium'),
      'BZ' => t('Belize'),
      'BJ' => t('Benin'),
      'BT' => t('Bhutan'),
      'BO' => t('Bolivia, Plurinational State of'),
      'BA' => t('Bosnia and Herzegovina'),
      'BW' => t('Botswana'),
      'BR' => t('Brazil'),
      'BN' => t('Brunei Darussalam'),
      'BG' => t('Bulgaria'),
      'BF' => t('Burkina Faso'),
      'BI' => t('Burundi'),
      'CV' => t('Cabo Verde'),
      'KH' => t('Cambodia'),
      'CM' => t('Cameroon'),
      'CA' => t('Canada'),
      'CF' => t('Central African Republic'),
      'TD' => t('Chad'),
      'CL' => t('Chile'),
      'CN' => t('China'),
      'HK' => t('China, Hong Kong SAR'),
      'MO' => t('China, Macao SAR'),
      'CO' => t('Colombia'),
      'KM' => t('Comoros'),
      'CG' => t('Congo, Republic of the'),
      'CD' => t('Congo, Democratic Republic of the'),
      'CR' => t('Costa Rica'),
      'HR' => t('Croatia'),
      'CU' => t('Cuba'),
      'CW' => t('Curaçao'),
      'CY' => t('Cyprus'),
      'CZ' => t('Czechia'),
      'CI' => t("Côte d'Ivoire"),
      'DK' => t('Denmark'),
      'DJ' => t('Djibouti'),
      'DM' => t('Dominica'),
      'DO' => t('Dominican Republic'),
      'EC' => t('Ecuador'),
      'EG' => t('Egypt, Arab Republic of'),
      'SV' => t('El Salvador'),
      'GQ' => t('Equatorial Guinea'),
      'ER' => t('Eritrea'),
      'EE' => t('Estonia'),
      'SZ' => t('Eswatini'),
      'ET' => t('Ethiopia'),
      'FJ' => t('Fiji'),
      'FI' => t('Finland'),
      'FR' => t('France'),
      'GF' => t('French Guiana'),
      'PF' => t('French Polynesia'),
      'GA' => t('Gabon'),
      'GM' => t('Gambia'),
      'GE' => t('Georgia'),
      'DE' => t('Germany'),
      'GH' => t('Ghana'),
      'GR' => t('Greece'),
      'GD' => t('Grenada'),
      'GP' => t('Guadeloupe'),
      'GU' => t('Guam'),
      'GT' => t('Guatemala'),
      'GN' => t('Guinea'),
      'GW' => t('Guinea-Bissau'),
      'GY' => t('Guyana'),
      'HT' => t('Haiti'),
      'HN' => t('Honduras'),
      'HU' => t('Hungary'),
      'IS' => t('Iceland'),
      'IN' => t('India'),
      'ID' => t('Indonesia'),
      'IR' => t('Iran, Islamic Republic of'),
      'IQ' => t('Iraq'),
      'IE' => t('Ireland'),
      'IL' => t('Israel'),
      'IT' => t('Italy'),
      'JM' => t('Jamaica'),
      'JP' => t('Japan'),
      'JO' => t('Jordan'),
      'KZ' => t('Kazakhstan'),
      'KE' => t('Kenya'),
      'KI' => t('Kiribati'),
      'KP' => t("Korea, Democratic People's Republic"),
      'KR' => t('Korea, Republic of'),
      'XK' => t('Kosovo'),
      'KW' => t('Kuwait'),
      'KG' => t('Kyrgyzstan'),
      'LA' => t("Lao People's Democratic Republic"),
      'LV' => t('Latvia'),
      'LB' => t('Lebanon'),
      'LS' => t('Lesotho'),
      'LR' => t('Liberia'),
      'LY' => t('Libya'),
      'LT' => t('Lithuania'),
      'LU' => t('Luxembourg'),
      'MK' => t('North Macedonia'),
      'MG' => t('Madagascar'),
      'MW' => t('Malawi'),
      'MY' => t('Malaysia'),
      'MV' => t('Maldives'),
      'ML' => t('Mali'),
      'MT' => t('Malta'),
      'MQ' => t('Martinique'),
      'MH' => t('Marshall Islands'),
      'MR' => t('Mauritania'),
      'MU' => t('Mauritius'),
      'MX' => t('Mexico'),
      'FM' => t('Micronesia (Federated States of)'),
      'MD' => t('Moldova, Republic of'),
      'MN' => t('Mongolia'),
      'ME' => t('Montenegro'),
      'MA' => t('Morocco'),
      'MZ' => t('Mozambique'),
      'MM' => t('Myanmar'),
      'NA' => t('Namibia'),
      'NP' => t('Nepal'),
      'NL' => t('Netherlands'),
      'NC' => t('New Caledonia'),
      'NZ' => t('New Zealand'),
      'NI' => t('Nicaragua'),
      'NE' => t('Niger'),
      'NG' => t('Nigeria'),
      'NO' => t('Norway'),
      'OM' => t('Oman'),
      'PK' => t('Pakistan'),
      'PS' => t('Palestine'),
      'PA' => t('Panama'),
      'PG' => t('Papua New Guinea'),
      'PY' => t('Paraguay'),
      'PE' => t('Peru'),
      'PH' => t('Philippines'),
      'PL' => t('Poland'),
      'PT' => t('Portugal'),
      'PR' => t('Puerto Rico'),
      'QA' => t('Qatar'),
      'RE' => t('Reunion'),
      'RO' => t('Romania'),
      'RU' => t('Russian Federation'),
      'RW' => t('Rwanda'),
      'KN' => t('Saint Kitts and Nevis'),
      'LC' => t('Saint Lucia'),
      'VC' => t('Saint Vincent and the Grenadines'),
      'WS' => t('Samoa'),
      'SM' => t('San Marino'),
      'ST' => t('Sao Tome and Principe'),
      'SA' => t('Saudi Arabia'),
      'SN' => t('Senegal'),
      'RS' => t('Serbia'),
      'SC' => t('Seychelles'),
      'SL' => t('Sierra Leone'),
      'SG' => t('Singapore'),
      'SK' => t('Slovakia'),
      'SI' => t('Slovenia'),
      'SB' => t('Solomon Islands'),
      'SO' => t('Somalia'),
      'ZA' => t('South Africa'),
      'SS' => t('South Sudan'),
      'ES' => t('Spain'),
      'LK' => t('Sri Lanka'),
      'SD' => t('Sudan'),
      'SR' => t('Suriname'),
      'SE' => t('Sweden'),
      'CH' => t('Switzerland'),
      'SY' => t('Syrian Arab Republic'),
      'TJ' => t('Tajikistan'),
      'TZ' => t('Tanzania, United Republic of'),
      'TH' => t('Thailand'),
      'TL' => t('Timor-Leste'),
      'TG' => t('Togo'),
      'TO' => t('Tonga'),
      'TT' => t('Trinidad and Tobago'),
      'TN' => t('Tunisia'),
      'TR' => t('Turkey'),
      'TM' => t('Turkmenistan'),
      'TC' => t('Turks and Caicos Islands'),
      'TV' => t('Tuvalu'),
      'UG' => t('Uganda'),
      'UA' => t('Ukraine'),
      'AE' => t('United Arab Emirates'),
      'GB' => t('United Kingdom'),
      'VI' => t('United States Virgin Islands'),
      'US' => t('United States of America'),
      'UY' => t('Uruguay'),
      'UZ' => t('Uzbekistan'),
      'VU' => t('Vanuatu'),
      'VE' => t('Venezuela, Bolivarian Republic of'),
      'VN' => t('Viet Nam'),
      'EH' => t('Western Sahara'),
      'YE' => t('Yemen'),
      'ZM' => t('Zambia'),
      'ZW' => t('Zimbabwe'),
    ];
  }

  /**
   * Checks if the acting user didn't previously submit the pledge form.
   */
  public function unfpa_global_fgm_campaign_pledge_check_submission($email) {
    // For security reasons e-mails submitted via the pledge form are not
    // stored the in database as open text but as SHA-256 hashes. Convert provided
    // e-mail address to hash and query database with hash condition.
    $hash = hash('sha256', $email);
    $result = $this->connection->select('unfpa_global_fgm_campaign_pledges', 'p')
      ->fields('p', ['country'])
      ->condition('user_hash', $hash)
      ->range(0, 1)
      ->execute()
      ->fetchAssoc();

    // If found previous submission then return FALSE.
    if (isset($result['country'])) {
      return FALSE;
    }

    // Otherwise return TRUE.
    return TRUE;
  }

  /**
   * Saves selected data from pledge form submission into the database.
   */
  public function unfpa_global_fgm_campaign_pledge_save_submission($country, $email, $ip) {
    // For security reasons e-mails submitted via the pledge form are not
    // stored the in database as open text but as SHA-256 hashes.
    $hash = '0';
    if ($email != '') {
      $hash = hash('sha256', $email);
    }

    $this->connection->insert('unfpa_global_fgm_campaign_pledges')
      ->fields([
        'country' => $country,
        'user_hash' => $hash,
        'ip_address' => $ip,
        'timestamp' => time(),
      ])
      ->execute();
  }

}
