<?php
/**
 * @include custom access permission
 */
namespace Drupal\srl_common\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;


class PageAccessPermission implements AccessInterface {
   public function access(AccountInterface $account) {
      $domain = \Drupal::service('domain.negotiator')->getActiveId();
      $access_domain = [
         'srl_pointpark_qc_niit_mts_com' => 'access_false',
         'stackroute_tamut_edu' => 'access_false',
         'srl_qc_niit_mts_com' => 'access_false',
         'point_park_university' => 'access_false',
         'california_state_university' => 'access_false',
         'fresno_pacific_university' => 'access_false',
         'srl_lindenwood_qc_niit_mts_com' => 'access_false',
         'stackroute_suffolk_university' => 'access_false',
         'clemson_edu' => 'access_false',
         'srl_bradley_qc_niit_mts_com' => 'access_false',
         'srl_tamiu_edu' => 'access_false',
         'srl_lsu_qc_niit_mts_com' => 'access_false',
         'schreiner_university' => 'access_false',
         'utep_edu' => 'access_false',
         'usca_university' => 'access_false',
      ];
      if(array_key_exists($domain,$access_domain)){
         return AccessResult::allowedIf(1 == 2);
      }else{
         return AccessResult::allowedIf(1 == 1);
      }
   }
}