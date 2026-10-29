<?php

namespace Drupal\srl_payment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormInterface;

/**
 * Displays a Payeezy payment form block.
 *
 * @Block(
 *   id = "payeezy_payment_block",
 *   admin_label = @Translation("Payeezy Payment Form"),
 * )
 */
class PayeezyPaymentBlock extends BlockBase {

  /**
   * Disables the cache for this block.
   */
  public function getCacheMaxAge() {
    return 0;
  }

  /**
   * Returns the Payeezy Form Block.
   */
  public function build() {

    $form = \Drupal::formBuilder()->getForm('Drupal\srl_payment\Form\PayeezyPaymentForm');

    return $form;
  }
}
