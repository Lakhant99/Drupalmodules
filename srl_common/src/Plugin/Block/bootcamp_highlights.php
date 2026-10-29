<?php
namespace Drupal\srl_common\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\Entity\Node\Entity\Node;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="bootcamp_highlights",
 *   admin_label = @Translation("Bootcamp Highlights"),
 *   category = @Translation("Blocks")
 * )
 */
//OurProgramsStartDate
class bootcamp_highlights extends BlockBase {
   /**
    * {@inheritdoc}
    */
   public function build() {
      $html = '';
      $program_list = '';
      $domain = \Drupal::service('domain.negotiator')->getActiveId();
      $results = \Drupal::entityQuery('node')
         ->condition('type', 'front')
         ->condition('field_domain_access.target_id', $domain)
         ->execute();
         $node_id = array_values($results)[0];
         $node = \Drupal::entityTypeManager()->getStorage('node')->load($node_id);
         $paragraph_list = $node->get('field_bootcamp_highlights_conten')->getValue();
         foreach($paragraph_list as $key => $pid){
            $paragraph = \Drupal\paragraphs\Entity\Paragraph::load( $pid['target_id'] );
               $items[$key]['title'] = $paragraph->field_bootcamp_title->getValue()[0]['value'];
               $items[$key]['description'] = $paragraph->field_bootcamp_body_content->getValue()[0]['value'];
               $fid = $paragraph->field_paragraph_image->getValue()[0]['target_id'];
               $file = \Drupal\file\Entity\File::load($fid);
               if($file){
                  $items[$key]['icon'] = file_url_transform_relative(file_create_url($file->getFileUri()));
               }
         }
         $html = '
         <div id="block-hilbert-views-block-lorem-ipsum-block-1" class="view view-lorem-ipsum view-id-lorem_ipsum view-display-id-block_1">
            <div class="view-header">
               <h2 class="bootcamp-heading">Bootcamp Highlights</h2>
            </div>
            <div class="view-content">
           
         ';
         foreach($items as $values){
            $html.= '<div>';
               $html.= '<div class="views-field views-field-nothing"><span class="field-content">';
                  $html.='<div class="row">';
                     $html.='
                           <div class="col-md-4">
                              <div class="iconback-bootcamp">
                                 <img class="bootcamp-icon lazyload" alt="Technology Bootcamps" width="100%" height="100%" data-src="'.$values['icon'].'">
                              </div>                            
                           </div>
                           <div class="col-md-8">
                              <h5>'.$values["title"].'</h5>
                              <p class="bootcamp-desc">'.$values["description"].'</p>
                           </div>
                     ';
                  $html.='</div>';
               $html.='</span></div>';
            $html.='</div>';
         }
         $html.='</div></div>'; 

      return array(
         '#markup' => t($html),
         '#cache' => array(
            'max-age' => 0,
         ),
      );
   }
}

