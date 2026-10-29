<?php

namespace Drupal\srl_payment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Render\Markup;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\DataCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\taxonomy\Entity\Term;
use Drupal\Node\Entity\Node;
use Symfony\Component\HttpFoundation;
/**
 * Show textfields based on Payeezy payment.
 *
 * @ingroup srl_payment
 */
class PayeezyPaymentForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'srl_payeezy_payment_form';
  }

  /**
   * {@inheritdoc}
   *
   * For all the requests this class gets, the buildForm() method will always be
   * called. If an AJAX request comes in, the form state will be set to the
   * state the user changed that caused the AJAX request. So if the user enabled
   * one of our checkboxes, it will be checked in $form_state.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $fee = NULL, $currency_code = NULL,$transaction_id = NULL, $course_id = NULL, $course_fee=NULL, $batch_start_date = NULL,$course_code = NULL, $delivery_modal = NULL) {

    $domain = \Drupal::service('domain.negotiator')->getActiveId();
    $university_id = NULL;
    $results = \Drupal::entityQuery('node')
      ->condition('type', 'course_university_mapping')
      ->condition('field_domain_access.target_id', '%'.$domain.'%', 'LIKE')
      ->condition('field_course_code_reference.target_id',$course_id,'=')
      ->range(0, 1)
      ->execute();
      foreach($results as $value){
        $university_id = $value;
      }
      $session = \Drupal::request()->getSession();
      $transaction_id = $session->get('transaction_id');
    $assessment_info = getAssessmentUserInfo($transaction_id);
    /**Get nrollment agreement info */

    $enrollment_info = get_EnrollmentAgreement_info($transaction_id);
    $term_ofuse = $enrollment_info['term_of_use'];
    $enrollfound = $enrollment_info['enrollment_agreement'];
    $enrollment_info = json_decode($enrollment_info['agreement_info']);
    // kint($enrollment_info);
    
    /**get payment configuration */
    $config = \Drupal::config('srl_payment.settings');
    
    $x_login = $config->get('x_login'); // Take from Payment Page ID in Payment Pages interface
    $transaction_key = $config->get('payeezy_transaction_key'); // Take from Payment Pages configuration interface
    $x_amount = $course_fee;
    (\Drupal::time()->getRequestTime()); // initialize random generator for x_fp_sequence
    $x_fp_sequence = rand(1000, 100000) + 123456;
    $x_fp_timestamp = \Drupal::time()->getRequestTime(); 
    $hmac_data = $x_login . "^" . $x_fp_sequence . "^" . $x_fp_timestamp . "^" . $x_amount . "^" . $currency_code;
    $x_fp_hash = hash_hmac('MD5', $hmac_data, $transaction_key);

    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $node_details = $node_storage->load($course_id);
    $path = '/node/' . (int) $course_id;
    $path_alias = \Drupal::service('path.alias_manager')->getAliasByPath($path);
    if(!empty($node_details->get('field_external_link')->getValue()[0]['uri'])){
        $path_alias = $node_details->get('field_external_link')->getValue()[0]['uri'];
    }

    $title_term_header = '';
    
    $university_policy = getUniversityPolicy(); 
    if($university_policy['term_of_use']){
      $policy = Term::load($university_policy['term_of_use']);
      $title_term_header = 'Terms of Use';//$policy->getName();
      $term_of_use = $policy->getDescription();
      
    }
    if($university_policy['enrollment_agreement']){
      $agreement = Term::load($university_policy['enrollment_agreement']);
      $title_agreement_header = 'Enrollment Agreement';//$agreement->getName();
      $enrolment_agreement = $agreement->getDescription();
      $address = $agreement->get('field_university_address')->getValue()[0]['value'];
      
    }
    $form['title_term_header'] = array(
      '#type' => 'markup',
      '#markup' => $title_term_header,
    );
    $form['terms_condition'] = array(
      '#type' => 'markup',
      '#markup' => $term_of_use,
    );
    $form['term_accepted'] = [
      '#type' => 'hidden',
      '#default_value' => 'value-'.$term_ofuse,
    ];
    $form['term_of_use_markup'] = [
      '#type' => 'markup',
      '#markup' => '<div id="term-accepted" class="accepted-'.$term_ofuse.'"><p class="term-of-use ">
          <span class="msg-agreement">Please review and accept</span>
          <a class="popup-button" data-target="#myfirstmodal" data-toggle="modal"> Terms of Use</a>
          <span class="term-validate"><i class="fa fa-check-circle" aria-hidden="true"></i>
          <span>Accepted</span>
          </span>
      </p></div>',
    ];
    $form['agreement_link_markup'] = [
      '#type' => 'markup',
      '#markup' => '<div id="agr-accepted" class="accepted-'.$enrollfound.'"><p class="enrollment-agreement ">
        <span class="msg-agreement">Please review and accept</span>
        <a class="popup-button myModal_unset"> Enrollment Agreement</a>
        <span class="agreement-validate"><i class="fa fa-check-circle" aria-hidden="true"></i><span>Accepted</span></span>
    </p></div>',
    ];
    $form['terms_condition_accept'] = array(
      '#type' => 'submit',
      '#value' => 'ACCEPT',
      '#button_type' => 'primary',
      '#op' => 'accept tern of use',
      '#ajax' => array(
        'callback' => [$this,'acceptTermOFUse'],
        'event' => 'click',
        'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
        ),
      ),
      '#attributes' => ['class' => ['alpha-link', 'alpha-uppercase', 'orange-link', 'btn-default-2']]
    );
    $form['terms_condition_reject'] = array(
      '#type' => 'submit',
      '#value' => 'REJECT',
      '#button_type' => 'primary',
      '#op' => 'reject tern of use',
      '#ajax' => array(
        'callback' => [$this,'rejectTermOFUse'],
        'event' => 'click',
        'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
        ),
      ),
      '#attributes' => ['class' => ['alpha-link', 'alpha-uppercase', 'orange-link']]
    );
    $form['title_agreement_header'] = array(
      '#type' => 'markup',
      '#markup' => $title_agreement_header,
    );
    if(!empty($enrolment_agreement)){
      // agreement form field popup markup start
      $session = \Drupal::request()->getSession();
      $transaction_id = $session->get('transaction_id');

      
      $result = getUserinfo($transaction_id);
      $result = json_decode(stripslashes($result));
      if($result->naame){

        $student_name = $result->naame.' '.$result->last_name;
      }else{
        $student_name = $result->full_name;
      }

      $enrolment_agreement = str_replace('#COURSE_NAME#',$node_details->getTitle(),$enrolment_agreement);
      $enrolment_agreement = str_replace('#STUDENT_NAME#',$student_name,$enrolment_agreement);
      $enrolment_agreement = str_replace('#FEE#',number_format($fee, 0, '.', ',').' '.$currency_code,$enrolment_agreement);
      $enrolment_agreement = str_replace('#UNIVERSITY#',number_format($x_amount, 0, '.', ','),$enrolment_agreement);
      $end_date = date('M d, Y', strtotime("+5 months", strtotime($batch_start_date)));
      $start_date = date('M d, Y', strtotime($batch_start_date));
      $enrolment_agreement = str_replace('#START_DATE#',$start_date,$enrolment_agreement);
      $enrolment_agreement = str_replace('#END_DATE#',$end_date,$enrolment_agreement);
      
      /** enrollment agrement popup container */
        $form['enroll_agreement'] = array('#type' => 'container');
        $form['enroll_agreement']['markup_university_info'] = [
          '#type' => 'markup',
          '#markup' => $address.'<h4>1. APPLICANT INFORMATION</h4>'
        ];
        $form['enroll_agreement']['f_name'] = [
          '#type' => 'textfield',
          '#title' => 'Name<i>*</i>',
          '#default_value' => $student_name,
          '#attributes' => [
            'readonly' => 'readonly',
          ],
        ];
        if($enrollment_info->dob){
          $form['enroll_agreement']['dob'] = array(
            '#title' => 'D.O.B.:<i>*</i>',
            '#type' => 'date',
            '#date_format' => 'M d, Y',
            '#default_value' =>date("Y-m-d",$enrollment_info->dob),
          );
        }else{
          $form['enroll_agreement']['dob'] = array(
            '#title' => 'D.O.B.:<i>*</i>',
            '#type' => 'date',
            '#date_format' => 'M d, Y',
          );
        }
        $form['enroll_agreement']['address'] = [
            '#type' => 'textfield',
            '#title' => 'Address:<i>*</i>',
            '#default_value' => $enrollment_info->address,
        ];
        $form['enroll_agreement']['city'] = [
            '#type' => 'textfield',
            '#title' => 'City:<i>*</i>',
            '#default_value' => $enrollment_info->city,
        ];
        $form['enroll_agreement']['state'] = [
            '#type' => 'textfield',
            '#title' => 'State:<i>*</i>',
            '#default_value' => $enrollment_info->state,
        ];
        $form['enroll_agreement']['zip'] = [
            '#type' => 'textfield',
            '#title' => 'Zip:<i>*</i>',
            '#default_value' => $result->zip,
            '#attributes' => [
              'readonly' => 'readonly',
            ],
        ];
        // $str = explode('-',$mobile_no);
        // $mobile_no = (int) filter_var($str[0], FILTER_SANITIZE_NUMBER_INT).'-'.$str[1];
        $form['enroll_agreement']['phone'] = [
            '#type' => 'textfield',
            '#title' => 'Cell Phone:<i>*</i>',
            '#default_value' => '+'.(int) filter_var($result->country, FILTER_SANITIZE_NUMBER_INT).'-'.$result->phone,
            '#attributes' => [
              'readonly' => 'readonly',
            ],
        ];
        $form['enroll_agreement']['home_phone'] = [
            '#type' => 'textfield',
            '#title' => 'Home Phone:',
            '#default_value' => $enrollment_info->home_phone,
        ];
        $form['enroll_agreement']['email'] = [
            '#type' => 'textfield',
            '#title' => 'Main Email:',
            '#default_value' => $result->email,
            '#attributes' => [
              'readonly' => 'readonly',
            ],
        ];
        $form['enroll_agreement']['school_graduate'] = [
            '#type' => 'select',
            '#title' => 'High School Graduate:<i>*</i>',
            '#options' => ['select' => '--Select--','No' => "No", 'Yes' => 'Yes'],
            '#default_value' => $enrollment_info->school_graduate,
        ];
        $form['enroll_agreement']['ged'] = [
            '#type' => 'select',
            '#title' => 'Earned GED:<i>*</i>',
            '#options' => ['select' => '--Select--','No' => "No", 'Yes' => 'Yes'],
            '#default_value' => $enrollment_info->ged,
        ];
        $year = date('Y');
        $year_option[0] = '--select--';
        for($YYYY = 1990; $YYYY <= $year; $YYYY++){
          $year_option[$YYYY] = $YYYY;
        }
        $form['enroll_agreement']['year_complete'] = [
            '#title' => 'Year Completed:<i>*</i>',
            '#type' => 'select',
            '#options' => $year_option,
            '#default_value' => $enrollment_info->year_complete,
        ];
        $form['enroll_agreement']['program_track'] = [
            '#type' => 'textfield',
            '#title' => 'Program Track:<i>*</i>',
            '#default_value' => $node_details->getTitle(),
            '#attributes' => [
              'readonly' => 'readonly',
            ],
        ];
        $form['enroll_agreement']['status'] = [
            '#type' => 'textfield',
            '#title' => 'Status:<i>*</i>',
            '#default_value' => 'Part Time',
            '#attributes' => [
              'readonly' => 'readonly',
            ],
            
        ];
        $form['enroll_agreement']['non_degree'] = [
            '#type' => 'textfield',
            '#title' => 'Instructional Hours:<i>*</i>',
            '#default_value' => 240,
            '#attributes' => [
              'readonly' => 'readonly',
            ],
        ];
        $form['enroll_agreement']['program_start_date'] = [
            '#type' => 'textfield',
            '#title' => 'Program Start Date:<i>*</i>',
            '#default_value' => $start_date,
            '#attributes' => [
              'readonly' => 'readonly',
            ],
        ];
        $form['enroll_agreement']['expected_graduation_date'] = [
            '#type' => 'textfield',
            '#default_value' => $end_date,
            '#title' => 'Expected Graduation Date:<i>*</i>',
            '#attributes' => [
              'readonly' => 'readonly',
            ],
        ];
        /** Course enrollment agreement */
        $form['enroll_agreement']['agr_info'] = array(
          '#type' => 'markup',
          '#markup' => $enrolment_agreement, 
        );
        /** student sgined confimation */
        $form['enroll_agreement']['markup_s_info'] = [
          '#type' => 'markup',
          '#markup' => '
          <h4>Electronically or ink signed by:</h4>
          <h5>STUDENT:</h5>'
        ];
        $form['enroll_agreement']['student_sign'] = [
          '#type' => 'textfield',
          '#title' => 'Student Signature:<i>*</i>',
          '#default_value' => $enrollment_info->student_sign ? $enrollment_info->student_sign : $student_name,
        ];
        $form['enroll_agreement']['student_name'] = [
          '#type' => 'textfield',
          '#title' => 'Student Name Printed:',
          '#default_value' => $student_name,
          '#attributes' => [
            'readonly' => 'readonly',
          ],
        ];
        $form['enroll_agreement']['student_agreement_date'] = [
          '#type' => 'textfield',
          '#title' => 'Date:',
          '#default_value' => $enrollment_info->acceptance_date ? $enrollment_info->acceptance_date : date('M d, Y'),
          '#attributes' => [
            'readonly' => 'readonly',
          ],
        ];
        $form['enroll_agreement']['validate_agreement'] = [
          '#type' => 'submit',
          '#value' => 'accept',
          '#ajax' => array(
            'callback' => [$this,'customAjaxValidation'],
            'event' => 'click',
            'progress' => array(
                'type' => 'throbber',
                'message' => 'Success',
            ),
          ),
          '#attributes' => [
            'class' => ['alpha-link', 'alpha-uppercase', 'orange-link'],
            ]
        ];
        $form['enroll_agreement']['reject_agreement'] = [
          '#type' => 'submit',
          '#value' => 'reject',
          '#ajax' => array(
            'callback' => [$this,'rejectEnrollmentAgreement'],
            'event' => 'click',
            'progress' => array(
                'type' => 'throbber',
                'message' => 'Success',
            ),
          ),
          '#attributes' => [
            'class' => ['alpha-link', 'alpha-uppercase', 'orange-link'],
            ]
        ];
    }
    $form['x_login'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Login'),
      '#default_value' => $x_login,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_amount'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Amount'),
      '#default_value' => $x_amount,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];
    $form['x_amount_total'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Amount'),
      '#default_value' => $fee,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_fp_sequence'] = [
      '#type' => 'hidden',
      '#title' => $this->t('sequence'),
      '#default_value' => $x_fp_sequence,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_fp_timestamp'] = [
      '#type' => 'hidden',
      '#title' => $this->t('timestamp'),
      '#default_value' => $x_fp_timestamp,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_fp_hash'] = [
      '#type' => 'hidden',
      '#title' => $this->t('hash'),
      '#default_value' => $x_fp_hash,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_currency_code'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Currency Code'),
      '#default_value' => $currency_code,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_show_form'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Show Form'),
      '#default_value' => 'PAYMENT_FORM',
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['enable_level3_processing'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Enable Level'),
      '#default_value' => 'TRUE',
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_cust_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Cust ID'),
      '#default_value' => $transaction_id,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];

    $form['x_email'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Email'),
      '#default_value' => $assessment_info->email,
      '#required' => TRUE,
      '#maxlength' => 250,
    ];
    $form['course_name'] = [
      '#type' => 'hidden',
      '#default_value' => $node_details->getTitle(),
      '#maxlength' => 500,
    ];
    $form['course_id'] = [
      '#type' => 'hidden',
      '#default_value' => $course_id,
    ];
    $form['firstname'] = [
      '#type' => 'hidden',
      '#default_value' => $assessment_info->naame,
      '#maxlength' => 50,
    ];
    $form['mobilephone'] = [
      '#type' => 'hidden',
      '#default_value' => $assessment_info->phone,
      '#maxlength' => 50,
    ];
    $form['university_id'] = [
      '#type' => 'hidden',
      '#default_value' => $university_id,
    ];
    $form['start_date'] = [
      '#type' => 'hidden',
      '#title' => '',
      '#default_value' => $batch_start_date,
      
    ];
    $form['term_of_use'] = [
      '#type' => 'hidden',
      '#title' => '',
      '#default_value' => 'N',
      
    ];
    $form['enrollment_agreement'] = [
      '#type' => 'hidden',
      '#title' => '',
      '#default_value' => 'N',
    ];
    /** submit confirmation credit card */
    $form['submit_card'] = array(
      '#type' => 'submit',
      '#value' => $this->t('PAY NOW'),
      '#button_type' => 'primary',
      '#op' => 'Pay via Climb Credit Payment Options',
      '#ajax' => array(
        // 'callback' => [$this,'createHubspotTask'],
        'callback' => [$this,'callbackPaymentClick'],
        'event' => 'click',
        'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
        ),
      ),
      '#attributes' => [
        'class' => array('class' => 'alpha-link alpha-uppercase orange-link card-button'),
      ],
    );
    $link_array = Node::load($university_id);
    if($link_array->field_payment){
      $link_list = $link_array->get('field_payment')->getValue();
    }
    if($link_list){
      $options['SFO'] = 'EXPLORE LOAN OPTIONS';
      foreach($link_list as $values){
        $options[$values['title']] = $values['title'];
      }
      $form['finance_options'] = [
        HASH_TYPE => 'select',
        HASH_TITLE => 'Finance Options:', 
        HASH_OPTIONS => $options,
        '#ajax' => array(
          // 'callback' => [$this,'createHubspotTask'], 
          'callback' => [$this,'callbackPaymentClick'],
          'event' => 'change',
          'progress' => array(
              'type' => 'throbber',
              'message' => 'Success', 
          ),
        ),
      ];
      $form['finance_discount'] = [
        '#type' => 'markup',
        '#markup' => '<div class="discount-note">
          <i>Checking your loan options online will not impact your credit score. Lenders only perform a “soft” credit pull to evaluate your eligibility. A “hard” credit pull is only performed once the loan is accepted and funded.</i>
        </div> ',
      ];
    }
    $form['submit_plan'] = array(
      '#type' => 'submit',
      '#value' => $this->t('ELMSelect Payment Options'),
      '#op' => 'Pay Via ELMSelect Payment Options',
      '#ajax' => array(
        'callback' => [$this,'createHubspotTask'],
        'event' => 'click',
        'progress' => array(
            'type' => 'throbber',
            'message' => 'Success',
        ),
      ),
      '#attributes' => [
        'class' => array('class' => 'alpha-link alpha-uppercase orange-link'),
      ],
    );
    $form['plan_markup'] = array(
      '#type' => 'markup',
      '#markup' => '<a class="install-redirect" href="'.$path_alias.'"></a>
      <div id="planModalbox" class="modal fade" role="dialog" data-url="'.$path_alias.'">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <span type="button" class="close" data-dismiss="modal">&times;</span>
            </div>
            <div class="modal-body">
              <p>Thank you for your purchase, one of our Professional Academics Consultants will be contacting you to complete your order. We are excited to have you join our program!</p>
            </div>
            <div class="modal-footer"></div>
          </div>
        </div>
      </div>
      
      ',
    );
  
    $form['#theme'] = 'term_agreement_popup_form';
    /** DMS required field */
    /** DMS API 1
     * @clientID (taxonomy univerisity) taxonomy university
     * @coursecode (our programs) content type
     */
    $DMS_Config = \Drupal::config('university_dmsapi.config');
    $DMS_API = \Drupal::service('srl_payment.dms');
    $api_values['clientID'] = $university_policy['clientID'];
    $api_values['coursecode'] = $course_code;
    $DMS_FEE = $DMS_API->DMS_GetBatchFeeDetails($api_values, $DMS_Config);

    $form['OrgID'] = ['#type' => 'hidden','#default_value' => 3];
    $form['coursecode'] = ['#type' => 'hidden','#default_value' => $course_code];
    $form['clientID'] = ['#type' => 'hidden','#default_value' => $university_policy['clientID']];
    $form['BatchID'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->batchDetailsSummaryModelMT[0]->BATCHID];
    $form['CountryCode'] = ['#type' => 'hidden','#default_value' => '+'.(int) filter_var($result->country, FILTER_SANITIZE_NUMBER_INT)];
    $form['MobileNo'] = ['#type' => 'hidden','#default_value' => $result->phone];

    $form['PaymentMode1'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[0]->DISPLAYINFORMATION];
    $form['FeePatternCode1'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[0]->FEEPATTERNCODE];
    $form['Currency1'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[0]->CURRENCYCODE];
    $form['FeeAmount1'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[0]->BATCHFEES];

    $form['PaymentMode2'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[1]->DISPLAYINFORMATION];
    $form['FeePatternCode2'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[1]->FEEPATTERNCODE];
    $form['Currency2'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[1]->CURRENCYCODE];
    $form['FeeAmount2'] = ['#type' => 'hidden','#default_value' => $DMS_FEE->courseDetailsSummaryModelMT[1]->BATCHFEES];

    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, $fee = NULL, $currency_code = NULL, $transaction_id = NULL, $course_id = NULL) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state, $fee = NULL, $currency_code = NULL, $transaction_id = NULL, $course_id = NULL) {
    // $form_field_values = $form_state->getValues();

          // $DMS_API = \Drupal::service('srl_payment.dms');
          // $api_values = [];
          // $api_values['OrgID'] = $form_field_values['OrgID'];
          // $api_values['coursecode'] = $form_field_values['coursecode'];
          // $api_values['clientID'] = $form_field_values['clientID'];
          // $api_values['BatchID'] = $form_field_values['BatchID'];
          // $api_values['CountryCode'] = $form_field_values['CountryCode'];
          // $api_values['MobileNo'] = $form_field_values['MobileNo'];
          // $api_values['EmailID'] = $form_field_values['x_email'];
          // $date =  \Drupal::time()->getRequestTime();
          // $api_values['DateTime'] = date('Y-m-d',$date);
          // $api_values['Fee'] = [
          //   array(
          //     'PaymentMode' => 'LOAN',
          //     'FeePatternCode' => $form_field_values['FeePatternCode'],
          //     'Currency' => $form_field_values['x_currency_code'],
          //     'FeeAmount' => $form_field_values['x_amount_total'],
          //   ),
          //   array(
          //     'PaymentMode' => 'LOAN',
          //     'FeePatternCode' => $form_field_values['FeePatternCode'],
          //     'Currency' => $form_field_values['x_currency_code'],
          //     'FeeAmount' => $form_field_values['x_amount_total'],
          //   )
          // ];

          // $DMS_FEE = $DMS_API->DMS_InsertLearnerDetail($api_values);
          // kint($DMS_FEE);
  }

  
  public function createHubspotTask(array &$form, FormStateInterface $form_state){

    $session = \Drupal::request()->getSession();
    $transaction_id = $session->get('transaction_id');
    
    $ajax_response = new AjaxResponse();
    
    $ajax_response->addCommand(new RemoveCommand('.unplanErrorClass'));
    $ajax_response->addCommand(new InvokeCommand('#edit-finance-options','removeClass',['error']));

    $flag_submit = FALSE;

    $leadData['course_name'] = $form_state->getValue('course_name');
    $leadData['email'] = $form_state->getValue('x_email');
    $leadData['name'] = $form_state->getValue('firstname');
    
    
    $term_use = $form_state->getValue('term_of_use');
    $enrollment_agreement = $form_state->getValue('enrollment_agreement');
    $acceptance_info['term_of_use'] = 0;
    $acceptance_info['enrollment_agreement'] = 0;
    $acceptance_info['enrollment_agreement_pdf'] = '';
    $acceptance_info['term_of_use_pdf'] = '';
    

    if($term_use == 'Y' && $enrollment_agreement == 'Y'){
      $flag_submit = TRUE;
    }else{
      $form_values = $form_state->getValues();
      $HubspotAPI = \Drupal::service('pdf.agreement');
      $acceptance_info['transaction_id'] = $form_values['x_cust_id']; 
      if($term_use == 'Y'){
        //generateTermofUsePdf
        $acceptance_info['flag'] = 'term_of_use';
        $term_of_use_pdf = $HubspotAPI->generateTermofUsePdf($form_values);
        $acceptance_info['term_of_use_pdf'] = $term_of_use_pdf;
        $acceptance_info['term_of_use'] = 1;
        UpdateAcceptanceInfo($acceptance_info);
      }elseif($enrollment_agreement == 'Y'){
        // callback on enrollment accepted
        // genrate agreement pdf
        $acceptance_info['flag'] = 'enrollment_agreement';
        $acceptance_info['enrollment_agreement'] = 1;
        $agreement_pdf = $HubspotAPI->generatePdf($form_values);
        $acceptance_info['enrollment_agreement_pdf'] = $agreement_pdf;

        UpdateAcceptanceInfo($acceptance_info);
      }
      // $ajax_response->addCommand(new InvokeCommand(NULL, 'agreementValidation', $leadData));
      if($form_state->getValue('university_id')){
        // pay now click condition
        if($form_state->getTriggeringElement()['#op'] == 'Pay via Climb Credit Payment Options'){
          $payment_option = 'Paid via Climb Credit Payment Options';
          $university = \Drupal::entityTypeManager()->getStorage('node')->load($form_state->getValue('university_id'));
          $payment_link = $university->get('field_credit_payment_link')->getValue()[0]['value'];
          
        }else{
          $link_array = Node::load($form_state->getValue('university_id'));
          $link_list = $link_array->get('field_payment')->getValue();
          if($link_list){
            foreach($link_list as $values){
              if($form_state->getValue('finance_options') == $values['title']){
                $payment_link = $values['uri'];
              }
            }
          }
        }
        if($payment_link){
          $leadData['link']  = $payment_link;
          $leadData['window_name']  = \Drupal::time()->getRequestTime();
          $ajax_response->addCommand(new InvokeCommand(NULL, 'paymentredirect', [$leadData]));
        }else{
          if($form_state->getValue('finance_options') != 'SFO'){
            $ajax_response->addCommand(new InvokeCommand(NULL, 'createHubspotTaskPopup', [['redirect'],'fixed']));
          }
        }
      }

      $flag_submit = FALSE;
      return $ajax_response;
    }
    
    if($flag_submit){

      // genrate agreement pdf
      $form_values = $form_state->getValues();
      
      // $HubspotAPI = \Drupal::service('pdf.agreement');
      // $agreement_pdf = $HubspotAPI->generatePdf($form_values);
      // $leadData['pdf_url'] = $agreement_pdf;
      // //generateTermofUsePdf
      // $term_of_use_pdf = $HubspotAPI->generateTermofUsePdf($form_values);
      // $leadData['term_of_use_pdf'] = $term_of_use_pdf;
      
      // update term of use acceptance
      // $acceptance_info['flag'] = 'both';
      // $acceptance_info['term_of_use'] = 1;
      // $acceptance_info['term_of_use_pdf'] = $term_of_use_pdf;
      // $acceptance_info['enrollment_agreement'] = 1;
      // $acceptance_info['enrollment_agreement_pdf'] = $agreement_pdf;
      // $acceptance_info['transaction_id'] = $form_values['x_cust_id']; 

      // UpdateAcceptanceInfo($acceptance_info);

      if($form_state->getValue('university_id')){
        if($form_state->getTriggeringElement()['#op'] == 'Pay via Climb Credit Payment Options'){
          $payment_option = 'Paid via Climb Credit Payment Options';
          $university = \Drupal::entityTypeManager()->getStorage('node')->load($form_state->getValue('university_id'));
          $payment_link = $university->get('field_credit_payment_link')->getValue()[0]['value'];
          $leadData['hs_lead_status'] = 'Paid via Credit Card';
          // $HubspotAPI = \Drupal::service('api.hubspot');
          // $contactID = $HubspotAPI->updateHubspotPyamentStatus($leadData);
          
        }else{
          $leadData['hs_lead_status'] = 'Paid via Installment Plan';
          // $HubspotAPI = \Drupal::service('api.hubspot');
          // $contactID = $HubspotAPI->updateHubspotPyamentStatus($leadData);

          $payment_option = 'Paid Via ELMSelect Payment Options';
          $university = \Drupal::entityTypeManager()->getStorage('node')->load($form_state->getValue('university_id'));
          if($form_state->getValue('finance_options') == 'SFO'){
            $ajax_response->addCommand(new InvokeCommand('#edit-finance-options','addClass',['error']));
            // $ajax_response->addCommand(new AfterCommand('#edit-finance-options', '<div class="unplanErrorClass">Please select finance option</div>'));
            // $ajax_response->addCommand(new DataCommand('#edit-finance-options', 'SFO', ['SFO' => '--Select Finance Option--','CCL' => 'CLIMB CREDIT Loan Option','ECL' => 'EMRTISE CREDIT Loan Option']));
            return $ajax_response;

          }
          $link_array = Node::load($form_state->getValue('university_id'));
          $link_list = $link_array->get('field_payment')->getValue();
          if($link_list){
            foreach($link_list as $values){
              if($form_state->getValue('finance_options') == $values['title']){
                $payment_link = $values['uri'];
              }
            }

          }
        }
        
        $connection = \Drupal::service('database');
          $query = $connection->update('payeezy_payment_details')
            ->fields([
              'delivery_mode' => 'Online '.$payment_option,
              
            ])
            ->condition('transaction_id', $transaction_id, '=')
            ->execute();

          if($payment_link){
            // $session = \Drupal::request()->getSession();
            // $session->remove('transaction_id');
            // $ajax_response->addCommand(new \Drupal\Core\Ajax\RedirectCommand($payment_link));
            $leadData['link']  = $payment_link;
            $leadData['window_name']  = \Drupal::time()->getRequestTime();
            $ajax_response->addCommand(new InvokeCommand(NULL, 'paymentredirect', [$leadData]));
          }else{
            // $session = \Drupal::request()->getSession();
            // $session->remove('transaction_id');
            $ajax_response->addCommand(new InvokeCommand(NULL, 'createHubspotTaskPopup', [['redirect'],'fixed']));
          }
      }
      return $ajax_response;
    }
  }
  /** accept term of use */
  public function acceptTermOFUse(array &$form, FormStateInterface $form_state){
    // \Drupal::logger('accept term of use')->notice('echo '.print_r(['accepted'],TRUE));
    $ajax_response = new AjaxResponse();
    $session = \Drupal::request()->getSession();
    $transaction_id = $session->get('transaction_id');
    $form_values = $form_state->getValues();

    
    $GenratePDF = \Drupal::service('pdf.agreement');
    $policy_pdf = $GenratePDF->generateTermofUsePdf($form_values);
    $leadData['policy_pdf'] = $policy_pdf;
    $leadData['email'] = $form_state->getValue('x_email');
    $leadData['hubspot_key'] = 'term_of_use';
    
    $policy_data['policy_pdf'] = $policy_pdf;
    $policy_data['policy_status'] = 1;
    $policy_data['policy'] = 'termofuse';
    $policy_data['transaction_id'] = $form_values['x_cust_id'];
    $data = updatePolicyStatus($policy_data);
    
    // /**Update hubspot agreement pdf */
    $HubspotAPI = \Drupal::service('api.hubspot');
    $contactID = $HubspotAPI->updateHubspotPDFLinks($leadData);
    $ajax_response->addCommand(new InvokeCommand(NULL, 'accepttermofusejscallback', $policy_data));

    return $ajax_response;
  }
  /**reject term of use */
  public function rejectTermOFUse(array &$form, FormStateInterface $form_state){
    // \Drupal::logger('reject term of use')->notice('echo '.print_r(['accepted'],TRUE));
    $ajax_response = new AjaxResponse();
    $form_values = $form_state->getValues();

    $leadData['policy_pdf'] = 'rejected';
    $leadData['email'] = $form_state->getValue('x_email');
    $leadData['hubspot_key'] = 'term_of_use';

    $policy_data['policy_pdf'] = 'rejected';
    $policy_data['policy_status'] = 0;
    $policy_data['policy'] = 'termofuse';
    $policy_data['transaction_id'] = $form_values['x_cust_id'];
    $rest = updatePolicyStatus($policy_data);
    
    /**Update hubspot agreement pdf */
    $HubspotAPI = \Drupal::service('api.hubspot');
    $contactID = $HubspotAPI->updateHubspotPDFLinks($leadData);
    
    $ajax_response->addCommand(new InvokeCommand(NULL, 'rejecttermofusejsCallback', $policy_data));
    return $ajax_response;
  }
  /**accept enrollment agreement */
  public function customAjaxValidation(array &$form, FormStateInterface $form_state){
    // \Drupal::logger('accept agreement')->notice('echo '.print_r(['accepted'],TRUE));
    $ajax_response = new AjaxResponse();
    $ajax_response->addCommand(new RemoveCommand('.unplanErrorClass'));
    $ajax_response->addCommand(new InvokeCommand('#edit-dob','removeClass',['error']));
    $ajax_response->addCommand(new InvokeCommand('#edit-home-phone','removeClass',['error']));

    $ajax_response->addCommand(new InvokeCommand('#edit-address','removeClass',['error']));
    $ajax_response->addCommand(new InvokeCommand('#edit-city','removeClass',['error']));
    $ajax_response->addCommand(new InvokeCommand('#edit-state','removeClass',['error']));
    $ajax_response->addCommand(new InvokeCommand('#edit-school-graduate','removeClass',['error']));
    $ajax_response->addCommand(new InvokeCommand('#edit-ged','removeClass',['error']));
    $ajax_response->addCommand(new InvokeCommand('#edit-year-complete','removeClass',['error']));
    $ajax_response->addCommand(new InvokeCommand('#edit-edit-student-sign','removeClass',['error']));
    
    $validation_error = [];
    $current_date = getCurrentDate();
    $leadData['course_name'] = $form_state->getValue('course_name');
    $flag_submit = FALSE;

    // agreement accept validation
    $dob = $form_state->getValue('dob');
    $home_number = $form_state->getValue('home_phone');
    $values = $form_state->getValues();

    if(!$dob){
      $validation_error['dob'] = t('Please enter DOB.');
    }else{
      $count = strlen($dob);
      if($count == 10){
        $dob_timestab = strtotime($dob);
        if($dob_timestab > $current_date){
          $form_submit = FALSE;
          $validation_error['dob'] = t('DOB can\'t be greater then current date');
        }else{
          $form_submit = TRUE;
        }

      }else{
        $form_submit = FALSE;
        $validation_error['dob'] = t('Invalid date');
      }

    }
    if(empty($values['address'])){
      $form_submit = FALSE;
      $validation_error['address'] = t('Please enter address.');
    }
    if(empty($values['city'])){
      $form_submit = FALSE;
      $validation_error['city'] = t('Please enter city.');
    }else{
      $pattren = '/^[a-zA-Z\/\+\s]*$/';
      $valdate = regexValidation($pattren,$values['city']);
      if($valdate){
        $form_submit = TRUE;
      }else{
        $form_submit = FALSE;
        $validation_error['city'] = t('Invalid city name.');
      }
    }
    if(empty($values['state'])){
      $form_submit = FALSE;
      $validation_error['state'] = t('Please enter state.');
    }else{
      $pattren = '/^[a-zA-Z\/\+\s]*$/';
      $valdate = regexValidation($pattren,$values['state']);
      if($valdate){
        $form_submit = TRUE;
      }else{
        $form_submit = FALSE;
        $validation_error['state'] = t('Invalid state name.');
      }
    }
    if($home_number){
      $validate = phoneNumberValidation($home_number);
      if($validate){
        $form_submit = TRUE;
      }else{
        $form_submit = FALSE;
        $validation_error['home_phone'] = t('Please Enter Valid Contact No.');
      }
    }
    $require = FALSE;
    if($values['school_graduate'] == 'select'){
      $require = TRUE;
      $ajax_response->addCommand(new InvokeCommand('#edit-school-graduate','addClass',['error']));
      $validation_error['school_graduate'] = t('Please select high school graduate');
    }
    if($values['ged'] == 'select'){
      $require = TRUE;
      $ajax_response->addCommand(new InvokeCommand('#edit-ged','addClass',['error']));
      $validation_error['ged'] = t('Please select earned GED');
    }
    if($values['year_complete'] == 0){
      $require = TRUE;
      $ajax_response->addCommand(new InvokeCommand('#edit-year-complete','addClass',['error']));
      $validation_error['year_complete'] = t('Please select Year Completed');
    }
    if(empty($values['student_sign'])){
      $require = TRUE;
      $ajax_response->addCommand(new InvokeCommand('#edit-student-sign','addClass',['error']));
      $validation_error['student_sign'] = t('Please enter sign');
    }
    if(count($validation_error) > 0 ){
      if(array_key_exists('dob',$validation_error)){
        $ajax_response->addCommand(new InvokeCommand('#edit-dob','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-dob', '<div class="unplanErrorClass">'.$validation_error['dob'].'</div>'));
      }
      if(array_key_exists('home_phone',$validation_error)){
        $ajax_response->addCommand(new InvokeCommand('#edit-home-phone','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-home-phone','<div class="unplanErrorClass">'.$validation_error['home_phone'].'</div>'));
      }
      if(array_key_exists('address',$validation_error)){
        $ajax_response->addCommand(new InvokeCommand('#edit-address','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-address', '<div class="unplanErrorClass">'.$validation_error['address'].'</div>'));
      }
      if(array_key_exists('city',$validation_error)){
        $ajax_response->addCommand(new InvokeCommand('#edit-city','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-city', '<div class="unplanErrorClass">'.$validation_error['city'].'</div>'));
      }
      if(array_key_exists('state',$validation_error)){
        $ajax_response->addCommand(new InvokeCommand('#edit-state','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-state', '<div class="unplanErrorClass">'.$validation_error['state'].'</div>'));
      }
      if(array_key_exists('school_graduate',$validation_error)){
        // $ajax_response->addCommand(new InvokeCommand('#edit-state','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-school-graduate', '<div class="unplanErrorClass">'.$validation_error['school_graduate'].'</div>'));
      }
      if(array_key_exists('ged',$validation_error)){
        // $ajax_response->addCommand(new InvokeCommand('#edit-state','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-ged', '<div class="unplanErrorClass">'.$validation_error['ged'].'</div>'));
      }
      if(array_key_exists('year_complete',$validation_error)){
        // $ajax_response->addCommand(new InvokeCommand('#edit-state','addClass',['error']));
        $ajax_response->addCommand(new AfterCommand('#edit-year-complete', '<div class="unplanErrorClass">'.$validation_error['year_complete'].'</div>'));
      }
      $ajax_response->addCommand(new AfterCommand('#edit-validate-agreement', '<div class="unplanErrorClass">Please fill all the required fields (*)</div>'));
      $ajax_response->addCommand(new InvokeCommand(NULL, 'agreementScroll', $leadData));
    }else{
      // \Drupal::logger('enrollment accept')->notice('echo '.print_r(['accepted'],TRUE));
      $form_values = $form_state->getValues();
      $leadData['email'] = $form_state->getValue('x_email');
      
      $GenratePDF = \Drupal::service('pdf.agreement');
      $policy_pdf = $GenratePDF->generatePdf($form_values);
      
      $policy_data['policy_pdf'] = $policy_pdf;
      $policy_data['policy_status'] = 1;
      $policy_data['policy'] = 'agreement';
      $policy_data['transaction_id'] = $form_values['x_cust_id'];
      $policy_data['acceptance_date'] = strtotime($form_values['student_agreement_date']);
      $policy_data['enrollment_agreement_info'] = json_encode([
        'dob' => strtotime($form_values['dob']),
        'address' => $form_values['address'],
        'city' => $form_values['city'],
        'state' => $form_values['state'],
        'home_phone' => $form_values['home_phone'],
        'school_graduate' => $form_values['school_graduate'],
        'acceptance_date' =>  $form_values['student_agreement_date'],
        'ged' => $form_values['ged'],
        'year_complete' => $form_values['year_complete'],
        'student_name' => $form_values['student_name'],
        'student_sign' => $form_values['student_sign'],
        'student_agreement_date' => $form_values['student_agreement_date'],
        ]);
        updatePolicyStatus($policy_data);

      /**Update hubspot agreement pdf */
      $leadData['policy_pdf'] = $policy_pdf;
      $leadData['hubspot_key'] = 'enrollment_agreement';
      $HubspotAPI = \Drupal::service('api.hubspot');
      $contactID = $HubspotAPI->updateHubspotPDFLinks($leadData);
      $ajax_response->addCommand(new InvokeCommand(NULL, 'acceptagreement', $policy_data));

      $ajax_response->addCommand(new InvokeCommand(NULL, 'agreementfieldValidation', $leadData));
      
    }
    return $ajax_response;

  }
  /** reject enrollment agreement */
  public function rejectEnrollmentAgreement(array &$form, FormStateInterface $form_state){
    $form_values = $form_state->getValues();
     // \Drupal::logger('reject agreement')->notice('echo '.print_r(['accepted'],TRUE));
    $ajax_response = new AjaxResponse();

    $leadData['policy_pdf'] = 'rejected';
    $leadData['email'] = $form_state->getValue('x_email');
    $leadData['hubspot_key'] = 'enrollment_agreement';

    $policy_data['policy_pdf'] = 'rejected';
    $policy_data['policy_status'] = 0;
    $policy_data['policy'] = 'agreement';
    $policy_data['transaction_id'] = $form_values['x_cust_id'];
    updatePolicyStatus($policy_data);
    $ajax_response->addCommand(new InvokeCommand(NULL, 'rejectagreementjsCallback', $policy_data));

    /**Update hubspot agreement pdf */
    $HubspotAPI = \Drupal::service('api.hubspot');
    $contactID = $HubspotAPI->updateHubspotPDFLinks($leadData);

    return $ajax_response;
  }
  /** Payment click (PAY NOW)  */
  public function callbackPaymentClick(array &$form, FormStateInterface $form_state){
    $session = \Drupal::request()->getSession();
    $transaction_id = $session->get('transaction_id');
    $ajax_response = new AjaxResponse();
    
    $ajax_response->addCommand(new InvokeCommand('#edit-finance-options','removeClass',['error']));
    if($form_state->getValue('university_id')){
      if($form_state->getTriggeringElement()['#op'] == 'Pay via Climb Credit Payment Options'){
        $payment_option = 'Paid via Climb Credit Payment Options';
        $PaymentMode = 'ONLINE';
        $university = \Drupal::entityTypeManager()->getStorage('node')->load($form_state->getValue('university_id'));
        $payment_link = $university->get('field_credit_payment_link')->getValue()[0]['value'];
      }else{
        $payment_option = 'Paid Via ELMSelect Payment Options';
        $PaymentMode = 'LOAN';
        $university = \Drupal::entityTypeManager()->getStorage('node')->load($form_state->getValue('university_id'));
        if($form_state->getValue('finance_options') == 'SFO'){
          $ajax_response->addCommand(new InvokeCommand('#edit-finance-options','addClass',['error']));
          return $ajax_response;
        }
        $link_array = Node::load($form_state->getValue('university_id'));
        $link_list = $link_array->get('field_payment')->getValue();
        if($link_list){
          foreach($link_list as $values){
            if($form_state->getValue('finance_options') == $values['title']){
              $payment_link = $values['uri'];
            }
          }
        }
      }
      $connection = \Drupal::service('database');
        $query = $connection->update('payeezy_payment_details')
        ->fields(['delivery_mode' => 'Online '.$payment_option])
        ->condition('transaction_id', $transaction_id, '=')
        ->execute();
        /**DMS API2 call */
        $form_field_values = $form_state->getValues();
          $DMS_Config = \Drupal::config('university_dmsapi.config');
          $DMS_API = \Drupal::service('srl_payment.dms');
          $api_values = [];
          $api_values['OrgID'] = $form_field_values['OrgID'];
          $api_values['Name'] = $form_field_values['student_name'];
          $api_values['CourseCode'] = $form_field_values['coursecode'];
          $api_values['ClientID'] = $form_field_values['clientID'];
          $api_values['BatchID'] = $form_field_values['BatchID'];
          $api_values['CountryCode'] = $form_field_values['CountryCode'];
          $api_values['MobileNo'] = $form_field_values['MobileNo'];
          $api_values['EmailID'] = $form_field_values['x_email'];
          $date =  \Drupal::time()->getRequestTime();
          $api_values['DateTime'] = date('Y-m-d',$date);
          $api_values['Fee'] = [
            array(
              'PaymentMode' => $form_field_values['PaymentMode1'],
              'FeePatternCode' => $form_field_values['FeePatternCode1'],
              'Currency' => $form_field_values['Currency1'],
              'FeeAmount' => $form_field_values['FeeAmount1'],
            ),
            array(
              'PaymentMode' => $form_field_values['PaymentMode2'],
              'FeePatternCode' => $form_field_values['FeePatternCode2'],
              'Currency' => $form_field_values['Currency2'],
              'FeeAmount' => $form_field_values['FeeAmount2'],
            )
          ];
          $apiBypass = false;
          if($form_field_values['coursecode']){
            $DMS_FEE = $DMS_API->DMS_InsertLearnerDetail($api_values, $DMS_Config);
          }else{
            $apiBypass = 'skipedCourse';
          }
          // \Drupal::logger('API  response')->notice(print_r($DMS_FEE,true));
        /**DMS API */
        if($DMS_FEE->MESSAGE == 'Records Inserted' || $apiBypass == 'skipedCourse'){
          if($payment_link){
            $leadData['link']  = $payment_link;
            $leadData['window_name']  = \Drupal::time()->getRequestTime();
            $ajax_response->addCommand(new InvokeCommand(NULL, 'paymentredirect', [$leadData]));
          }else{
            $ajax_response->addCommand(new InvokeCommand(NULL, 'createHubspotTaskPopup', [['redirect'],'fixed']));
          }
        }else{
          $ajax_response->addCommand(new InvokeCommand(NULL, 'errorinAPI', ['apiResponse']));
        }
    }
    return $ajax_response;
  }
}