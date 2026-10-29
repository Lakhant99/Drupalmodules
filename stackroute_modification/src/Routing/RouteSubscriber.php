<?php
namespace Drupal\stackroute_modification\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
   // Change path '/user/login' to '/login'.
   // \Drupal::logger('login alter')->notice('echo :-'.print_r($parameters,TRUE));
   if ($route = $collection->get('user.login')) {
      $route->setRequirement('_access', 'FALSE');
      // $route->setPath('/system/404');
   }
    // Always deny access to '/user/logout'.
    // Note that the second parameter of setRequirement() is a string.
    if ($route = $collection->get('user.logout')) {
      // $route->setRequirement('_access', 'FALSE'); 
    }
  }
}