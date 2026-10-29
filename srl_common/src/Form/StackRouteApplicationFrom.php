<?php
/**
 * @file
 * Contains \Drupal\amazing_forms\Form\ContributeForm.
 */

namespace Drupal\srl_common\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\AfterCommand;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Ajax\HtmlCommand;
/**
 * Contribute form.
 */
class StackRouteApplicationFrom extends FormBase {
   /**
    * {@inheritdoc} 
    */
   public function getFormId() {
      return 'stackroute_application_form';	
   }
   /**
    * {@inheritdoc}
    */
   public function buildForm(array $form, FormStateInterface $form_state) {
      $data = resumeApplicationForm();
      $form['user_info'] = [HASH_TYPE => 'container',];
      $form['user_info']['program_interest'] = [HASH_TYPE => SELECTFIELD,HASH_OPTIONS => $options,HASH_DEFAULT_VALUE => 2,];
      $form['user_info']['name'] = [HASH_TYPE => TEXTFIELD,HASH_DEFAULT_VALUE => '',];
      $form['user_info']['last_name'] = [HASH_TYPE => TEXTFIELD,HASH_DEFAULT_VALUE => '',];
      $form['user_info']['email'] = [HASH_TYPE => TEXTFIELD,HASH_DEFAULT_VALUE => '',];
      $form['user_info']['phone'] = [HASH_TYPE => TEXTFIELD,HASH_DEFAULT_VALUE => '',];
      $form['user_info']['zip'] = [HASH_TYPE => TEXTFIELD,HASH_DEFAULT_VALUE => '',];
      $form['user_info']['utm_source'] = [HASH_TYPE => HIDDEN,HASH_VALUE => '',];
      $form['user_info']['utm_campaign'] = [HASH_TYPE => HIDDEN,HASH_VALUE => '',];
      $form['user_info']['utm_medium'] = [HASH_TYPE => HIDDEN,HASH_VALUE => '',];
      $form['user_info']['utm_term'] = [HASH_TYPE => HIDDEN,HASH_VALUE => '',];
      $form['user_info']['utm_content'] = [HASH_TYPE => HIDDEN,HASH_VALUE => '',];
      $form['user_info']['gclid'] = [HASH_TYPE => HIDDEN,HASH_VALUE => '',];

      $form['your_program_goals'] = [HASH_TYPE => 'container',];
      $form['your_educational_background'] = [HASH_TYPE => 'container',];
      $form['your_current_employment_status'] = [HASH_TYPE => 'container',];
      $form['our_overall_career_outlook'] = [HASH_TYPE => 'container',];




      $form['emailid'] = array(    
         HASH_TYPE => 'textfield',
         HASH_TITLE =>'Email<i>*</i>'   
      );
      $form['phone_no'] = array(    
         HASH_TYPE => 'textfield',
         HASH_TITLE =>'Phone<i>*</i>',
         
      );
      $form['resume_form'] = array(
         HASH_TYPE => SUBMIT,
         HASH_VALUE => $this->t('RESUME'),
         HASH_AJAX => array(
            CALLBACK => [$this,'resumeApplicationFormSubmit'],
            EVENT => 'click',
            PROGRESS => array(
               TYPE => 'throbber',
               MESSAGE => 'Success',
           ),
         ),
         HASH_ATTRIBUTES => [
           'class' => array('class' => 'alpha-link alpha-uppercase'),
         ],
      );
      return $form;
   }
   public function validateForm(array &$form, FormStateInterface $form_state) {
   
   }
   /**
   * {@inheritdoc}
   */
   public function submitForm(array &$form, FormStateInterface $form_state) {

   }
   function resumeApplicationFormSubmit(array &$form, FormStateInterface $form_state){
      
      $ajax = new AjaxResponse();
      $ajax->addCommand(new RemoveCommand('.requireErrorClass'));
      $ajax->addCommand(new InvokeCommand('#edit-emailid','removeClass',['error']));
      $ajax->addCommand(new InvokeCommand('#edit-phone-no','removeClass',['error']));
      
      $values = $form_state->getValues();
      $required = '';
      $phonesubmit = FALSE;
      $emailsubmit = FALSE;
      if($values['emailid']){
         if (filter_var($values['emailid'], FILTER_VALIDATE_EMAIL)) {
            $emailsubmit = TRUE;
         }else{
            $emailsubmit = FALSE;
            $ajax->addCommand(new AfterCommand('#edit-emailid', '<div class="requireErrorClass">Invalid Email id</div>'));
            $ajax->addCommand(new InvokeCommand('#edit-emailid','addClass',['error']));
         }
      }else{
         $emailsubmit = FALSE;
         $required = 'required';
      }
      if($values['phone_no']){
         $validate = phoneNumberValidation($values['phone_no']);
         if($validate){
            $phonesubmit = TRUE;
         }else{
            $phonesubmit = FALSE;
            $ajax->addCommand(new AfterCommand('#edit-phone-no', '<div class="requireErrorClass">Invalid phone no</div>'));
            $ajax->addCommand(new InvokeCommand('#edit-phone-no','addClass',['error']));
         }

      }else{
         $phonesubmit = FALSE;
         $required = 'required';
      }
      if($required == 'required'){
         $ajax->addCommand(new AfterCommand('#edit-resume-form', '<div class="requireErrorClass">Please fill all the required fields (*)</div>'));
      }elseif($phonesubmit && $emailsubmit){
          
         $phone_number = str_replace('+','',$values['phone_no']);
         $phone_number = str_replace('-','',$phone_number);
         $phone_number = str_replace(' ','',$phone_number);
         $phone_number = str_replace('(','',$phone_number);
         $phone_number = str_replace(')','',$phone_number);
         $payment = db_select('payeezy_payment_details', 'detail');
         $payment->fields('detail', ['transaction_id']);
         $payment->condition('detail.email_id',$values['emailid']);
         $payment->condition('detail.mobile_no',$phone_number);
         $payment->orderby('detail.id','DESC');
         $payment->range(0,1);
         $payment_detail = $payment->execute()->fetchCol();
         if($payment_detail){
            $session = \Drupal::request()->getSession();
            $session->set('transaction_id', $payment_detail[0]);
            $ajax->addCommand(new RedirectCommand('order-summary'));
            
         }else{
            $application = db_select('srl_candidate_test_details', 'detail');
            $application->fields('detail', ['transaction_id']);
            $application->condition('detail.email_id',$values['emailid'], '=');
            $application->condition('detail.mobile_no',$phone_number,'=');
            $application->orderby('detail.id','DESC');
            $application->range(0,1);
            $assessment = $application->execute()->fetchCol();

            if($assessment){
               $session = \Drupal::request()->getSession();
               $session->set('transaction_id', $assessment[0]);
               
               $ajax->addCommand(new RedirectCommand('thank-you'));
               // redirect to thank you page
            }else{
               // no record found
               
               $html = '
               <div class="no-data-found"><img src="/themes/hilbert/images/default_images/no-data.png"><p class="first-p">No Application Found</p><p>Looks like you haven’t filled your application yet</p><p>Want to start an application now?</p><button type="button" class="btn-apply" data-dismiss="modal">APPLY NOW</button></div>
               ';
               $ajax->addCommand(new HtmlCommand('#resumeapplication',$html));

            }

         }
         
      }
      return $ajax;
   }
}