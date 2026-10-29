<?php
namespace Drupal\stackroute_modification\Plugin\views\access;

use Drupal\views\Plugin\views\access\AccessPluginBase;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Class ViewsCustomAccess
 *
 * @ingroup views_access_plugins
 *
 * @ViewsAccess(
 *     id = "ViewsCustomAccess",
 *     title = @Translation("Customised access for views"),
 *     help = @Translation("Add custom logic to access() method"),
 * )
 */
class ViewsCustomAccess extends AccessPluginBase{
   /**
    * {@inheritdoc}
    */
   public function summaryTitle()
   {
      return $this->t('Customised Settings');
   }


   /**
    * {@inheritdoc}
    */
   public function access(AccountInterface $account){
       $roles = $account->getRoles($exclude_locked_roles = false);
      
      if(in_array('marketing_management',$roles)){
         return TRUE;
      }else{
         return FALSE;
      }

   }
   /**
    * {@inheritdoc}
    */
   public function alterRouteDefinition(Route $route){ 
   $route->setRequirement('_custom_access', 'stackroute_modification.custom_access_check::access');

   //   if($domain == 'srl_qc_niit_mts_com' && in_array('marketing_management',$user->roles)){
   //       $route->setRequirement('_access', 'TRUE');
   //   }else{
   //       $route->setRequirement('_access', 'FALSE');
   //   }
   }
}
?>