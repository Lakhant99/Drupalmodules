(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.unfpa_global_donate_page_stripe = {
    attach: function (context, settings) {
      jQuery('#unfpa-donation-form', context).once('unfpa_global_donate_page_stripe').each(function (index) {
        var params = drupalSettings.params;

        var donationPageFormID = drupalSettings.donation_form_id;
        if (!donationPageFormID) {
          donationPageFormID = 'unfpa-donation-form';
        }

        // Initialize stripe elements.
        var stripe = Stripe(params.donate_pk);
        var elements = stripe.elements();
        // Create card element.
        var card = elements.create('card');
        // Add an instance of the card UI component into the `card-element` <div>.
        card.mount('#card-element');

        // Handle real-time validation errors from the card Element.
        card.addEventListener('change', function (event) {
          var displayError = document.getElementById('card-errors');
          if (event.error) {
            displayError.textContent = event.error.message;
          }
          else {
            displayError.textContent = '';
          }
        });

        // Handle form submission.
        var form = document.getElementById(donationPageFormID);
        form.addEventListener('submit', function (event) {
          event.preventDefault();
          // validate required form elements.
          validateFormElements();
        });

        function validateFormElements() {
          var firstname = jQuery('#edit-formfield-first-name').val();
          var lastname = jQuery('#edit-formfield-last-name').val();
          var email = jQuery('#edit-formfield-email').val();
          var setFocusElement = "";
          var validateCard = "";
          var other_amount_tab = jQuery('#other_amount_tab').val();
          if (other_amount_tab == '1') {
            var otherField = jQuery('#other_amount').val();
            var minAmount = jQuery('#MonthlyPay li:first .monthly-price').attr('data-price');
            jQuery('#other_amount_value').val(otherField);
            if (!otherField) {
              jQuery('#otheramount-error-wrap').show();
              jQuery('#otheramount-error-wrap').text(Drupal.t("Please enter amount."));
              jQuery('#other_amount').addClass('StripeElement--empty StripeElement--invalid');
              validateCard = 'no';
              if (setFocusElement == "") {
                setFocusElement = '#other_amount';
              }
            }
            else if (otherField < minAmount) {
              var currencySymbol = jQuery('#MonthlyPay li:first .monthly-price').attr('data-symbol');
              jQuery('#otheramount-error-wrap').show();
              jQuery('#otheramount-error-wrap').text(Drupal.t("Your donation amount must be at least") + ' ' + currencySymbol + minAmount);
              jQuery('#other_amount').addClass('StripeElement--empty StripeElement--invalid');
              validateCard = 'no';
              if (setFocusElement == "") {
                setFocusElement = '#other_amount';
              }
            }
            else {
              jQuery('#otheramount-error-wrap').hide();
              jQuery('#otheramount-error-wrap').text("");
              jQuery('#other_amount').removeClass('StripeElement--empty StripeElement--invalid');
              if (validateCard != 'no') {
                validateCard = 'yes';
              }
              else {
                validateCard = 'no';
              }
            }
          }

          if (!firstname) {
            jQuery('#firstname-error-wrap').show();
            jQuery('#firstname-error-wrap').text(Drupal.t("Please enter first name."));
            jQuery('#edit-formfield-first-name').addClass('StripeElement--empty StripeElement--invalid');
            validateCard = 'no';
            if (setFocusElement == "") {
              setFocusElement = '#edit-formfield-first-name';
            }
          }
          else {
            jQuery('#firstname-error-wrap').hide();
            jQuery('#firstname-error-wrap').text("");
            jQuery('#edit-formfield-first-name').removeClass('StripeElement--empty StripeElement--invalid');
            if (validateCard != 'no') {
              validateCard = 'yes';
            }
            else {
              validateCard = 'no';
            }
          }
          if (!lastname) {
            jQuery('#lastname-error-wrap').show();
            jQuery('#lastname-error-wrap').text(Drupal.t("Please enter last name."));
            jQuery('#edit-formfield-last-name').addClass('StripeElement--empty StripeElement--invalid');
            validateCard = 'no';
            if (setFocusElement == "") {
              setFocusElement = '#edit-formfield-last-name';
            }
          }
          else {
            jQuery('#lastname-error-wrap').hide();
            jQuery('#lastname-error-wrap').text("");
            jQuery('#edit-formfield-last-name').removeClass('StripeElement--empty StripeElement--invalid');
            if (validateCard != 'no') {
              validateCard = 'yes';
            }
            else {
              validateCard = 'no';
            }
          }
          if (!email) {
            jQuery('#email-error-wrap').show();
            jQuery('#email-error-wrap').text(Drupal.t("Please enter email."));
            jQuery('#edit-formfield-email').addClass('StripeElement--empty StripeElement--invalid');
            validateCard = 'no';
            if (setFocusElement == "") {
              setFocusElement = '#edit-formfield-email';
            }
          }
          else {
            var emailErrorText = '';
            // Email regExp pattern to validate email.
            var mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
            if (!mailformat.test(email)) {
              emailErrorText = 'Please enter valid email.';
            }

            if (emailErrorText) {
              jQuery('#email-error-wrap').show();
              jQuery('#email-error-wrap').text(emailErrorText);
              setFocusElement = '#edit-formfield-email';
              validateCard = 'no';
            }
            else {
              jQuery('#email-error-wrap').hide();
              jQuery('#email-error-wrap').text("");
              jQuery('#edit-formfield-email').removeClass('StripeElement--empty StripeElement--invalid');
              if (validateCard != 'no') {
                validateCard = 'yes';
              }
              else {
                validateCard = 'no';
              }
            }
          }
          // Validate card element.
          if (validateCard === 'yes') {
            // Disable pointer event to avoid form resubmission.
            jQuery("#edit-submit").css('pointer-events', 'none');
            stripe.createToken(card).then(function (result) {
              if (result.error) {
                // Check if there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                // Enable pointer event if any error occurs.
                jQuery("#edit-submit").css('pointer-events', 'auto');
                jQuery("#edit-submit").css('cursor', 'pointer');
              }
              else {
                if (grecaptcha.getResponse() == "") {
                  jQuery('#captcha-error-wrap').text(Drupal.t("You can't proceed! Please click on the reCAPTCHA box."));
                  jQuery("#edit-submit").css('pointer-events', 'auto');
                  jQuery("#edit-submit").css('cursor', 'pointer');
                  return false;
                } else {
                  jQuery('#captcha-error-wrap').text("");
                }
                jQuery('#form-loader').addClass('active');
                // Send the token to server.
                stripeTokenHandler(result.token);
              }
            });

          }
          else {
            jQuery(setFocusElement).focus();
            return false;
          }
        }
        function stripeTokenHandler(token) {
          // Insert the token ID into the form.
          var form = document.getElementById(donationPageFormID);
          jQuery("#stripeToken").val(token.id);
          // Submit the form.
          form.submit();
        }
      });
    }
  };
})($, Drupal, drupalSettings);
