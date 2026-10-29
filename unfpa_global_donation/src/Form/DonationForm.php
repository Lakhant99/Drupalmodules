<?php

namespace Drupal\unfpa_global_donation\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\unfpa_global_donation\DonationPageService;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\unfpa_global_donation\StripeService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Session\SessionManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Form for Donation.
 */
class DonationForm extends FormBase {
  /**
   * The Donation Page service.
   *
   * @var \Drupal\unfpa_global_donation\DonationPageService
   */
  protected $donation_service;

  /**
   * Current Path Service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $current_path;

  /**
   * Configuration object.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The StripeService data services.
   *
   * @var \Drupal\unfpa_global_donation\StripeService
   */
  protected $stripeService;

  /**
   * The Account manager service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Session manager variable.
   *
   * @var Drupal\Core\Session\SessionManager
   */
  protected $sessionManager;

  /**
   * The Request Stack Service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $request;

  /**
   * {@inheritdoc}
   */
  public function __construct(DonationPageService $donationService, CurrentPathStack $currentPath, ConfigFactory $config_factory, StripeService $stripeService, AccountProxyInterface $current_user, SessionManager $session_manager, RequestStack $request) {
    $this->donation_service = $donationService;
    $this->current_path = $currentPath;
    $this->configFactory = $config_factory;
    $this->stripeService = $stripeService;
    $this->currentUser = $current_user;
    $this->sessionManager = $session_manager;
    $this->request = $request;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('unfpa_global_donation.main'),
      $container->get('path.current'),
      $container->get('config.factory'),
      $container->get('unfpa_global_donation.stripe'),
      $container->get('current_user'),
      $container->get('session_manager'),
      $container->get('request_stack')

    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'unfpa_donation_form';
  }

  /**
   * @inheritdoc
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get county list.
    $current_country_name = '';
    $ip_details = $this->donation_service->ip_details($this->donation_service->get_user_ip());
    if (isset($ip_details->country) && !empty($ip_details->country != '')) {
      $current_country_name = $ip_details->country;
    }

    // Check url exist for current IP address.
    $country_list_array = $this->donation_service->getUNCountryList();
    $current_path = $this->current_path->getPath();

    unset($country_list_array['US']);
    $form['#tree'] = TRUE;
    $form['#action'] = "/pay-donation";
    $form['#attached']['library'][] = 'unfpa_global_fundraising/stripe_form';
    $form['#attached']['drupalSettings']['unfpa_global_donation']['public_key'] = $this->configFactory->get('unfpa_global_fundraising.settings')->get('public_key');

    // Form field for First Name.
    $form['formfield_first_name'] = [
      '#type' => 'textfield',
      '#attributes' => ['placeholder' => $this->t('First Name*')],
      '#minlength' => 3,
      '#maxlength' => 20,
      '#required' => TRUE,
    ];
    // Form field for Last Name.
    $form['formfield_last_name'] = [
      '#type' => 'textfield',
      '#attributes' => ['placeholder' => $this->t('Last Name*')],
      '#minlength' => 3,
      '#maxlength' => 20,
      '#required' => TRUE,
    ];
    // Form field for Email.
    $form['formfield_email'] = [
      '#type' => 'textfield',
      '#attributes' => ['placeholder' => $this->t('Email address*')],
      '#required' => TRUE,
    ];
    // Form field for Address 1.
    $form['formfield_address'] = [
      '#type' => 'textfield',
      '#attributes' => ['placeholder' => $this->t('Address')],
    ];
    // Form field for Address 2.
    $form['formfield_address_2'] = [
      '#type' => 'textfield',
      '#attributes' => ['placeholder' => $this->t('Address line 2')],
    ];
    // Form field for City.
    $form['formfield_city'] = [
      '#type' => 'textfield',
      '#attributes' => ['placeholder' => $this->t('City')],
    ];
    // Form field for Country.
    $form['formfield_country'] = [
      '#type' => 'select',
      '#options' => $country_list_array,
      '#default_value' => isset($current_country_name) ? $current_country_name : '',
    ];
    // Form hidden field for stripe token.
    $form['stripeToken'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'stripeToken'],
    ];
    // Form hidden field for description token.
    $referer_description = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $current_path;
    $form['description'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'description'],
      '#default_value' => isset($referer_description) ? $referer_description : '',
    ];
    // Form hidden field for statement_descriptor token.
    $statement_descriptor = $current_path;
    $form['statement_descriptor'] = [
      '#type' => 'hidden',
      '#attributes' => ['id' => 'statement_descriptor'],
      '#default_value' => isset($statement_descriptor) ? $statement_descriptor : '',
    ];
    $form['formfield_captcha'] = [
      '#type' => 'captcha',
      '#captcha_type' => 'recaptcha/reCAPTCHA',
    ];

    if (!empty($form_state->stripe_error)) {
      $form['stripe_card_error'] = [
        '#type' => 'markup',
        '#markup' => '<div class="stripe-element-error fundraising-field-error">' . $form_state->stripe_error . '</div>',
      ];
    }

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Make donation'),
      '#submit' => ['::submitForm'],
      '#attributes' => ['class' => ['btn secondary']],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $secret_key = $this->configFactory->get('unfpa_global_fundraising.settings')->get('secret_key');
    $public_key = $this->configFactory->get('unfpa_global_fundraising.settings')->get('public_key');
    // Stripe keys.
    $params['keys'] = [
      "secret_key" => $secret_key,
      "public_key" => $public_key,
    ];
    $params['values'] = $form_state->getValues();
    $country_code = $params['values']['formfield_country'];
    $country = $form['formfield_country']['#options'][$country_code];
    $params['values']['country'] = $country;
    if (isset($_POST['amount']) && !empty($_POST['amount'])) {
      $params['values']['amount'] = $_POST['amount'];
    }
    else {
      $params['values']['amount'] = $_POST['other_amount_value'];
    }
    $params['values']['currency_code'] = $_POST['currency_code'];
    $params['values']['donate_subscription'] = $_POST['donate_subscription'];
    $payment_status = $this->stripeService->payWithStripe($params);
    if ($payment_status['type'] != 'success') {
      // Put error into form state for further use.
      $form_state->set('stripe_error', $_SESSION['fundraising']['result']);
      // Rebuild form.
      // For anonymous user, force a session start.
      if ($this->currentUser->isAnonymous()) {
        $this->sessionManager->regenerate();
      }
      $this->request->getCurrentRequest()->getSession()->set('donation_thankyou', 0);
    }
    else {

      // For anonymous user, force a session start.
      if ($this->currentUser->isAnonymous()) {
        $this->sessionManager->regenerate();
      }
      $_SESSION['fundraising']['unfpa_global_donation'] = $payment_status['tracking_data'];
      $this->request->getCurrentRequest()->getSession()->set('donation_thankyou', 1);
      $node_alias = $_POST['node_alias'];
      $path = 'thanks' . $node_alias;
      $response = new RedirectResponse($path);
      $response->send();
      return;
    }
  }

}
