<?php
/**custom access permission */
namespace Drupal\srl_common\Access;

use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;


/**@implemetn custom access check 
 * Access permission with landing university 
 * */

class LandingRouteAccessPermission implements AccessInterface {
    
    public function access(AccountInterface $account) {
        $domain = \Drupal::service('domain.negotiator')->getActiveId();
        $university_config = \Drupal::config('university_info.config');
        $domain_type = $university_config->get($domain.'_domain_type');
        if($domain_type == 'domain_microsite'){
            return AccessResult::allowedIf(1 == 2);
        }else{
            return AccessResult::allowedIf(1 == 1);
        }
    }
}
