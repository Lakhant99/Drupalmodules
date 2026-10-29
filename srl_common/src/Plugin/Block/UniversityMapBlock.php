<?php
namespace Drupal\srl_common\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="university_map_block",
 *   admin_label = @Translation("University map block"),
 *   category = @Translation("Blocks")
 * )
 */

//OurProgramsStartDate
class UniversityMapBlock extends BlockBase {
    /**
      * {@inheritdoc}
      */
    public function build() {
      $map = getUniversity_Lat_Lang();
      $html = '<div id="googleMap" style="width:360px;height:450px;" data-lat="'.$map['lat'].'" data-lng="'.$map['lng'].'"></div>';

      return array(
        '#markup' => t($html),
        '#cache' => array(
          'max-age' => 0,
        ),
      );
  }
}
