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
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Ajax\RedirectCommand;

class TermAgreementForm extends FormBase{
   /**
   * {@inheritdoc}
   */
    public function getFormId() {
        return 'term_agreement_form';
    }
    /**
    * {@inheritdoc}
    */
    public function buildForm(array $form, FormStateInterface $form_state){
        $result = getUserinfo('(000) 000-0000_1626863609');
        $result = json_decode(stripslashes($result));
        // kint($result);

        $form['f_name'] = [
            '#type' => 'textfield',
            '#title' => 'Name',
            
        ];
        $form['dob'] = [
            '#type' => 'textfield',
            '#title' => 'D.O.B.:',
        ];
        $form['address'] = [
            '#type' => 'textfield',
            '#title' => 'Address:',
        ];
        $form['city'] = [
            '#type' => 'textfield',
            '#title' => 'City:',
        ];
        $form['state'] = [
            '#type' => 'textfield',
            '#title' => 'State:',
        ];
        $form['zip'] = [
            '#type' => 'textfield',
            '#title' => 'Zip:',
        ];
        $form['phone'] = [
            '#type' => 'textfield',
            '#title' => 'Cell Phone:',
        ];
        $form['home_phone'] = [
            '#type' => 'textfield',
            '#title' => 'Home Phone:',
        ];
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => 'Main Email:',
        ];
        $form['school_graduate'] = [
            '#type' => 'textfield',
            '#title' => 'High School Graduate:',
        ];
        $form['ged'] = [
            '#type' => 'textfield',
            '#title' => 'Earned GED:',
        ];
        $form['year_complete'] = [
            '#type' => 'textfield',
            '#title' => 'Year Completed:',
        ];
        $form['program'] = [
            '#type' => 'textfield',
            '#title' => 'Program Track:',
        ];
        $form['status'] = [
            '#type' => 'textfield',
            '#title' => 'Status:',
        ];
        $form['part_time'] = [
            '#type' => 'textfield',
            '#title' => 'Part Time',
        ];
        $form['non_degree'] = [
            '#type' => 'textfield',
            '#title' => 'Non-Degree Instructional Hours:',
        ];
        $form['program_start_date'] = [
            '#type' => 'textfield',
            '#title' => 'Program Start Date:',
        ];
        $form['expected_gradiation_date'] = [
            '#type' => 'textfield',
            '#title' => 'Expected Graduation Date:',
        ];

        $form['#theme'] = 'term_agreement_form';
        return $form;

    }
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        
    }
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        
    }
}
