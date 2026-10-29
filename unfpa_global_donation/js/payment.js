  (function ($, Drupal, drupalSettings) {
    'use strict';

    Drupal.behaviors.unfpa_global_donation = {
      attach: function (context, settings) {
        jQuery('body', context).once('unfpa_global_donation').each(function (index) {
          jQuery('.other-wrapper').hide();

          jQuery("#MonthlyPayClick").click(function () {
            jQuery('.amount-panel.tab-content').show();
            jQuery("#MonthlyPay").show();
            jQuery("#OneTimePay").hide();
            jQuery("#MonthlyPayClick").addClass("active");
            jQuery("#OneTimePayClick").removeClass("active");
            jQuery('#donate_subscription').val('monthly');
            jQuery('.donate-currency-form input:hidden[name=paymt_option]').val('monthly');
            jQuery('#final_amount').val(jQuery("#MonthlyPay li.active").find('.current_amount').html());
            jQuery('.onetime_values').text(jQuery("#MonthlyPay li.active a").find('.current_amount').html());
          });

          jQuery("#OneTimePayClick").click(function () {
            jQuery('.amount-panel.tab-content').show();
            jQuery("#OneTimePay").show();
            jQuery("#MonthlyPay").hide();
            jQuery("#OneTimePayClick").addClass("active");
            jQuery("#MonthlyPayClick").removeClass("active");
            jQuery('#donate_subscription').val('one_time');
            jQuery('.donate-currency-form input:hidden[name=paymt_option]').val('one_time');
            jQuery('#final_amount').val(jQuery("#OneTimePay li.active").find('.current_amount').html());
          });

          jQuery(".monthly-price").click(function () {
            var $num = jQuery(this).attr('data-num');
            var $symbol = jQuery(this).attr('data-symbol');
            var $type = jQuery(this).attr('data-type');
            var $price = jQuery(this).attr('data-price');
            if ($num == '6') {
              var lval = '9%';
            } else if ($num == '7') {
              var lval = '29%';
            } else if ($num == '8') {
              var lval = '50%';
            } else if ($num == '9') {
              var lval = '70%';
            } else if ($num == '10') {
              var lval = '90%';
            }
            if ($type == 1) {
              jQuery('.tab-pane.active p').hide();
              jQuery('#other_amount_tab').val('1');
              jQuery('.amount_d').hide();
              jQuery('.other-wrapper').show();
              jQuery('.amount-panel.tab-content').hide();
            } else {
              jQuery('.amount-panel.tab-content').show();
              jQuery('.tab-pane.active p').show();
              jQuery('#other_amount_tab').val('0');
              jQuery('.amount_d').show();
              jQuery('.other-wrapper').hide();
            }
            jQuery(".onetime_currency_code").text($symbol);
            jQuery(".monthly_values").text($price);
            jQuery('#final_amount').val($price);
            jQuery('.onetime_values').text($price);
            document.getElementById("arrow2").style.left = lval;
          });

          jQuery(".onetime-price").click(function () {
            var $num = jQuery(this).attr('data-num');
            var $symbol = jQuery(this).attr('data-symbol');
            var $type = jQuery(this).attr('data-type');
            var $price = jQuery(this).attr('data-price');
            if ($num == '1') {
              var lval = '9%';
            } else if ($num == '2') {
              var lval = '29%';
            } else if ($num == '3') {
              var lval = '50%';
            } else if ($num == '4') {
              var lval = '70%';
            } else if ($num == '5') {
              var lval = '90%';
            }
            if ($type == 1) {
              jQuery('.tab-pane.active p').hide();
              jQuery('#other_amount_tab').val('1');
              jQuery('.amount_d').hide();
              jQuery('.other-wrapper').show();
              jQuery('.amount-panel.tab-content').hide();
            } else {
              jQuery('.amount-panel.tab-content').show();
              jQuery('.tab-pane.active p').show();
              jQuery('#other_amount_tab').val('0');
              jQuery('.amount_d').show();
              jQuery('.other-wrapper').hide();
            }

            jQuery(".onetime_currency_code").text($symbol);
            jQuery(".onetime_values").text($price);
            jQuery('#final_amount').val($price);
            jQuery('.onetime_values').text($price);
            document.getElementById("arrow").style.left = lval;
          });
          jQuery('.amount-panel').removeClass("mk-amount-panel");
          jQuery('.make-donation-btn').on('click', function(){
            jQuery('.amount-panel').show();
            jQuery('.amount-panel').addClass("mk-amount-panel");
          });

          // Trigger payment-type option.
          var paymt_option = drupalSettings.paymt_option ? drupalSettings.paymt_option : 'one_time';
          if (paymt_option == 'monthly') {
          console.log('triggerd monthly');
            jQuery("#MonthlyPayClick").trigger('click');
          }
          else {
            jQuery("#OneTimePayClick").trigger('click');
          }

//          jQuery('#donate_subscription').val('one_time');
//          jQuery('#final_amount').val(jQuery("#OneTimePay li.active a").find('.current_amount').html());
//          jQuery('.onetime_values').text(jQuery("#OneTimePay li.active a").find('.current_amount').html());

          jQuery('#node_alias').val(jQuery('#alias').val());
          jQuery('#currency_code').val(jQuery('#curr_code').val());
        });
      }
    }
  })($, Drupal, drupalSettings);


  function validate(evt) {
    var theEvent = evt || window.event;

    // Handle paste
    if (theEvent.type === 'paste') {
      key = event.clipboardData.getData('text/plain');
    } else {
      // Handle key press
      var key = theEvent.keyCode || theEvent.which;
      key = String.fromCharCode(key);
    }
    var regex = /[0-9]|\./;
    if (!regex.test(key)) {
      theEvent.returnValue = false;
      if (theEvent.preventDefault)
        theEvent.preventDefault();
    }
  }
