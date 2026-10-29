<?php

namespace Drupal\unfpa_global_strategic_plan\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\unfpa_global_goals\GoalsData;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Provides route responses for strategic Plan.
 */
class StrategicPlanController extends ControllerBase {

  /**
   * Get module path Drupal\Core\Extension\ModuleHandlerInterface.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * The Categories data services.
   *
   * @var \Drupal\unfpa_global_goals\GoalsData
   */
  protected $goals_data;

  /**
   * Constructs for the following data.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Module handler interface services.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The RequestStack services.
   */
  public function __construct(ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, RequestStack $request_stack, GoalsData $goals_data) {
    $this->moduleHandler = $module_handler;
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();
    $this->requestStack = $request_stack;
    $this->goals_data = $goals_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_handler'),
      $container->get('language_manager'),
      $container->get('request_stack'),
      $container->get('unfpa_global_goals.goals_data')
    );
  }

  /**
   * Swop Report For 2015.
   */
  public function unfpa_global_strategic_plan_details() {
    $share_id = $this->requestStack->getCurrentRequest()->get('shareid');
    $sharecard_id = $this->requestStack->getCurrentRequest()->get('sharecardid');
    // Because for social sites this page is being dynamically constructed,
    // make sute it is not cacheable when social site accesses it.
    if ($share_id || $sharecard_id) {
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
        if ($share_id) {
          $share_id = "Node-".$share_id;
        }else {
          $share_id = "ShareCards";
        }
        $response = new RedirectResponse(Url::fromRoute('unfpa_global_strategic_plan.strategic_plan', [], ['fragment' => '!/' . $share_id])->toString());
        $response->send();
      }
    }

    $base_url = $GLOBALS['base_url'];
    $module_path = $GLOBALS['base_url'] . '/' . $this->moduleHandler->getModule('unfpa_global_strategic_plan')->getPath();
    if ($this->currentLanguage == 'en') {
      $url_with_lang = $base_url;
    }
    else {
      $url_with_lang = $base_url . '/' . $this->currentLanguage;
    }
    $data['maternal_death'] = $this->goals_data->unfpa_global_goals_maternal_death();
    $data['unmet_need'] = $this->goals_data->unfpa_global_goals_unmet_need();
    $data['child_marriage'] = $this->goals_data->unfpa_global_goals_child_marriage();
    $data['ending_fgm'] = $this->goals_data->unfpa_global_goals_ending_fgm();
    $data['ending_gbv'] = $this->goals_data->unfpa_global_goals_ending_gbv();
    return [
      '#theme' => 'strategic_plan_template',
      '#module_path' => $module_path,
      '#base_url' => $base_url,
      '#language' => $this->currentLanguage,
      '#url_with_lang' => $url_with_lang,
      '#attached' => [
        'library' => [
          'unfpa_global_strategic_plan/unfpa_global_strategic_plan',          
        ],
        'drupalSettings' => [
          'unfpa_global_strategic_plan' => ['path' => $module_path],
          'unfpa_global_goals_md' => $data['maternal_death'],
          'unfpa_global_goals_un' => $data['unmet_need'],
          'unfpa_global_goals_cm' => $data['child_marriage'],
          'unfpa_global_goals_ef' => $data['ending_fgm'],
          'unfpa_global_goals_eg' => $data['ending_gbv'],
        ],
      ],
    ];
  }

}
