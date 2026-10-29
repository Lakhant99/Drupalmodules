/**
 * @file
 * Custom js file with Map 1 Configuration and initialization.
 */

var map_colors = {
  red: '#ef483e',
  orange: '#f8941d',
  yellow: '#fde625',
  pink: '#ec519d',
  n: '#bcbdc0'
};

var map_hover = {
  red: '#f1635b',
  orange: '#f9a94d',
  yellow: '#fef077',
  pink: '#ef6bac',
  n: '#c6c7c9'
};

var simplemaps_worldmap_mapdata = {
  main_settings: {
    // Unique Map Div Id.
    div: 'map',

    // Genreal settings.
    auto_load: 'yes',
    // Pixel value or 'responsive'.
    width: 'responsive',
    // 'on_click', 'on_hover', or 'detect'.
    pop_ups: 'detect',
    // Use image instead of arrow for back zoom.
    back_image: 'no',
    url_new_tab: 'no',
    // For example 'map_images/'.
    images_directory: 'default',
    // Text mobile browsers will see for links.
    link_text: Drupal.t('(Link)'),

    //General colors and styles
    background_color: '#fff',
    background_transparent: 'yes',
    border_color: '#fff',
    border_size: 0.6,
    arrow_color: '#f8941d',
    arrow_color_border: 'none',
    state_color: '#bcbdc0',
    state_hover_color: '#bcbdc0',
    region_opacity: 1,
    region_hover_opacity: 0.7,
    // Hover/unhover color change time.
    fade_time: 0.3,

    //Popup
    popup_color: '#fff',
    popup_opacity: .9,
    popup_shadow: 1,
    popup_corners: 3,
    popup_font: '13px/1.5 unfpatext, Arial, Helvetica, sans-serif',
    // Use your own css.
    popup_nocss: 'no',

    // Labels.
    label_color: '#d5ddec',
    label_hover_color: '#d5ddec',
    label_size: 22,
    label_font: 'Arial',
    hide_labels: 'no',
    // Zoom.
    // Use default regions.
    zoom: 'yes',
    // -1 is zoomed out, 0 is for the first continent etc.
    initial_zoom: -1,
    // Hide adjacent states when starting map zoomed in.
    initial_zoom_solo: 'no',
    // Show back button when zoomed out and do this JavaScript upon click.
    initial_back: 'no',
    // If no, map will zoom all the way out on click.
    zoom_out_incrementally: 'yes',
    zoom_percentage: .99,
    // Time to zoom between regions in seconds.
    zoom_time: .5,
    // Opacity of adjacent states when zoomed in on a state or region.
    adjacent_opacity: .2,

    // State defaults.
    state_description: '',
    state_url: '',
    /*all_states_inactive: 'no',
    all_states_zoomable: 'yes',*/

    // Location defaults
    // See: https://simplemaps.com/docs/locations.
    location_size: 16,
    location_color: '#bcbdc0',
    location_hover_color: '#bcbdc0',
    location_opacity: 1,
    location_hover_opacity: 1,
    location_type: 'circle',
    location_border: 0,
    location_hover_border: 0,
    location_border_color: '#FFFFFF',
    location_description: '',
    location_size: 8,
    all_locations_inactive: 'no'
  },

  state_specific: {
    AF: {
      name: Drupal.t('Afghanistan'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    AL: {
      name: Drupal.t('Albania'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    DZ: {
      name: Drupal.t('Algeria'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    AO: {
      name: Drupal.t('Angola'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    AR: {
      name: Drupal.t('Argentina'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    AM: {
      name: Drupal.t('Armenia'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    AZ: {
      name: Drupal.t('Azerbaijan'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    BD: {
      name: Drupal.t('Bangladesh'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    BY: {
      name: Drupal.t('Belarus'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    BJ: {
      name: Drupal.t('Benin'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    BT: {
      name: Drupal.t('Bhutan'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    BO: {
      name: Drupal.t('Bolivia (Plurinational State of)'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    BA: {
      name: Drupal.t('Bosnia and Herzegovina'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    BW: {
      name: Drupal.t('Botswana'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    BR: {
      name: Drupal.t('Brazil'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    BF: {
      name: Drupal.t('Burkina Faso'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    BI: {
      name: Drupal.t('Burundi'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    KH: {
      name: Drupal.t('Cambodia'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    CM: {
      name: Drupal.t('Cameroon'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    CV: {
      name: Drupal.t('Cape Verde'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    CF: {
      name: Drupal.t('Central African Republic'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    TD: {
      name: Drupal.t('Chad'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    CL: {
      name: Drupal.t('Chile'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    CN: {
      name: Drupal.t('China'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    CO: {
      name: Drupal.t('Colombia'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    KM: {
      name: Drupal.t('Comoros'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    CD: {
      name: Drupal.t('Congo, Democratic Republic of the'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    CG: {
      name: Drupal.t('Congo, Republic of'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    CR: {
      name: Drupal.t('Costa Rica'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    CI: {
      name: Drupal.t("Côte d'Ivoire"),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    CU: {
      name: Drupal.t('Cuba'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    DJ: {
      name: Drupal.t('Djibouti'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    DO: {
      name: Drupal.t('Dominican Republic'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    EC: {
      name: Drupal.t('Ecuador'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    EG: {
      name: Drupal.t('Egypt, Arab Republic of'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    SV: {
      name: Drupal.t('El Salvador'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    GQ: {
      name: Drupal.t('Equatorial Guinea'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    ER: {
      name: Drupal.t('Eritrea'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    ET: {
      name: Drupal.t('Ethiopia'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    GA: {
      name: Drupal.t('Gabon'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    GM: {
      name: Drupal.t('Gambia, The'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    GE: {
      name: Drupal.t('Georgia'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    GH: {
      name: Drupal.t('Ghana'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    GT: {
      name: Drupal.t('Guatemala'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    GN: {
      name: Drupal.t('Guinea'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    GW: {
      name: Drupal.t('Guinea-Bissau'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    HT: {
      name: Drupal.t('Haiti'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    HN: {
      name: Drupal.t('Honduras'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    IN: {
      name: Drupal.t('India'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    ID: {
      name: Drupal.t('Indonesia'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    IR: {
      name: Drupal.t('Iran, Islamic Republic of'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    IQ: {
      name: Drupal.t('Iraq'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    JO: {
      name: Drupal.t('Jordan'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    KZ: {
      name: Drupal.t('Kazakhstan'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    KE: {
      name: Drupal.t('Kenya'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    KP: {
      name: Drupal.t("Korea, Democratic People's Republic of"),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    XK: {
      name: Drupal.t('Kosovo'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_colors['yellow']
    },
    KG: {
      name: Drupal.t('Kyrgyz Republic'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_colors['yellow']
    },
    LA: {
      name: Drupal.t("Lao People's Democratic Republic"),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    LB: {
      name: Drupal.t('Lebanon'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    LS: {
      name: Drupal.t('Lesotho'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    LR: {
      name: Drupal.t('Liberia'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    LY: {
      name: Drupal.t('Libya'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    MK: {
      name: Drupal.t('The former Yugoslav Republic of Macedonia'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    MG: {
      name: Drupal.t('Madagascar'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    MW: {
      name: Drupal.t('Malawi'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    MY: {
      name: Drupal.t('Malaysia'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    MV: {
      name: Drupal.t('Maldives'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    ML: {
      name: Drupal.t('Mali'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    MR: {
      name: Drupal.t('Mauritania'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    MU: {
      name: Drupal.t('Mauritius'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    MX: {
      name: Drupal.t('Mexico'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    MD: {
      name: Drupal.t('Moldova'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    MN: {
      name: Drupal.t('Mongolia'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    MA: {
      name: Drupal.t('Morocco'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    MZ: {
      name: Drupal.t('Mozambique'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    MM: {
      name: Drupal.t('Myanmar'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    NA: {
      name: Drupal.t('Namibia'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    NP: {
      name: Drupal.t('Nepal'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    NI: {
      name: Drupal.t('Nicaragua'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },

    NE: {
      name: Drupal.t('Niger'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    NG: {
      name: Drupal.t('Nigeria'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    OM: {
      name: Drupal.t('Oman'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    PK: {
      name: Drupal.t('Pakistan'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    PS: {
      name: Drupal.t('Palestine'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    PA: {
      name: Drupal.t('Panama'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    PG: {
      name: Drupal.t('Papua New Guinea'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    PY: {
      name: Drupal.t('Paraguay'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    PE: {
      name: Drupal.t('Peru'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    PH: {
      name: Drupal.t('Philippines'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    RW: {
      name: Drupal.t('Rwanda'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    ST: {
      name: Drupal.t('Sao Tome and Principe'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    SN: {
      name: Drupal.t('Senegal'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    RS: {
      name: Drupal.t('Serbia'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    SL: {
      name: Drupal.t('Sierra Leone'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    SO: {
      name: Drupal.t('Somalia'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    ZA: {
      name: Drupal.t('South Africa'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    SS: {
      name: Drupal.t('South Sudan'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    LK: {
      name: Drupal.t('Sri Lanka'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    SD: {
      name: Drupal.t('Sudan'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    SZ: {
      name: Drupal.t('Swaziland'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    SY: {
      name: Drupal.t('Syrian Arab Republic'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    TJ: {
      name: Drupal.t('Tajikistan'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    TZ: {
      name: Drupal.t('Tanzania, United Republic of'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    TH: {
      name: Drupal.t('Thailand'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    TL: {
      name: Drupal.t('Timor-Leste'),
      description: '',
      color: map_colors['orange'],
      hover_color: map_hover['orange']
    },
    TG: {
      name: Drupal.t('Togo'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    TN: {
      name: Drupal.t('Tunisia'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    TR: {
      name: Drupal.t('Turkey'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    TM: {
      name: Drupal.t('Turkmenistan'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    UG: {
      name: Drupal.t('Uganda'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    UA: {
      name: Drupal.t('Ukraine'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    UY: {
      name: Drupal.t('Uruguay'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    UZ: {
      name: Drupal.t('Uzbekistan'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    VE: {
      name: Drupal.t('Venezuela'),
      description: '',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    },
    VN: {
      name: Drupal.t('Vietnam'),
      description: '',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    YE: {
      name: Drupal.t('Yemen'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    ZM: {
      name: Drupal.t('Zambia'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },
    ZW: {
      name: Drupal.t('Zimbabwe'),
      description: '',
      color: map_colors['red'],
      hover_color: map_hover['red']
    },

    // The countries listed below are not highlighted.
    AE: {
      name: Drupal.t('United Arab Emirates'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    AU: {
      name: Drupal.t('Australia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    AT: {
      name: Drupal.t('Austria'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    BE: {
      name: Drupal.t('Belgium'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    BG: {
      name: Drupal.t('Bulgaria'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    BZ: {
      name: Drupal.t('Belize'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    BN: {
      name: Drupal.t('Brunei Darussalam'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    CA: {
      name: Drupal.t('Canada'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    CH: {
      name: Drupal.t('Switzerland'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    CZ: {
      name: Drupal.t('Czechia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    DE: {
      name: Drupal.t('Germany'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    DK: {
      name: Drupal.t('Denmark'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    EE: {
      name: Drupal.t('Estonia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    FI: {
      name: Drupal.t('Finland'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    FJ: {
      name: Drupal.t('Fiji'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    GB: {
      name: Drupal.t('United Kingdom'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    GR: {
      name: Drupal.t('Greece'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    GL: {
      name: Drupal.t('Greenland'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    GY: {
      name: Drupal.t('Guyana'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    HR: {
      name: Drupal.t('Croatia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    HU: {
      name: Drupal.t('Hungary'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    IE: {
      name: Drupal.t('Ireland'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    IS: {
      name: Drupal.t('Iceland'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    IL: {
      name: Drupal.t('Israel'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    IT: {
      name: Drupal.t('Italy'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    JM: {
      name: Drupal.t('Jamaica'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    JP: {
      name: Drupal.t('Japan'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    KR: {
      name: Drupal.t('Korea, Republic of'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    KW: {
      name: Drupal.t('Kuwait'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    LT: {
      name: Drupal.t('Lithuania'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    LU: {
      name: Drupal.t('Luxembourg'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    LV: {
      name: Drupal.t('Latvia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    ME: {
      name: Drupal.t('Montenegro'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    NL: {
      name: Drupal.t('Netherlands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    NO: {
      name: Drupal.t('Norway'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    NZ: {
      name: Drupal.t('New Zealand'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    PL: {
      name: Drupal.t('Poland'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    PT: {
      name: Drupal.t('Portugal'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    QA: {
      name: Drupal.t('Qatar'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    RO: {
      name: Drupal.t('Romania'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    RU: {
      name: Drupal.t('Russia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    EH: {
      name: Drupal.t('Western Sahara'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SA: {
      name: Drupal.t('Saudi Arabia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SR: {
      name: Drupal.t('Suriname'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SK: {
      name: Drupal.t('Slovakia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SI: {
      name: Drupal.t('Slovenia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SE: {
      name: Drupal.t('Sweden'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    US: {
      name: Drupal.t('United States'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    VU: {
      name: Drupal.t('Vanuatu'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    GF: {
      name: Drupal.t('French Guiana'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    FR: {
      name: Drupal.t('France'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    ES: {
      name: Drupal.t('Spain'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    AW: {
      name: Drupal.t('Aruba'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    AI: {
      name: Drupal.t('Anguilla'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    AD: {
      name: Drupal.t('Andorra'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    AG: {
      name: Drupal.t('Antigua and Barbuda'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    BS: {
      name: Drupal.t('Bahamas'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    BM: {
      name: Drupal.t('Bermuda'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    BB: {
      name: Drupal.t('Barbados'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    KY: {
      name: Drupal.t('Cayman Islands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    DM: {
      name: Drupal.t('Dominica'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    FK: {
      name: Drupal.t('Falkland Islands (Malvinas)'),
      description: Drupal.t('In accordance with the editorial directive on the nomenclature of the Falkland Islands (Malvinas) (ST/CS/SER.A/42)'),
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    FO: {
      name: Drupal.t('Faeroe Islands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    GD: {
      name: Drupal.t('Grenada'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    HK: {
      name: Drupal.t('Hong Kong'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    KN: {
      name: Drupal.t('Saint Kitts and Nevis'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    LC: {
      name: Drupal.t('Saint Lucia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    LI: {
      name: Drupal.t('Liechtenstein'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    MF: {
      name: Drupal.t('Saint Martin (French)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    MT: {
      name: Drupal.t('Malta'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    MS: {
      name: Drupal.t('Montserrat'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    NC: {
      name: Drupal.t('New Caledonia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    NR: {
      name: Drupal.t('Nauru'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    PN: {
      name: Drupal.t('Pitcairn Islands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    PR: {
      name: Drupal.t('Puerto Rico'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    PF: {
      name: Drupal.t('French Polynesia'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SG: {
      name: Drupal.t('Singapore'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SB: {
      name: Drupal.t('Solomon Islands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SX: {
      name: Drupal.t('Saint Martin (Dutch)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    SC: {
      name: Drupal.t('Seychelles'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    TC: {
      name: Drupal.t('Turks and Caicos Islands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    TO: {
      name: Drupal.t('Tonga'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    TT: {
      name: Drupal.t('Trinidad and Tobago'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    VC: {
      name: Drupal.t('Saint Vincent and the Grenadines'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    VG: {
      name: Drupal.t('British Virgin Islands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    VI: {
      name: Drupal.t('United States Virgin Islands'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    CY: {
      name: Drupal.t('Cyprus'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    RE: {
      name: Drupal.t('Reunion (France)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    YT: {
      name: Drupal.t('Mayotte (France)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    MQ: {
      name: Drupal.t('Martinique (France)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    GP: {
      name: Drupal.t('Guadeloupe (France)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    CW: {
      name: Drupal.t('Curaco (Netherlands)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    },
    IC: {
      name: Drupal.t('Canary Islands (Spain)'),
      description: '',
      color: map_colors['n'],
      hover_color: map_hover['n']
    }
  },
  locations: {
    0: {
      name: Drupal.t('Comoros'),
      lat: -12.1667,
      lng: 44.25,
      display: 'region'
    },
    1: {
      name: Drupal.t('Maldives'),
      lat: -0.6,
      lng: 73.1,
      display: 'region'
    },
    2: {
      name: Drupal.t('Sao Tome & Principe'),
      lat: 0.333,
      lng: 6.73,
      display: 'region'
    },
    3: {
      name: Drupal.t('Seychelles'),
      lat: -4.5833,
      lng: 55.6667,
      display: 'region'
    },
    4: {
      name: Drupal.t('Cape Verde'),
      lat: 15.1111,
      lng: -23.6167,
      display: 'region'
    },
    5: {
      name: Drupal.t('Pacific SRO'),
      lat: -18,
      lng: 179,
      display: 'region',
      color: map_colors['yellow'],
      hover_color: map_hover['yellow']
    },
    6: {
      name: Drupal.t('Caribbean SRO'),
      lat: 18.1824,
      lng: -77.3218,
      display: 'region',
      color: map_colors['pink'],
      hover_color: map_hover['pink']
    }
  },
  regions: {
    /*0: {
      name: Drupal.t('North America'),
      states: ['MX','CA','US','GL']
    },
    1: {
      name: Drupal.t('Latin America & the Caribbean'),
      states: ['EC','AR','VE','BR','CO','BO','PE','BZ','CL','CR','CU','DO','SV','GT','GY','GF','HN','NI','PA','PY','PR','SR','UY','JM','HT','BS']
    },
    2: {
      name: Drupal.t('Europe'),
      states:['IT','NL','NO','DK','IE','GB','RO','DE','FR','AL','AM','AT','BY','BE','LU','BG','CZ','EE','GE','GR','HU','IS','LV','LT','MD','PL','PT','RS','SI','HR','BA','ME','MK','SK','ES','FI','SE','CH','TR','CY','UA','XK']
    },
    3: {
      name: Drupal.t('Africa and the Middle East'),
      states: ['QA','SA','AE','SY','OM','KW','PK','AZ','AF','IR','IQ','IL','PS','JO','LB','YE','TJ','TM','UZ','KG','NE','AO','EG','TN','GA','DZ','LY','CG','GQ','BJ','BW','BF','BI','CM','CF','TD','CI','CD','DJ','ET','GM','GH','GN','GW','KE','LS','LR','MG','MW','ML','MA','MR','MZ','NA','NG','ER','RW','SN','SL','SO','ZA','SD','SS','SZ','TZ','TG','UG','EH','ZM','ZW']
    },
    4: {
      name: Drupal.t('South Asia'),
      states:['IN','AU','MY','NP','NZ','TH','BN','JP','VN','LK','SB','FJ','BD','BT','KH','LA','MM','KP','PG','PH','KR','ID','CN']
    },
    5: {
      name: Drupal.t('North Asia'),
      states: ['MN', 'RU', 'KZ']
    }*/
  },
  borders: {
    0: {
      name: Drupal.t('Kosovo'),
      path: 'm 1102.2181,356.79763 c 0.5294,-0.2112 1.0339,-0.50801 1.5795,-0.66507 0.1655,-0.015 0.3311,-0.0301 0.4966,-0.0451 0.2412,-0.40621 0.7189,-0.71819 0.7435,-1.21625 0.1369,-0.46302 -0.1218,-1.25401 0.5593,-1.26271 0.4044,0.0263 0.7119,0.45452 1.0766,0.64452 0.864,0.61148 1.7281,1.22297 2.5921,1.83444 0.1186,0.24999 0.081,0.69395 0.2971,0.82277 0.4109,0.0171 0.8218,0.0342 1.2327,0.0513 0.2215,0.43031 0.4416,0.86059 0.068,1.27022 -0.2595,0.50535 -0.5191,1.01069 -0.7786,1.51606 -1.1851,0.35969 -2.3733,0.70944 -3.5511,1.09265 -0.1457,0.40354 -0.098,1.14952 -0.3688,1.33167 -0.2994,-0.13489 -0.7582,-0.12751 -0.7406,-0.54515 -0.1037,-0.44428 -0.2074,-0.88856 -0.3111,-1.33284 -0.6738,-0.52812 -1.3476,-1.05624 -2.0214,-1.58436 -0.2914,-0.63739 -0.5827,-1.2748 -0.8741,-1.91219 z',
      color: '#3B729F',
      size: '1',
      dash: '.'
    },
    1: {
      name: Drupal.t('Sudan'),
      path: 'm 1135.1413,573.78254 1.1589,-1.31346 1.159,-0.23179 0.9271,-3.24502 0.4636,-1.2362 0,-1.31347 c 0.2322,-0.71267 1.0997,-1.63712 1.9315,-2.54966 l 0.6954,-0.77263 2.936,-0.69536 0.5408,1.46799 1.6225,1.54525 1.3135,1.8543 1.2362,1.2362 2.5497,-0.92715 3.8631,0 1.468,1.8543 4.5585,-0.15452 0.077,-0.77263 3.3996,-2.00883 0.5408,-1.08167 0.4636,-1.00441 1.7771,-0.92715 c 1.5682,1.10743 3.4452,2.21485 4.2494,3.32228 l 2.6269,-0.46357 2.4724,-2.54966 1.468,-3.09051 2.5496,-2.31787 -0.618,-4.3267 -1.468,-1.69978 3.6313,-0.15456 2.4724,-1.08168 -0.3863,3.63134 0.6953,5.40837 4.2495,3.70861 0.2318,1.69977 -0.077,1.31347 0.6181,1.46799',
      color: '#3B729F',
      size: '1',
      dash: '.'
    },
    2: {
      name: Drupal.t('Kashmir'),
      path: 'm 1403.7369,423.83216 1.639,0 1.0926,-0.3278 0.5464,-1.42045 1.3112,-0.6556 2.076,-0.54632 2.1853,0.65559 3.1687,-0.54633 3.0595,-0.10926 2.5131,0.54632',
      color: '#fff',
      size: '1',
      dash: '.'
    },
    3: {
      name: Drupal.t('Jammu'),
      path: 'm 1385.271,394.11188 c -0.7978,0.6336 -1.5957,1.26717 -2.3935,1.90076 -1.2223,0.3796 -1.4474,1.7298 -0.6659,2.6884 0.5463,-0.21853 1.0926,-0.43706 1.6389,-0.65559 1.6733,2.27561 3.3465,4.55126 5.0198,6.82686 0.8589,0.92105 -0.4741,1.82162 -0.7584,2.67926 0.6504,1.65986 1.2109,3.35415 1.8981,4.9997 1.2227,2.02051 3.14,3.49237 4.8764,5.05274 l 0.089,-2.9823 -0.8499,-2.31788 -0.7726,-0.30905 -0.9272,-1.54525 0.1546,-2.08609 1.5452,-0.69536 6.1038,1.15894 2.0088,0.30905 1.3134,-1.00442 2.8588,-0.46357',
      color: '#3B729F',
      size: '1',
      dash: '.'
    },
    4: {
      name: Drupal.t('Korea'),
      path: 'm 1662.9603,388.19792 c 0.1932,-1.00441 0.3863,-2.00883 0.5795,-3.01324 0.5812,-0.23536 1.1364,-0.64371 1.7996,-0.53494 1.0246,-0.0115 2.0719,0.1459 3.0684,-0.16285 0.3804,-0.17681 1.2027,0.01 1.0508,-0.60464 0.01,-0.46725 0.021,-0.93451 0.031,-1.40176',
      color: '#3B729F',
      size: '0.6',
      dash: '.'
    },
    5: {
      name: Drupal.t('Somalia'),
      path: 'm 1234.9213,603.02797 c 0.2563,0.90027 0.8011,-0.50856 1.202,-0.76486 l 4.4799,-0.9834 1.5297,-2.18531 5.2447,-1.96678 4.6984,0.49169 16.7177,-19.72246',
      color: '#3B729F',
      size: '1',
      dash: '.'
    },
    6: {
      name: Drupal.t('Kashmir-Jammu-China'),
      path: 'm 1420.0625,415.12502 -1.625,-1.25 -0.75,-1.875 1.4375,-1 -0.125,-0.5625 -0.1875,-0.5625 -0.6875,-0.5 -0.875,-0.1875 -0.5625,0 -0.4375,-0.25 -0.6875,-0.0625 -0.9375,0.25 -0.4375,-0.875 -0.8125,-2.125 -1,-1.0625 -0.3125,-0.875 -0.062,-0.8125 -0.3125,-0.4375',
      color: '#3B729F',
      size: '1',
      dash: '.'
    }
  }

};//end of simplemaps_worldmap_mapdata

var map = simplemaps_worldmap.create();
map.load();
