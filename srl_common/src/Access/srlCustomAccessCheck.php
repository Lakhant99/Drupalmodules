<?php
/**custom access permission */
namespace Drupal\srl_common\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Database;

/**@implemetn custom access check */

class srlCustomAccessCheck implements AccessInterface {
    
    public function access(AccountInterface $account) {
        $session = \Drupal::request()->getSession();
        $transaction_id = $session->get('transaction_id');
        $permission = checkAccessRecord($transaction_id);
        if($permission){
            return AccessResult::allowedIf(1 == 1);
        }else{
            return AccessResult::allowedIf(1 == 2);
        }
    }
}
