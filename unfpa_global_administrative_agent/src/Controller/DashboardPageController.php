<?php

namespace Drupal\unfpa_global_administrative_agent\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\unfpa_global_administrative_agent\DashboardData;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Component\Serialization\Json;

/**
 * Defines DashboardPageController class.
 */
class DashboardPageController extends ControllerBase {

  /**
   * The request variable.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The Dashboard data.
   *
   * @var \Drupal\unfpa_global_administrative_agent\DashboardData
   */
  protected $dashboardData;

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $request_stack, DashboardData $dashboard_data) {
    $this->requestStack = $request_stack;
    $this->dashboardData = $dashboard_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'), $container->get('unfpa_global_administrative_agent.dashboard_data')
    );
  }

  /**
   * Function for dashboard page content.
   */
  public function dashboardData() {
    return [
      '#theme' => 'dashboard_page',
    ];
  }

  /**
   * Returns finance data on load more.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Json string
   */
  public function loadMoreTransactions() {
    $donor_id = $this->requestStack->getCurrentRequest()->get('donorId');
    $partner_id = $this->requestStack->getCurrentRequest()->get('partnerId');
    $fund_id = $this->requestStack->getCurrentRequest()->get('fundId');
    $type = $this->requestStack->getCurrentRequest()->get('type');
    $offset = $this->requestStack->getCurrentRequest()->get('offset');
    $transaction_register = $this->dashboardData->getTransactionRegisterData($type, $donor_id, $partner_id, $offset, $fund_id);
    unset($transaction_register['count']);
    return new JsonResponse(Json::encode($transaction_register));
  }

}
