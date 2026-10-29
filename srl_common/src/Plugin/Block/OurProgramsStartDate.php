<?php
namespace Drupal\srl_common\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\taxonomy\Entity\Term;


/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id ="our_programs_start_date",
 *   admin_label = @Translation("Our programs Start Date"),
 *   category = @Translation("Blocks")
 * )
 */
//OurProgramsStartDate
class OurProgramsStartDate extends BlockBase {
   /**
    * {@inheritdoc}
    */
   public function build() {
      $html = '';
      $program_list = '';
      $comming_soon = FALSE;
      $domain = \Drupal::service('domain.negotiator')->getActiveId();
      $current_date = \Drupal::time()->getRequestTime();
      // $domain = 'srl_hilbert_qc_niit_mts_com';
      $results = \Drupal::entityQuery('node')
         ->condition('type', 'course_university_mapping')
         ->condition('field_domain_access.target_id', $domain)
         ->range(0,10)
         ->sort('field_course_code_reference.target_id','ASC')
         ->execute();
         foreach($results as $value){
            $course_map[] = $value;
         }
         if(count($course_map) > 0){
            $node_storage = \Drupal::entityTypeManager()->getStorage('node');
            $nodes = $node_storage->loadMultiple($course_map);
            $batch_dates = [];
            foreach($nodes as $node){ // start foreach 
               $uni_id = $node->get('field_course_code_reference')->getValue()[0]['target_id'];
               $close_day = $node->get('field_registration_close_day')->getValue()[0]['value'];
               $course_title = \Drupal::entityTypeManager()->getStorage('node')->load($uni_id);
               
               $batch_dates = $node->get('field_course_batch_start_date')->getValue();
               $course_name[] = $course_title->getTitle();
            }
               // $close_day = '9 days';  
               $next_current_date = strtotime('+'.$close_day, $current_date); // 13th
            
               if(count($batch_dates) > 0){
                  $date_timestamp = [];
                  foreach($batch_dates as $batch_date){
                     $get_date = strtotime($batch_date['value']);
                     $get_date = date('d M Y',$get_date).' 23:59:59';
                     $get_date = strtotime($get_date);
                     if($get_date >= $next_current_date){
                        $date_timestamp[] = $get_date;
                     }
                  }
                  sort($date_timestamp);  
                  if(count($date_timestamp) > 0){
                     $next_date = date('F d',$date_timestamp[0]).'<span>'.date('S',$date_timestamp[0]).'</span> '.date('Y',$date_timestamp[0]);
                     $registration_close = date('d M Y', strtotime('-'.$close_day, $date_timestamp[0]));

                     $counter_update ='<div id="timer" class="program-date timer-inner" data-endDate="'.$registration_close.' 23:59:59">          
                              <div class="time-update" id="hours"></div>
                              <div class="time-update" id="minutes"></div>
                              <div class="time-update" id="seconds"></div>
                           </div>';
                  }
   
               }else{
                  $comming_soon = TRUE;
               }
               $course_list_li = '';
               foreach($course_name as $key => $c_name){
                  $course_list_li.='<li>'.$c_name.'</li>'; 
                  if($c_name == 'Web Development'){
                     $program_list.='<li class="bg-white radius date-tiles" id="program'.$key.'">';
                        $program_list.='<div class="icon"><img src="/themes/hilbert/images/default_images/web.png"></div>';
                        $program_list.='<a href="">'.$c_name.'</a>';
                     $program_list.='</li>'; 
                  }elseif($c_name == 'Data Analytics'){
                     $program_list.='<li class="bg-white radius date-tiles" id="program'.$key.'">';
                        $program_list.='<div class="icon"><img src="/themes/hilbert/images/default_images/data.png"></div>';
                        $program_list.='<a href="">'.$c_name.'</a>';
                     $program_list.='</li>'; 
                  }elseif($c_name == 'Cyber Security'){
                     $program_list.='<li class="bg-white radius date-tiles" id="program'.$key.'">';
                     $program_list.='<div class="icon"><img src="/themes/hilbert/images/default_images/cyber.png"></div>';
                     $program_list.='<a href="">'.$c_name.'</a>';
                     $program_list.='</li>'; 
                  }elseif($c_name == 'Cloud Systems Administration'){
                     $program_list.='<li class="bg-white radius date-tiles" id="program'.$key.'">';
                     $program_list.='<div class="icon"><img src="/themes/hilbert/images/default_images/cloud.png"></div>';
                        $program_list.='<a href="">'.$c_name.'</a>';
                     $program_list.='</li>'; 
                  }else{
                     $program_list.='<li class="bg-white radius date-tiles" id="program'.$key.'">';
                        $program_list.='<div class="icon"><img src="/themes/hilbert/images/default_images/web.png"></div>';
                        $program_list.='<a href="">'.$c_name.'</a>';
                     $program_list.='</li>'; 
                  }
               }
               if($course_list_li ){
                  $html.='<div id="our-program-container">';
                     $html.='
                        <div class="view-header mb-5">
                           <h2 class="programs-heading text-center">Program Schedule</h2>
                        </div>
                        <div class="bg-blue text-white p-3">
                           <ul class="services mb-0 pl-0">
                              '.$course_list_li.'
                           </ul>
                        </div>
                        <div class="programschedule-new mt-3 mb-5 d-flex align-items-center relative lazyload" loading="lazy" style="background-image:url(/themes/hilbert/images/bg3.jpeg);">
                           <div class="container-fluid">
                              <div class="row">
                                 <div class="col-md-7 p-0">
                                    <div class="orange-block">
                                       <p class="mb-0 skew-normal">6:00PM – 10:00PM EST ON MONDAYS AND </br>
                                       6:00PM - 8:00PM EST ON THURSDAYS</p>
                                    </div>
                                    <div class="blue-block mt-3">
                                       <p class="mb-0 skew-normal">NEXT START DATE THE WEEK OF</br> '.$next_date.'</p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           ';
                           /** timer counter */
                           $html.='<div class="footer_section section_left d-flex justify-content-center py-2 align-items-center">';
                                 // $html.='<img src="/themes/hilbert/images/default_images/sand.png">';
                                 $html.='<div class="footer-section-close">';
                                    $html.='<div class="footer_text py-2">';
                                       // $html.='<P class="registration">REGISTRATION CLOSES :</P>';
                                       $html.='<div id="our-program-counter" class="timer">'.$counter_update.'</div>';
                                    $html.='</div>';
                                 $html.='</div>';
                              $html.='</div>';
                        $html.='</div>
                     
                     
                     ';
                  $html.='</div>';
               }else{
                  $html.='<h2>Program Schedule</h2><div id="our-program-container"><div class="row">';
                     $html.='<div class="col-md-5">';
                        $html.='<div class=" section_left"><ul>';
                           $html.= $program_list;
                        $html.='</ul></div>';
                     $html.='</div>';
                     $html.='<div class="col-md-7">';
                        if($comming_soon){
                           $html.='<div class="coming-soon">';
                           $html.='</div>';
                        }else{
                           $html.='<div class=" bg-white section_right">';
                              $html.='<div class="text-center header-text">';
                                 $html.='<P class="time py-3"><span>6:00PM – 10:00PM</span> EST ON MONDAYS
                                    <br>AND <span>6:00PM TO 8:00PM</span> EST ON THURSDAYS</P>';
                                 $html.='<P class="section_date">
                                 <span class="d-block" style="font-size: 20px;line-height: 20px;">NEXT START DATE THE WEEK OF</span>
                                 <span class="section_month">'.$next_date.'</p>';
                                 // $html.='<P class="section_date">REGISTRATION CLOSES</P>';
                              $html.='</div>';
                              $html.='<div class="footer_section section_left d-flex justify-content-center py-2 align-items-center">';
                                 $html.='<span class="sand-icon"><img src="/themes/hilbert/images/default_images/sand.png"></span>';
                                 $html.='<div class="footer-section-close">';
                                    $html.='<div class="footer_text py-2">';
                                       // $html.='<P class="registration">REGISTRATION CLOSES :</P>';
                                       $html.='<div id="our-program-counter">'.$counter_update.'</div>';
                                    $html.='</div>';
                                 $html.='</div>';
                              $html.='</div>';
                           $html.='</div>';
                        }
                           // $html.= $program_list; 
                     $html.='</div>';
                  $html.='</div></div>'; 
               }
         }else{
            $html = '  ';
         }
      return array(
         '#markup' => t($html),
         '#cache' => array(
            'max-age' => 0,
         ),
      );
   }
}

