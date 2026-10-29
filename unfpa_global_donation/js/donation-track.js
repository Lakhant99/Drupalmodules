/**
 * @file
 *
 * Add donation tracking after payment is success.
 */
(function ($, Drupal, drupalSettings, window) {
  Drupal.behaviors.donationTrack = {
    attach: function (context, drupalSettings) {
      var donationInfo = drupalSettings.donationData;
      var price = Math.round(donationInfo.amount / 100).toFixed(2);
      $(window, context).once("donationTrack").each(function() {
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
          'transactionId': donationInfo.transaction_id,
          'transactionTotal': price,
          'transactionProducts': [{
            'name': donationInfo.name,
            'price': price,
            'quantity': 1,
          }],
        });
      });
    }
  }
})(jQuery, Drupal, drupalSettings, window);
