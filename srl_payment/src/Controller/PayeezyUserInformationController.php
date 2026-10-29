<?php

namespace Drupal\srl_payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;

/**
 * PayeezyUserInformationController controller class.
 */
class PayeezyUserInformationController extends ControllerBase {

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
  public function __construct(FormBuilder $formBuilder)
  {
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
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('form_builder')
    );
  }

  public function userinformation()  {

    $query = db_select('payeezy_payment_details','records');
    $query->fields('records',array('id','transaction_id','user_name', 'email_id','mobile_no','course_code','university',
    'created_on','course_name','delivery_mode','currency_code','base_amount','tax_amount','total_amount','cohort_date','zip_code',
    'fp_transaction_id','transaction_initiated_on','status','response_code','response_data','transaction_completed_on'));
    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $field_values = $pager->execute()->fetchAll();
    
    // Create table header.
    $header = [
      'id' => $this->t('ID'),
      'transaction_id' => $this->t('Transaction ID'),
		  'user_name' => $this->t('User Name'),
		  'email_id' => $this->t('Email'),
	    'mobile_no' => $this->t('Mobile'),
      'course_code'=> $this->t('Course'),
      'university'=> $this->t('University'),
      'created_on'=> $this->t('Created On'),	
      'course_name'=> $this->t('Course Name'),	  
      'delivery_mode'=> $this->t('Delivery Mode'),	
      'currency_code'=> $this->t('Currency Code'),  
	    'base_amount' =>$this->t('Base Amount'),
      'tax_amount'=> $this->t('Tax Amount'),	
      'total_amount'=> $this->t('Total Amount'),  
	    'cohort_date' =>$this->t('Cohort Date'),
      'zip_code'=> $this->t('Zip'),	
      'fp_transaction_id'=> $this->t('FP Transaction ID'),  
	    'transaction_initiated_on' =>$this->t('Transaction Initiated'),
      'status'=> $this->t('Status'),	
      'response_code'=> $this->t('Response Code'), 
      'transaction_completed_on' =>$this->t('Transaction Completed On'), 
	    'response_data' =>$this->t('Response Data')
    ];

    $rows = array();
    foreach($field_values as $field_details) {
      $row = array();
      $row[] = array(
        'data' => $field_details->id,
        'class' => 'id'
      );
      $row[] = array(
        'data' => $field_details->transaction_id,
        'class' => 'transaction_id'
      );
      $row[] = array(
        'data' => $field_details->user_name,
        'class' => 'user_name'
      );
      $row[] = array(
        'data' => $field_details->email_id,
        'class' => 'email_id'
      );
      $row[] = array(
        'data' => $field_details->mobile_no,
        'class' => 'mobile_no'
      );
      $node_storage = \Drupal::entityTypeManager()->getStorage('node');
      $node_details = $node_storage->load($field_details->course_code);
      $course_name = $node_details->getTitle();

      $row[] = array(
        'data' => $course_name,
        'class' => 'course_code'
      );
      $university = \Drupal\taxonomy\Entity\Term::load($field_details->university)->get('name')->value;
      $row[] = array(
        'data' => $university,
        'class' => 'email_id'
      );
      $row[] = array(
        'data' => $field_details->created_on,
        'class' => 'created_on'
      );
      $row[] = array(
        'data' => $field_details->course_name,
        'class' => 'course_name'
      );
      $row[] = array(
        'data' => $field_details->delivery_mode,
        'class' => 'delivery_mode'
      );
      $row[] = array(
        'data' => $field_details->currency_code,
        'class' => 'currency_code'
      );
      $row[] = array(
        'data' => $field_details->base_amount,
        'class' => 'base_amount'
      );
      $row[] = array(
        'data' => $field_details->tax_amount,
        'class' => 'tax_amount'
      );
      $row[] = array(
        'data' => $field_details->total_amount,
        'class' => 'total_amount'
      );
      $row[] = array(
        'data' => $field_details->cohort_date,
        'class' => 'cohort_date'
      );
      $row[] = array(
        'data' => $field_details->zip_code,
        'class' => 'zip_code'
      );
      $row[] = array(
        'data' => $field_details->fp_transaction_id,
        'class' => 'fp_transaction_id'
      );
      $row[] = array(
        'data' => $field_details->transaction_initiated_on,
        'class' => 'transaction_initiated_on'
      );
      $row[] = array(
        'data' => $field_details->status,
        'class' => 'status'
      );
      $row[] = array(
        'data' => $field_details->response_code,
        'class' => 'response_code'
      );
      $row[] = array(
        'data' => $field_details->transaction_completed_on,
        'class' => 'transaction_completed_on'
      );
      $row[] = array(
        'data' => $field_details->response_data,
        'class' => 'response_data'
      );

      $rows[] = array(
        'data' => (array) $row,
        'class' => 'data_row'
      );
    }
	
    $form['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No records found'),
    ];

    $form['pager'] = [
      '#type' => 'pager'
    ];
    return $form;
  }

}
