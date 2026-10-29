<?php
/**
 * @include custom access permission
 */
namespace Drupal\srl_common\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;


class LandingApplicationFormRouteAccessPermission implements AccessInterface {
   public function access(AccountInterface $account) {
      $domain = \Drupal::service('domain.negotiator')->getActiveId();
      if($domain == 'srl_qc_niit_mts_com'){
       return AccessResult::allowedIf(1 == 1);
    }else{
       return AccessResult::allowedIf(1 == 2);
    }
   }
}