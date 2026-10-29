<?php

namespace Drupal\unfpa_global_fgm_campaign\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Cache\Cache;
use Drupal\unfpa_global_fgm_campaign\FGMCampaignData;
use Drupal\Core\Url;
use Drupal\Core\Form\FormBuilderInterface;

/**
 * Get the fgm annual report data.
 */
class FgmCampaignController extends ControllerBase {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $currentLanguage;

  /**
   * The RequestStack services.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The cache backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The FGMCampaignData data services.
   *
   * @var \Drupal\unfpa_global_fgm_campaign\FGMCampaignData
   */
  protected $fgm_campaign_data;

  /**
   * Form builder will be used via Dependency Injection.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs for the following data.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The RequestStack services.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The CacheBackendInterface services.
   * @param \Drupal\unfpa_global_fgm_campaign\FGMCampaignData $fgm_campaign_data
   *   The FGMCampaignData data services.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The FormBuilderInterface service.
   */
  public function __construct(LanguageManagerInterface $language_manager, RequestStack $request_stack, CacheBackendInterface $cache_backend, FGMCampaignData $fgm_campaign_data, FormBuilderInterface $formBuilder) {
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();
    $this->requestStack = $request_stack;
    $this->cacheBackend = $cache_backend;
    $this->fgm_campaign_data = $fgm_campaign_data;
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'), $container->get('request_stack'), $container->get('cache.data'), $container->get('unfpa_global_fgm_campaign.fgm_campaign_data'), $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function unfpa_global_fgm_campaign_data() {
    $share_id = $this->requestStack->getCurrentRequest()->get('share_id');
    // Because for social sites this page is being dynamically constructed,
    // make sute it is not cacheable when social site accesses it.
    if ($share_id) {

      \Drupal::service('page_cache_kill_switch')->trigger();

      // Share id is present in url. If user agent is not share service,
      // then redirect the user to normal url without share id, but with hash tag.
      $agent = $_SERVER['HTTP_USER_AGENT'];
      if (!preg_match('/FacebookExternalHit/i', $agent) &&
        !preg_match('/Facebot/i', $agent) &&
        !preg_match('/Twitterbot/i', $agent) &&
        !preg_match('/Google/i', $agent) &&
        !preg_match('/LinkedInBot/i', $agent)) {
        // Not social site user agent - redirect.
        $response = new RedirectResponse(Url::fromRoute('unfpa_global_fgm_campaign.unfpa_global_fgm_campaign_data', [], ['fragment' => '!/' . $share_id])->toString());
        $response->send();
      }
    }
    $data = $this->unfpa_global_fgm_campaign_page($share_id);

    return [
      '#theme' => 'unfpa_global_fgm_campaign_data',
      '#data' => $data,
      '#attached' => [
        'library' => [
          'unfpa_global_fgm_campaign/unfpa_global_fgm_campaign',
        ],
      ],
      '#cache' => [
        'contexts' => [
          'url.path',
        ],
      ],
    ];
  }

  /**
   * Function to get fgm campaign page data.
   */
  public function unfpa_global_fgm_campaign_page($share_id) {
    $lang = $this->currentLanguage;
    $rtl = FALSE;
    if ($lang == 'ar') {
      $rtl = TRUE;
    }
    $variables = [];
    /* --- Nav. --- */
    $variables['nav'] = $this->fgm_campaign_data->unfpa_global_fgm_campaign_nav($share_id);
    /* --- Entry section. --- */
    $variables['entry'] = $this->fgm_campaign_data->unfpa_global_fgm_campaign_entry();
    /* --- Panels section. --- */
    // Panels section urls are language dependant.
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
    $base_p = ($lang == 'en') ? 'base:/' : 'base:/' . $lang;
    $panels = [
      [
        'title' => t('Wounds and scars'),
        'text' => t('“When I was 7, I was mutilated.”'),
        'image' => 'panel-1.jpg',
        'color' => 1,
        'url' => Url::fromUri($base_p . $urls[$lang][1], ['absolute' => TRUE])->toString(),
      ],
      [
        'title' => t('Harmful norms'),
        'text' => t('“I will never do that to my daughters.”'),
        'image' => 'panel-2.jpg',
        'color' => 2,
        'url' => Url::fromUri($base_p . $urls[$lang][2], ['absolute' => TRUE])->toString(),
      ],
      [
        'title' => t('Intact'),
        'text' => t('“There is no day that I will be cut.”'),
        'image' => 'panel-3.jpg',
        'color' => 3,
        'url' => Url::fromUri($base_p . $urls[$lang][3], ['absolute' => TRUE])->toString(),
      ],
    ];
    if ($rtl) {
      $panels = array_reverse($panels);
    }
    $variables['panels'] = $this->fgm_campaign_data->unfpa_global_fgm_campaign_panels($panels);

    /* --- Actions. --- */
    $variables['actions'] = $this->fgm_campaign_data->unfpa_global_fgm_campaign_actions();

    /* --- Map. --- */
    $take_action_form = $this->formBuilder->getForm('Drupal\unfpa_global_fgm_campaign\Form\TakeActionForm');
    $variables['map'] = $this->fgm_campaign_data->unfpa_global_fgm_campaign_map($take_action_form, $share_id);

    /* --- Share Modal --- */
    $variables['sharemodal'] = $this->fgm_campaign_data->unfpa_global_fgm_campaign_sharemodal();

    return $variables;
  }

  /**
   * {@inheritdoc}
   */
  public function unfpa_global_fgm_campaign_pledgetoendfgm() {
    $data = [];
    $take_action_form = $this->formBuilder->getForm('Drupal\unfpa_global_fgm_campaign\Form\TakeActionForm');

    $variables['module_path'] = $this->module_path;

    // Add pledge form to template.
    $variables['pledge_form'] = $take_action_form;

    // Attach js with pledges count, used by the map.
    $pledge_count = $this->fgm_campaign_data->unfpa_global_fgm_campaign_pledge_get_count();

    return [
      '#theme' => 'unfpa_global_fgm_campaign_pledgetoendfgm',
      '#data' => $variables,
      '#attached' => [
        'drupalSettings' => [
          'unfpa_global_fgm_campaign' => ['pledge_count' => $pledge_count],
        ],
      ],
    ];
  }

}
