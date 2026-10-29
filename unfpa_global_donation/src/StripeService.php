<?php

namespace Drupal\unfpa_global_donation;

use Stripe\Error\Base;
use Stripe\Error\ApiConnection;
use Stripe\Error\Authentication;
use Stripe\Error\InvalidRequest;
use Stripe\Error\Card;
use Stripe\Stripe;
use Stripe\StripeClient;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Stripe Service for Donation Pages.
 */
class StripeService {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The Database Connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * ResquestStack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, Connection $connection, RequestStack $request_stack) {
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->connection = $connection;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('language.manager'),
      $container->get('database'),
      $container->get('request_stack')
    );
  }

  /**
   * Stripe payment function.
   *
   * @param mixed $param
   *   Submitted values of the form.
   */
  public function payWithStripe($param) {
    $stripe_token = $param['values']['stripeToken'];
    $formfield_email = $param['values']['formfield_email'];
    $donate_amount = $param['values']['amount'];
    $description = $param['values']['description'];
    $donate_subscription = $param['values']['donate_subscription'];
    $donate_currency = !empty($param['values']['currency_code']) ? $param['values']['currency_code'] : 'usd';
    $user_metadata = [];
    $user_metadata['first_name'] = $param['values']['formfield_first_name'];
    $user_metadata['last_name'] = $param['values']['formfield_last_name'];
    $user_metadata['address'] = $param['values']['formfield_address'];
    $user_metadata['address2'] = $param['values']['formfield_address_2'];
    $user_metadata['city'] = $param['values']['formfield_city'];
    $user_metadata['country'] = $param['values']['country'];
    // Customer data for Indian customer.
    $metadata['city'] = !empty($param['values']['formfield_city']) ? $param['values']['formfield_city'] : '';
    $metadata['country'] = $param['values']['country'];
    $metadata['line1'] = !empty($param['values']['formfield_address']) ? $param['values']['formfield_address'] : '';
    // Prepare payment data for Stripe server.
    $stripe = new StripeClient($param['keys']['secret_key']);
    Stripe::setMaxNetworkRetries(2);
    if (isset($param['keys']['secret_key'])) {
      $amount = $donate_amount * 100;
      $donorEmail = $formfield_email;
      try {
        // Get stripe token.
        $token = $stripe_token;
        // Create customer for each transaction.
        $customer = $stripe->customers->create([
          'email' => $donorEmail,
          'source' => $token,
          'metadata' => $user_metadata,
          'address' => $metadata,
          'name' => $user_metadata['first_name'],
        ]);
        // Create subscription and plan for Monthly donation.
        if ($donate_subscription == 'monthly') {
          // Create a products.
          $products = $stripe->products->create([
            'name' => $this->t('UNFPA Monthly Plan'),
            'type' => "service",
          ]);
          // Define monthly plan.
          $plan = $stripe->plans->create([
            "amount" => $amount,
            "currency" => $donate_currency,
            "interval" => "month",
            "product" => $products->id,
          ]);
          // Create a subscription.
          $stripe->subscriptions->create([
            "customer" => $customer->id,
            "items" => [
            [
              "plan" => $plan->id,
            ],
            ],
          ]);
        }
        // Create stripe charges.
        $charge = [];
        if ($donate_subscription == 'one_time') {
          $charge = $stripe->charges->create([
            'customer' => $customer->id,
            'amount' => $amount,
            'currency' => $donate_currency,
            'description' => $description,
            'receipt_email' => $donorEmail,
          ]);
        }
        // Select info for donation tracking
        $tracking_data = [
          'transaction_id' => $charge->id,
          'customer_id' => $customer->id,
          'name' => $user_metadata['first_name'] . ' ' . $user_metadata['last_name'],
          'amount' => $charge->amount,
          'currency' => $charge->currency,
        ];

        $result['customer'] = $customer;
        $result['charge'] = $charge;
        $result['tracking_data'] = $tracking_data;
        $result['type'] = "success";
      }
      // Handling Stripe api errors in Try catch block.
      catch (Card $e) {
        $result['type'] = $this->getStripeErrorMessage($e);
      }
      catch (InvalidRequest $e) {
        $result['type'] = $this->getStripeErrorMessage($e);
      }
      catch (Authentication $e) {
        $result['type'] = $this->getStripeErrorMessage($e);
      }
      catch (ApiConnection $e) {
        $result['type'] = $this->getStripeErrorMessage($e);
      }
      catch (Base $e) {
        $result['type'] = $this->getStripeErrorMessage($e);
      }
      catch (Exception $e) {
        $result['type'] = $this->getStripeErrorMessage($e);
      }

      $_SESSION['fundraising'] = [];
      $_SESSION['fundraising']['result'] = $result['type'];
      if ($donate_subscription == 'monthly') {
        $_SESSION['fundraising']['type'] = "UNFPA Monthly Plan";
      }
      else {
        $_SESSION['fundraising']['type'] = "UNFPA Onetime Plan";
      }
      return $result;
    }

  }

  /**
   * Implements getStripeErrorMessage().
   *
   * This function is to prepare Stripe api response error message.
   */
  public function getStripeErrorMessage($e) {
    $body = $e->getJsonBody();
    $err = $body['error'];
    return $err['message'];
  }

}
