<?php

namespace Drupal\unfpa_global_fgm_campaign\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\unfpa_global_fgm_campaign\FGMCampaignData;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\InvokeCommand;

/**
 * Provides a campaign form.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class TakeActionForm extends FormBase {

  /**
   * The RequestStack service.
   *
   * @var Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The FGM campaign data service.
   *
   * @var Drupal\unfpa_global_fgm_campaign\FGMCampaignData
   */
  protected $fgm_campaign_data;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entityTypeManager service.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $currentLanguage;

  /**
   * Constructs for the campaign data.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The RequestStack services.
   * @param \Drupal\unfpa_global_fgm_campaign\FGMCampaignData $fgm_campaign_data
   *   The Categories data services.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The MessengerInterface service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(RequestStack $request_stack, FGMCampaignData $fgm_campaign_data, MessengerInterface $messenger, EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager) {
    $this->requestStack = $request_stack;
    $this->fgm_campaign_data = $fgm_campaign_data;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entity_type_manager;
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'), $container->get('unfpa_global_fgm_campaign.fgm_campaign_data'), $container->get('messenger'), $container->get('entity_type.manager'), $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fgm_campaign_pledge_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#tree'] = TRUE;
    $form['#theme'] = 'fgm_campaign_pledge_form';
    $form['#attached'] = [
      'library' => [
        'unfpa_global_fgm_campaign/unfpa_global_fgm_campaign',
        'core/drupal.dialog.ajax',
      ],
    ];
    // Disable browser HTML5 validation.
    $form['#attributes']['novalidate'] = 'novalidate';
    $form['firstname'] = [
      '#title' => $this->t('First name'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#maxlength' => 255,
    ];
    $form['lastname'] = [
      '#title' => $this->t('Last name'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#maxlength' => 255,
    ];
    // This is a honeypot spambots trap field.
    $form['username'] = [
      '#title' => $this->t('Username'),
      '#type' => 'textfield',
      '#maxlength' => 255,
      '#attributes' => [
        'title' => $this->t('Please leave this field empty'),
        'tabindex' => -1,
      ],
    ];
    $form['email'] = [
      '#title' => $this->t('Email address'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#maxlength' => 255,
    ];
    $form['country'] = [
      '#title' => $this->t('Country/Territory'),
      '#type' => 'select',
      '#required' => TRUE,
      '#options' => $this->fgm_campaign_data->unfpa_global_fgm_campaign_pledge_get_countries(),
      '#default_value' => false,
      '#empty_option'  => '',
    ];
    // Hidden element changed by ajax submit if validation went ok.
    // After ajax submission this field will have one of these values:
    // 0 - submission failed due to validation errors
    // 1 - submission went ok and country got +1 from this user
    // 2 - submission went ok, but country didn't get +1 because the user
    // has already submitted this form in the past.
    $form['result'] = [
      '#type' => 'hidden',
      '#value' => '0',
      '#attributes' => [
        'class' => ['form-item-result'],
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#value' => t('Submit'),
      '#type' => 'submit',
      '#ajax' => [
        'callback' => '::ajaxCampaignPledgeSubmitForm',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
        ],
      ],

    ];
    $share_data = $this->fgm_campaign_data->unfpa_global_fgm_campaign_share_get_data('map');
    $sharelink = [
      'share_url' => $this->fgm_campaign_data->unfpa_global_fgm_campaign_share_get_href('map'),
      'share_img' => $share_data['img'],
      'share_description' => $share_data['description'],
      'color' => '#9956cc',
    ];
    $form['sharelink'] = [
      '#type' => 'markup',
      '#markup' => $this->fgm_campaign_data->unfpa_global_fgm_campaign_sharelink($sharelink),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxCampaignPledgeSubmitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $css = ['border-bottom' => '1px solid red', 'color' => 'red'];
    // Check the first name is valid.
    if (!$form_state->getValue('firstname')||empty($form_state->getValue('firstname'))) {      
      $response->addCommand(new CssCommand('#edit-firstname', $css));
      $response->addCommand(new HtmlCommand('#firstname_err', $this->t('First name field is required.')));
    }
    else {
      $response->addCommand(new HtmlCommand('#firstname_err', ''));
    }
    // Check the last name is valid.
    if (!$form_state->getValue('lastname')||empty($form_state->getValue('lastname'))) {
      $response->addCommand(new CssCommand('#edit-lastname', $css));
      $response->addCommand(new HtmlCommand('#lastname_err', $this->t('Last name field is required.')));
    }
    else {
      $response->addCommand(new HtmlCommand('#lastname_err', ''));
    }
    // Check the email is valid.
    if (!$form_state->getValue('email')||!filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new CssCommand('#edit-email', $css));
      $response->addCommand(new HtmlCommand('#email_err', $this->t('Email address field is required.')));
    }
    else {
      $response->addCommand(new HtmlCommand('#email_err', ''));
    }
    
    // Check the email is valid.
    if (!$form_state->getValue('country')||!filter_var($form_state->getValue('country'), FILTER_VALIDATE_EMAIL)) {
      $response->addCommand(new CssCommand('#edit_country_chosen', $css));
      $response->addCommand(new CssCommand('#edit_country_chosen .chosen-single', ['border-bottom' => 'transparent']));
      $response->addCommand(new HtmlCommand('#country_err', $this->t('Country/Territory field is required.')));
    }
    else {
      $response->addCommand(new HtmlCommand('#country_err', ''));
    }

    if (!($form_state->hasAnyErrors())) {
      if ($form_state->get('pledge_check_submission')) {
        $response->addCommand(new InvokeCommand('.fgm-form-success', 'removeClass', ['element-hidden']));
        $response->addCommand(new InvokeCommand('#fgm-campaign-pledge-form', 'trigger', ['reset']));
      }
      else {
        $response->addCommand(new HtmlCommand('#mess_success', $this->t('Your pledge has already been recorded. Thank you.')));
        $response->addCommand(new InvokeCommand('#fgm-campaign-pledge-form', 'trigger', ['reset']));
      }
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $honeypot = $values['username'];
    if ($honeypot != '') {
      return;
    }

    $first_name = $values['firstname'];
    $last_name = $values['lastname'];
    $email = $values['email'];
    $country = $values['country'];
    $ip = $this->requestStack->getCurrentRequest()->getClientIp();;

    // This variable will let the form ajax submit callback know if country
    // got +1 from this user's submission. The reason for this variable being
    // passed via $form_state is that the regular form submit callback is
    // called before ajax submit callback. At the time of ajax callback run the
    // submission data will be already saved into the database, so we woulnd't
    // be able to check submission validity again using
    // unfpa_global_fgm_campaign_pledge_check_submission() as it would always
    // return false in the ajax callback. For this reason, we are calling the
    // checking function just here and passing it's result to ajax submit
    // handler.
    $form_state->set('pledge_check_submission', FALSE);

    // Save +1 for the country if there was no submission with this e-mail yet.
    if ($this->fgm_campaign_data->unfpa_global_fgm_campaign_pledge_check_submission($email)) {
      $this->fgm_campaign_data->unfpa_global_fgm_campaign_pledge_save_submission($country, $email, $ip);
      $form_state->set('pledge_check_submission', TRUE);
    }

    // Sign up the user to Constant Contact emailing.
    unfpa_global_fgm_campaign_constant_contact_signup($first_name, $last_name, $email);
    $form_state->setRebuild();
  }

}
