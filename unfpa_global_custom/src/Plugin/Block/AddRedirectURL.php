<?php

namespace Drupal\unfpa_global_custom\Plugin\Block;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Path\CurrentPathStack;

/**
 * Block to display Add Redirect URL.
 *
 * @Block(
 *   id = "add_redirect_url",
 *   admin_label = @Translation("Add Redirect URL Block"),
 *   category = @Translation("Custom"),
 * )
 */
class AddRedirectURL extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;


  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LanguageManager $language_manager, CurrentPathStack $current_path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->languageManager = $language_manager;
    $this->currentPathStack = $current_path;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration, $plugin_id, $plugin_definition, $container->get('language_manager'), $container->get('path.current')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    global $base_url;
    $language_id = $this->languageManager->getCurrentLanguage()->getId();
    if($language_id == 'en') {
      $url = $base_url;
      $path = explode('/', $this->currentPathStack->getPath());
      $redirect_url = $base_url . "/admin/config/search/redirect/add?source=". $path[1] ."&destination=". $path[1] ."";
    }
    else {
      $url = '/' . $language_id . '/';
      $path = explode($url, $this->currentPathStack->getPath());
      $redirect_url = $base_url . '/' . $language_id . "/admin/config/search/redirect/add?source=". $path[1] ."&destination=". $path[1] ."";
    }
    return [
      '#type' => 'markup',
      '#markup' => '<div><a href='. $redirect_url .'>'. $this->t('Add URL redirect from this page to another location') .'</a></div>',
    ];
  }

}
