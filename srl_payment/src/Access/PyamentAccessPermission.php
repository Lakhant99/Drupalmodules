<?php
/**
 * @include custom access permission
 */
namespace Drupal\srl_payment\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Database;


class PyamentAccessPermission implements AccessInterface {
   public function access(AccountInterface $account) {
      $rout_access = FALSE;
      $session = \Drupal::request()->getSession();
      $transaction_id = $session->get('transaction_id');
      
      if(isset($transaction_id)){
         $records = getAssessmentUserRecord($transaction_id);
         if($records->actual_ended_on != NULL || $records->result == 'skiped'){
             if($records->result == 'passed' || $records->result == 'skiped'){
               $rout_access = TRUE;
             }
         }
      }

      if($rout_access){
         return AccessResult::allowedIf(1 == 1);
      }else{
         return AccessResult::allowedIf(1 == 2);
      }
   }
}