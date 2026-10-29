<?php

namespace Drupal\srl_payment\Controller;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Render\FormattableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormBuilder;

/**
 * PayeezyPaymentCallbackController controller class.
 */
class PayeezyPaymentCallbackController extends ControllerBase {
  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }


  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * HcpLoginController page.
   */
  public function payeezypaymentcallback($page = NULL, $id = NULL) {

    global $base_url;
		$module_path = \Drupal::service('module_handler')->getModule('srl_payment')->getPath();
		$succes_img = $base_url.'/'.$module_path.'/css/images/check-mark.png';
    $fail_img = $base_url.'/'.$module_path.'/css/images/alert.png';

    $university_site = $phone_number = '';

    $thankyou = '';
    if($_REQUEST['x_response_reason_code'] == 1){
      $query = db_select('payeezy_payment_details','records');
      $query->fields('records',array('transaction_id','university','course_name','course_code','currency_code'));
      $query->condition('records.transaction_id',$_REQUEST['x_cust_id']);
      $field_values = $query->execute()->fetchAll();
  
      $university = $field_values['0']->university;
      $course_name = $field_values['0']->course_name;
  
      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($university);
      $university_name = $term->name->value;
      $university_country = $term->field_country_code->value;
      $university_site = $term->field_domain->value;
      $phone_number = $term->field_phone_number->value;


      $connection = \Drupal::service('database');
      $query = $connection->update('payeezy_payment_details')
        ->fields([
          'fp_transaction_id' => $_REQUEST['x_trans_id'],
          'transaction_initiated_on' => $_REQUEST['x_fp_timestamp'],
          'status' => $_REQUEST['Transaction_Approved'],
          'response_code' => $_REQUEST['x_response_reason_code'],
          'response_data' => json_encode($_REQUEST),
          'transaction_completed_on' => \Drupal::time()->getRequestTime()
        ])
        ->condition('transaction_id', $_REQUEST['x_cust_id'], '=')
        ->execute();

      $thankyou .= '<div class="container wrapper">
      <div class="container wrapper" id="">
      <div class="media message-card">
        <div class="media-left">
          <img src="'.$succes_img.'" class="media-object">
        </div>
        <div class="media-body">
          <h4 class="media-heading">Payment Successfull!</h4>
          <p>Your payment has been successfully completed.</p>
        </div>
      </div>
      <div class="row">
      <div class="col-sm-12">
        <div class="panel panel-default">
          <div class="panel-heading">Transaction Details</div>
          <div class="panel-body">
            <div class="row">
              <div class="col-sm-6">
                <label class="transaction-info_name"><i class="la la-"></i>Transaction ID</label>
                <label class="transaction-info_value">'.$_REQUEST['x_trans_id'].'</label>
              </div>
              <div class="col-sm-6">
                <label class="transaction-info_name">Transaction Amount</label>
                <label class="transaction-info_value">'.$field_values['0']->currency_code.' '.$_REQUEST['x_amount'].'</label>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-6">
        <div class="panel panel-default">
          <div class="panel-heading">Contact Information</div>
          <div class="panel-body">
            <p>
              <label class="contact-info_name"><i class="la la-building"></i>Preferred Location</label>
              <label class="contact-info_value">'.$university_name.'</label>
            </p>
            <p>
              <label class="contact-info_name"><i class="la la-map-marker"></i>Address</label>
              <label class="contact-info_value">'.$university_country.'</label>
            </p>
            <p>
              <label class="contact-info_name"><i class="la la-phone"></i>Website</label>
              <label class="contact-info_value">'.$university_site.'</label>
            </p>
            <p>
            <label class="contact-info_name"><i class="la la-phone"></i>Phone Number</label>
            <label class="contact-info_value">'.$phone_number.'</label>
          </p>
          </div>
        </div>
      </div>
      <div class="col-sm-6">
        <div class="panel panel-default min-ht-panel">
          <div class="panel-heading">Enrolled Course Detail</div>
          <div class="panel-body">
            <p>
              <label class="contact-info_name">Course Name</label>
              <label class="font-light">'.$course_name.'</label>
            </p>
          </div>
        </div>
      </div>
      </div>
      <div class="back-button"><a href="/node/'.$field_values['0']->course_code.'" class="alpha-link alpha-uppercase orange-link">Back to '.$course_name.'</a></div>
    </div>';
    }else{
      $connection = \Drupal::service('database');
      $query = $connection->update('payeezy_payment_details')
        ->fields([
          'fp_transaction_id' => $_REQUEST['x_trans_id'],
          'transaction_initiated_on' => $_REQUEST['x_fp_timestamp'],
          'status' => $_REQUEST['Transaction_Approved'],
          'response_data' => json_encode($_REQUEST),
          'response_code' => $_REQUEST['x_response_reason_code'],
          'transaction_completed_on' => \Drupal::time()->getRequestTime()
        ])
        ->condition('transaction_id', $_REQUEST['x_cust_id'], '=')
        ->execute();
      $thankyou .= '<div class="container wrapper">
        <div class="container wrapper" id="">
        <div class="media message-card">
          <div class="media-left">
            <img src="'.$fail_img.'" class="media-object">
          </div>
          <div class="media-body">
            <h4 class="media-heading">Payment Failed!</h4>
            <p>In case your money has already debited from your account, It will credit to your bank account within 5-7 business days.</p>
          </div>
        </div>
      </div>
    </div>';
   }

		$output = [
		  '#type' => 'inline_template',
		  '#template' => $thankyou,
		  '#cache' => ['max-age' => 0],
      '#attached' => [
        'library' => [
          'srl_payment/srl_payment',
        ],
      ],
		];

    return $output;
  }

}
