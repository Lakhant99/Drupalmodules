<?php
/**
 * @file
 * Definition of Drupal\unfpa_global_custom\Plugin\views\filter\RelatedContentTitles.
 */
namespace Drupal\unfpa_global_custom\Plugin\views\filter;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Drupal\views\ViewExecutable;
/**
 * Filters by given list of related content title options.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("programme_country_titles")
 */
class ProgrammeCountryTitles extends ManyToOne {

  /**
   * The current display.
   *
   * @var string
   *   The current display of the view.
   */
  protected $currentDisplay;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->valueTitle = t('Allowed Programme Country Titles');
    $this->definition['options callback'] = array($this, 'generateOptions');
    $this->currentDisplay = $view->current_display;
  }

  /**
   * Helper function that generates the options.
   * @return array
   */
  public function generateOptions() {
    $storage = \Drupal::entityManager()->getStorage('node');
    $nodedata = [];
    $nodedata = $storage->loadByProperties(['type' => 'ct_data_portal', 'status' => 1]);
    $data = [];
    if(is_array($nodedata) && count($nodedata) > 0) {
      foreach ($nodedata as $value) {
        $title = explode('UNFPA ', $value->getTitle());
        $data[$value->id()] = $title[1];
      }
      asort($data);
    }

    return $data;
  }

}
