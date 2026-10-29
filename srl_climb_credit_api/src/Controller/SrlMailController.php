<?php

namespace Drupal\srl_climb_credit_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
/**
 * An example controller.
 */
class SrlMailController extends ControllerBase {

  /**
   * Returns a render-able array for a test page.
   */
  public function content() {
    $build['content'] = [
        '#type' => 'item',
        '#markup' => $this->t('It works!'),
      ];
  
    //  
    $mailManager = \Drupal::service('plugin.manager.mail');
    $module = 'srl_climb_credit_api';
    $key = 'test_mail'; // Replace with Your key
    $to = 'darshankariyask@gmail.com';
    $params['message'] = 'this is Srl mail';
    $params['title'] = 'my drupal mail';
    $langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $send = true;
  
    $result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);
    if ($result['result'] != true) {
      $message = t('There was a problem sending your email notification to @email.', array('@email' => $to));
      // drupal_set_message($message, 'error');
      \Drupal::messenger()->addMessage($message, 'error');
      \Drupal::logger('mail-log')->error($message);
      return;
    }
    return $build;

  }
}