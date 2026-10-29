(function ($, Drupal, drupalSettings) {
  "use strict";

  Drupal.behaviors.extra = {
    attach: function (context, settings) {
      jQuery('#procurement-supplychain-wrapper', context).once('load').each(function (index) {
        var ww = document.body.clientWidth;
        if (ww < 640) {
          console.log('entered if');
          jQuery('.hero-image').attr('data-image-src', drupalSettings.mobile_image);
          jQuery('.hero-image').css('background-image', 'url(' + drupalSettings.mobile_image + ')');
        } else {
          jQuery('.hero-image').attr('data-image-src', drupalSettings.banner_image);
          jQuery('.hero-image').css('background-image', 'url(' + drupalSettings.banner_image + ')');
        }
      });

      jQuery('.popup-wrapper', context).once('exta').each(function () {
        // Set share card image
        var hero_image = jQuery('.hero-image').data('image-src');
        if (hero_image != null) {
          jQuery('.share-card .img-holder img').attr('src', hero_image);
        }
      });
    }
  }
})($, Drupal, drupalSettings);