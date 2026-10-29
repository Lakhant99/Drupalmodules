<?php

namespace Drupal\srl_common;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Url;

/**
 * Logic for results page.
 */
class CommonData {
  
    /**
     * The database service.
     *
     * @var \Drupal\Core\Database\Connection
     */
    protected $connection;
  
    
    /**
     * Constructs for annual report configuration.
     *
     * @param \Drupal\Core\Database\Connection $connection
     *   The Database services.
     */
    public function __construct(Connection $connection) {
      global $base_url;
      $this->connection = $connection;
    }
  
    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container) {
      return new static(
            $container->get('database')
        );
    }

    /**
   * Function to get gm_annual_nav data.
   */
    public function getNextDate() {
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
                }
            }else{
                $comming_soon = TRUE;
            }
        }
        $build = [];
        $build = $next_date;
        return $build;
    }
}