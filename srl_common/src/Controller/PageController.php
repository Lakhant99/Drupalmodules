<?php

namespace Drupal\srl_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Database\Database;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;


class PageController extends ControllerBase{
    
    public function AboutUS(){
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $results = \Drupal::entityQuery('node')
        ->condition('type', 'about')
        ->condition('field_domain_access.target_id', $domain)
        ->execute();
        foreach($results as $value){
            $node_id = $value;
        }
        if($node_id){
            $node_storage = \Drupal::entityTypeManager()->getStorage('node');
            $node = $node_storage->load($node_id);
            $banner = $node->get('body')->getValue()[0]['value'];
            $description = $node->get('field_about_content')->getValue()[0]['value'];
            $skil_base_training_graph = render(views_embed_view('our_programs', 'block_1'));
            $skills_training['description'] = $node->get('field_skills_training_content')->getValue()[0]['value'];
            $fid = $node->get('field_skill_training_image')->getValue()[0]['target_id'];
            $file = \Drupal\file\Entity\File::load($fid);
            if($file){
                $image_src = file_url_transform_relative(file_create_url($file->getFileUri()));
                $skills_training['image'] = '<img src="'.$image_src.'">';
            }
            
           $block = \Drupal\block\Entity\Block::load('bootcamphighlights');
           $bootcamp_highlights = \Drupal::entityManager()->getViewBuilder('block')->view($block);
            $map = getUniversity_Lat_Lang();
            if($map['field_iframe_map_view_value'] && $map['field_university_map_html_value']){
                $map_section['map'] = $map['field_university_map_html_value'];
            }else{
                
                $map_section['map'] = \Drupal::service('plugin.manager.block')
                ->createInstance('university_map_block', [])
                ->build();
            }
            $map_section['description'] = $node->get('field_mapsection_content')->getValue()[0]['value'];
            
        }
        return [
            '#theme' => 'page_about_us_template',
            '#banner_image' => $banner,
            '#description' => $description,
            '#skills_based_training_content' => $skil_base_training_graph,
            '#skills_training' => $skills_training,
            '#bootcamp_highlights' => $bootcamp_highlights,
            '#map_location' => $map_section,
            '#chat_with_advisior' => NULL,
            '#cache' => array(
                'max-age' => 0,
            ),
        ];

    }
    public function PageFAQsTitle(){
        $site_name = \Drupal::config('system.site')->get('name');
        return $site_name.' FAQs';
    }
    public function PageFAQs(){
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        // $domain = 'mercy_edu';
        $faq_left = '';
        $faq_right = '';
        $dates_string = null;
        /** Load batch dates */
        $values = [
            'type' => 'course_university_mapping',
            'field_domain_access.target_id' => $domain,
            'status' => 1,
          ];
        $nodes_university = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties($values);
        // $nid = array_key_first($nodes_university);
        foreach($nodes_university as $key_id => $node_value){
            $batch_date = $node_value->field_course_batch_start_date->getValue();
            $course_fee = $node_value->get('field_fee')->getValue()[0]['value'];
            break;
        }
        $items = count($batch_date)-1;
        foreach($batch_date as $key => $date_value){
            $datetime = strtotime($date_value['value']);
            if($items == $key){
            $dates_string.= date('M d, Y',$datetime);
            }else{
            $dates_string.= date('M d, Y',$datetime).', ';
            }
        
        }

        if( $domain == 'stackroute_mercy_college' || $domain == 'mercy_edu'){
            $page_content['domain'] = 'mercy_edu';
        }else{
            $page_content['domain'] = $domain;
        }
        $values = [
            'type' => 'faq',
            'field_domain_access.target_id' => $domain,
            'status' => 1,
          ];
        $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties($values);
        
        if($nodes){
            foreach($nodes as $node){
                if(count($node->get('field_faq_questions')->getValue()) > 0){
                    $paragraph = $node->field_faq_questions->getValue();
                    $q_count = 0;
                    foreach ( $paragraph as $key => $element ) {
                        $q_count++;
                        $p = \Drupal\paragraphs\Entity\Paragraph::load( $element['target_id'] );
                        $faqs[$q_count]['title'] = $p->field_title->getValue()[0]['value'];
                        $faqs[$q_count]['answer'] = $p->field_description->getValue()[0]['value'];
                    }

                }
            }
            if($domain == 'mercy_edu' || $domain == 'stackroute_mercy_college'){
                foreach($faqs as $key => $values){
                    $page_content['faqs'][$key]['title'] = $values['title'];
                    $answer = str_replace('#CORSE_BATCH_START_DATE#',$dates_string,$values['answer']);
                    $answer = str_replace('#COURSEFEE#','$'.number_format($course_fee, 0, '.', ','),$answer);
                    $page_content['faqs'][$key]['answer'] = $answer;
                }
            }else{
                $break_key = $q_count/2;
                $break_key = $break_key > 3 ? $break_key+2 : 8;
                $grid_class = 'col-sm-12';
                foreach($faqs as $key => $values){

                    $values['answer'] = str_replace('#CORSE_BATCH_START_DATE#',$dates_string,$values['answer']);
                    $values['answer'] = str_replace('#COURSEFEE#','$'.number_format($course_fee, 0, '.', ','),$values['answer']);
                    if($key <= $break_key){
                        $faq_left.='<div class="qa">';
                            $faq_left.='<p class="question"><span class="question-count">Question '.$key.'</span>'.$values['title'].'</p>';
                            $faq_left.='<div class="answers"><span class="answer-count">Answers</span>'.$values['answer'].'</div>';
                        $faq_left.='</div>';
                    }else{
                        $grid_class = 'col-sm-6';
                        $faq_right.='<div class="qa">';
                            $faq_right.='<p class="question"><span class="question-count">Question '.$key.'</span>'.$values['title'].'</p>';
                            $faq_right.='<div class="answers"><span class="answer-count">Answers</span>'.$values['answer'].'</div>';
                        $faq_right.='</div>';
                    }
                }
                $page_content['faq_left'] = '<div class="'.$grid_class.'">'.$faq_left.'</div>';
                $page_content['faq_right'] = '<div class="'.$grid_class.'">'.$faq_right.'</div>';
            }
            
        }
        
        
        return [
            '#theme' => 'page_faqs_template',
            '#page_content' => $page_content,
        ];
    }
    public function PageSupportTitle(){
        $site_name = \Drupal::config('system.site')->get('name');
        return $site_name.' support';
    }
    public function PageSupport(){
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
         $values = [
            'type' => 'support_template',
            'field_domain_access.target_id' => $domain,
            'status' => 1,
          ];
        $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties($values);
        
        if($nodes){
            foreach($nodes as $node){
                $page_content['body'] = $node->get('body')->getValue()[0]['value'];
                $page_content['learn_support_content'] = $node->get('field_learn_support_content')->getValue()[0]['value'];
                $page_content['university_content'] = $node->get('field_university_content')->getValue()[0]['value'];
                $fid = $node->get('field_university_image')->getValue()[0]['target_id'];
                $file = \Drupal\file\Entity\File::load($fid);
                if($file){
                    $file_src = file_create_url($file->getFileUri());
                    $page_content['university_image'] = file_url_transform_relative($file_src);
                }
            }
        }
        return [
            '#theme' => 'page_support_template',
            '#page_content' => $page_content,
        ];
    }
    public function PagePrivacyPolicy(){
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
         $values = [
            'type' => 'privacy_policy',
            'field_domain_access.target_id' => $domain,
            'status' => 1,
          ];
        $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties($values);
        $page_content['body'] = '';
        if($nodes){
            foreach($nodes as $node){
                // kint($node);
                $page_content['body'] = $node->get('body')->getValue()[0]['value'];
            }
        }
        return [
            '#type' => 'markup',
            '#markup' => $page_content['body'],
        ];

    }
    public function PageTermofUse(){
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $vocabulary_name = 'university';
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', $vocabulary_name);
        $query->condition('field_university_domain', $domain);
        $query->sort('weight');
        $tids = $query->execute();
        $terms = Term::loadMultiple($tids);
        foreach($terms as $term) {
                $termid = $term->get('field_term_of_use')->getValue()[0]['target_id'];
           }
           $description = \Drupal\taxonomy\Entity\Term::load($termid)->get('description')->value;
           
            
        return [
            '#type' => 'markup',
            '#markup' => $description,
        ];
       

    }
}
