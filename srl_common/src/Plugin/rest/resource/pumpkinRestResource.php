<?php

namespace Drupal\srl_common\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


/**
 * Provides a resource to get view modes by entity and bundle.
 * @RestResource(
 *   id = "pumpkin_user_first_sign_on",
 *   label = @Translation("pumpkin User First Sign On"),
 *   uri_paths = {
 *     "canonical" = "/pumpkin-rest/user-first-signon",
 *     "create" = "/pumpkin-rest/user-first-signon"
 *   }
 * )
 */


 

 class pumpkinRestResource extends ResourceBase {
    
  public function get() {
    // Implementing our custom REST Resource here.
    // Use currently logged user after passing authentication and validating the access of term list.
    
  //Apples vocabulary
	$vid = 'course_category';
	$terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
	foreach ($terms as $term) {
	  $term_result[] = array(
	    'id' => $term->tid,
		'name' => $term->name
	  );
	}

    $response = new ResourceResponse($term_result);
    //Uncomment if desired to cache
    //$response->addCacheableDependency($term_result);
    return $response;
  }
  

    /**
   * @return \Drupal\rest\ResourceResponse
   *   The HTTP response objects
   */
    public function post(Request $request) {
      //Set second param of TRUE to have data returned as array as opposed to stdclass object
     
       $data = json_decode($request->getContent(), TRUE);
       try {
        print_r($data, true);   
        } catch (EntityStorageException $e) {
          \Drupal::logger('custom-rest')->error($e->getMessage());
        }
       //Dump data to watchdog for testing
       \Drupal::logger("pumpkinrest")->debug("Got a request:<pre>". print_r($data, true) ."</pre>");
       return  new ResourceResponse($data);
      }
    


    //End parent class
 }