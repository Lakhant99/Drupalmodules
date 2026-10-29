<?php
namespace Drupal\srl_common\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\file\Entity\File;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="our_programs",
 *   admin_label = @Translation("Our programs"),
 *   category = @Translation("Blocks")
 * )
 */
//OurPrograms 
class OurPrograms extends BlockBase {
   /**
    * {@inheritdoc}
    */
   public function build() {
      $html = '';
      $block_html = '';
      $block_title = 'Our Program';
      $domain = \Drupal::service('domain.negotiator')->getActiveId();
      $grid_image = '';
      $grid_fid = null;
      // $domain = 'srl_hilbert_qc_niit_mts_com';
      $results = \Drupal::entityQuery('node')
         ->condition('type', 'our_programs')
         ->condition('field_domain_access.target_id',$domain)
         ->range(0,10)
         ->sort('nid','ASC')
         ->execute();
      if(count($results) > 0){
         $node_storage = \Drupal::entityTypeManager()->getStorage('node');
         $nodes = $node_storage->loadMultiple($results);
         $row_size = (12/count($results));

         /**get university specific grid images */
         $results_refrence = \Drupal::entityQuery('node')
            ->condition('type', 'course_university_mapping')
            ->condition('field_course_code_reference.target_id',$results,'IN')
            ->condition('field_domain_access.target_id',$domain)
            ->execute();
            $nodes_refrence = $node_storage->loadMultiple($results_refrence);
            $get_product_pdf = array();
            foreach($nodes_refrence as $index => $map_node){
               if($map_node->field_program_grid_image->getValue()){
                  $grid_fid = $map_node->get('field_program_grid_image')->getValue()[0]['target_id'];
               }
               if($map_node->field_course_brochure->getValue()){
                  $nodeid_product = $map_node->get('field_course_code_reference')->getValue()[0]['target_id'];
                  $get_product_pdf[$nodeid_product] =$map_node->get('field_course_brochure')->getValue()[0]['target_id'];

               }
              if($grid_fid){
                $file = file::load($grid_fid);
                $program_nid = $map_node->get('field_course_code_reference')->getValue()[0]['target_id'];
                $grid_image[$program_nid] = file_url_transform_relative(file_create_url($file->getFileUri()));
              }
            }
         /** 
          * get program values 
          * @program
          */
         foreach($nodes as $key => $node){
            $image = '';
            $program[$key]['nid'] = $node->id();
            $program[$key]['title'] = $node->getTitle();
            if(!empty($grid_image[$node->id()])){
               $image = '<img class="img-fluid"  src="'.$grid_image[$node->id()].'">';
               $program[$key]['image'] = $image;
               $program[$key]['image_src'] = $grid_image[$node->id()];
            }else{
               if($domain != 'mercy_edu' || $domain == 'stackroute_mercy_college'){
                  $fid = $node->get('field_image')->getValue()[0]['target_id'];
               }else{
                  $fid = $node->get('field_banner_image')->getValue()[0]['target_id'];
               }
               if($fid){
                  $file = File::load($fid);
                  $image = '<img class="img-fluid lazyload" alt="Technology Bootcamps" width="100%" height="100%" data-src="'.file_url_transform_relative(file_create_url($file->getFileUri())).'">';
                  $program[$key]['image'] = $image;
                  $program[$key]['image_src'] = file_url_transform_relative(file_create_url($file->getFileUri()));
               }
            }

            if($node->field_external_link->getValue()){
               $learn_more = $node->get('field_external_link')->getValue()[0]['value'];
               $program[$key]['learn_more'] = '<a class="btn btn-program" href="'.$learn_more.'?cev=learn-more">LEARN MORE</a>';
            }else{
               $alias = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$node->id());
               $program[$key]['learn_more'] = '<a class="btn btn-program" href="'.$alias.'?cev=learn-more">LEARN MORE</a>';
            }
            if($get_product_pdf[$node->id()]){
               $program[$key]['download_pdf'] = '
               <button type="button" data-course="'.$node->id().'" data-coursename="'.$node->getTitle().'" class="alpha-link alpha-uppercase orange-link btnDownloadBrochure" data-toggle="modal" data-target="#download_brochure">Download PDF</button>';
            }else{
               $program[$key]['download_pdf'] = '<button type="button"  class="alpha-link alpha-uppercase orange-link btnDownloadBrochure" style="cursor: not-allowed;">Download PDF</button>';
            }
         }
         if(count($program) > 1){
            $block_title = 'Our Programs';
         }
         if($domain == 'mercy_edu' || $domain== 'stackroute_mercy_college'){
            $html = $this->mercyLayout($program,$row_size);
         }else{
            $colid = 'col-md-3';
            if(count($program) > 4){
               $colid = 'col-md-4';
            }
            foreach($program as $value){
               $html.='<div class="'.$colid.' 9 '.$row_size.' mb-4">';
                  $html.='<span class="field-content">';
                     $html.='
                        <div class="programname">'.$value['title'].'</div>
                        '.$value['image'].'
                        <div class="progress-actionbtn">
                           '.$value['learn_more'].'
                           '.$value['download_pdf'].'
                        </div>
                     ';
                  $html.='</span>';
               $html.='</div>';
            }
         }
         $block_html.= '<div class="view-our-programs view-id-our_programs">';
            $block_html.='<div class="view-header"><h2 class="programs-heading">'.$block_title.'</h2></div>';
            $block_html.='<div class="view-content"><div class="row mt-4 justify-content-center">';
               $block_html.= $html;
            $block_html.='</div></div>';
         $block_html.='</div>'; 

      }
      return array(
         '#markup' => t($block_html),
         '#cache' => array(
            'max-age' => 0,
         ),
      );
   }
   public function mercyLayout($program, $row_size){
      $html = '';
      foreach($program as $value){
         $html.=' 
         <div class="layout-background col-md-'.$row_size.' mb-3">
            <div class="views-field views-field-nothing" style="background-image:url('.$value['image_src'].')">
               <div class="program-title pl-5">'.$value['title'].'</div>
               <div class="marquee-block fade-full my-5">
                  
               <div class="fade-in">
               <h4>MARKET TRENDS</h4>
               <p><span><i class="fa fa-circle" style="font-size:10px;"></i></span> Technology continues to evolve rapidly and has a shorter shelf life</p>
               <p><span><i class="fa fa-circle" style="font-size:10px;"> </i></span> It is becoming harder for businesses to forecast the resources they need.</p>
               <p><span><i class="fa fa-circle" style="font-size:10px;"></i></span> Full Stack Engineers who have a comprehensive technology skillset are in high demand.</p>
               </div>
               <div class="fade-out track-fade-none">
               <h4>JOB DEMAND</h4>
               <p><span><i class="fa fa-circle" style="font-size:10px;"></i></span> 38% of Hiring Managers are looking for Full Stack developers.</p>
               <p><span><i class="fa fa-circle" style="font-size:10px;"> </i></span> 25% Year on Year growth for JavaScript developers</p>
               <p><span><i class="fa fa-circle" style="font-size:10px;"></i></span> 35%Year on Year growth for Full Stack Engineers</p>
               </div>


               </div>
               <div class="progress-actionbtn pl-md-5">
                  '.$value['learn_more'].'
                  '.$value['download_pdf'].'
               </div>
            </div>
         </div>';
      }

      return $html;
   }
}

