<?php
/**custom access permission */
namespace Drupal\stackroute_modification\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Database\Database;

/**@implemetn custom access check */

class ServicesAccessCheck implements AccessInterface {
    
    public function access(AccountInterface $account) {
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $user = \Drupal::currentUser();
        $roles = $user->getRoles($exclude_locked_roles = false);
        //srl_qc_niit_mts_com 
        // if($domain == 'srl_stackroute_learning' && in_array('marketing_management',$roles)){
        if($domain == 'srl_stackroute_learning' && in_array('marketing_management',$roles)){
            return AccessResult::allowedIf(1 == 1);
        }else{
            return AccessResult::allowedIf(1 == 2);
        }
    }
}
