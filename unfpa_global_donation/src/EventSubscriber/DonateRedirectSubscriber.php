<?php

namespace Drupal\unfpa_global_donation\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\node\NodeInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\AliasManager;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Description of DonateRedirectSubscriber.
 *
 */
class DonateRedirectSubscriber implements EventSubscriberInterface {

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * The path alias.
   *
   * @var \Drupal\Core\Path\AliasManager
   */
  protected $aliasManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Path\CurrentPathStack $current_path
   *   The current path.
   * @param \Drupal\Core\Path\AliasManager $alias_manager
   *   The alias manager.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(CurrentPathStack $current_path, AliasManager $alias_manager, PathMatcherInterface $path_matcher, RouteMatchInterface $route_match) {
    $this->currentPath = $current_path;
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
    $this->routeMatch = $route_match;
  }

  /**
   * Listen to kernel.request events and call arabic_russian_page_or_404.
   * {@inheritdoc}
   * @return array Event names to listen to (key) and methods to call (value)
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['donateRedirect'];
    return $events;
  }

  /**
   * Callback function for Request Event.
   */
  public function donateRedirect() {
    // Check current page not admin.
    $current_path = $this->currentPath->getPath();
    $alias = $this->aliasManager->getAliasByPath($current_path);
    if ($alias == '/donate') {
      \Drupal::service('page_cache_kill_switch')->trigger();
      $remote_ip = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : \Drupal::request()->getClientIp();
      $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $remote_ip));
      $country_details = json_encode($query);
      $ip_details = json_decode($country_details);
      $cc = $ip_details->countryCode;
      if (!empty($cc)) {
        $current_country = $cc;
        switch ($current_country) {
          case 'US':
            $redirect = new RedirectResponse('https://donate.friendsofunfpa.org/us-en/general');
            $redirect->send();
            break;

          default:
            $redirect = new RedirectResponse('https://donate.unfpa.org/donate');
            $redirect->send();
        }
      }
      else {
        $redirect = new RedirectResponse('https://donate.unfpa.org/donate');
        $redirect->send();
      }
    }
  }

}
