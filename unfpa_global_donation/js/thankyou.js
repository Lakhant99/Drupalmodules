(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.thankyou = {
    attach: function (context, settings) {
      jQuery('body', context).once('thankyou').each(function (index) {
        document.body.classList.add("donate-page");

        // Hero image
        var thankyou_image = drupalSettings.thankyou_image;

        var addRemoveImage = function () {
          var ww = document.body.clientWidth;

          //Without effect images
          if (ww < 640) {
            jQuery('.hero-image.withoutEffect').css({'background': 'url(' + thankyou_image + ') center 0 no-repeat'});
          }
          else if (ww >= 640 && ww <= 768) {
            jQuery('.hero-image.withoutEffect').css({'background': 'url(' + thankyou_image + ') center center no-repeat'});
          }
          if (ww >= 769 && ww < 1440) {
            jQuery('.hero-image.withoutEffect').css({'background': 'url(' + thankyou_image + ') center center no-repeat'});
          }
          else if (ww >= 1440) {
            jQuery('.hero-image.withoutEffect').css({'background': 'url(' + thankyou_image + ') center center no-repeat'});
          }
        };

        // load and resize function
        jQuery(window).bind("load resize", function () {
          addRemoveImage();

          var wh = document.body.clientHeight;
          var ww = document.body.clientWidth;

          //width parallax effect image positioning
          if (wh <= 640) {
            jQuery('.hero-image.withoutEffect').css('height', (500) + 'px');
            jQuery('.withoutEffect-hero-image').css('top', -(50) + 'px');
          }
          else if (wh >= 640 && wh < 767) {
            jQuery('.hero-image.withoutEffect').css('height', (500) + 'px');
            jQuery('.withoutEffect-hero-image').css('top', -(25) + 'px');
          }
          else if (wh <= 667 && ww <= 411) {
            jQuery('.hero-image.withoutEffect').css('height', (460) + 'px');
            jQuery('.withoutEffect-hero-image').css('top', -(25) + 'px');
          }
          else if (wh <= 667 && ww > 640) {
            jQuery('.hero-image.withoutEffect').css('height', (520) + 'px');
            jQuery('.withoutEffect-hero-image').css('top', -(25) + 'px');
          }
          else if (wh < 768 && wh > 667) {
            $('.hero-image.withoutEffect').css('height', (wh / 1.5) + 'px');
            $('.withoutEffect-hero-image').css('top', -(35) + 'px');
          }
          else if (wh <= 823 && wh > 667) {
            jQuery('.hero-image.withoutEffect').css('height', (wh / 1.5) + 'px');
            jQuery('.withoutEffect-hero-image').css('top', -(15) + 'px');
          }
          else if (wh <= 823 && wh > 667 && ww <= 411) {
            $('.withoutEffect-hero-image').css('top', -(15) + 'px');
          }
          else if (wh >= 768 && wh < 1079) {
            $('.hero-image.withoutEffect').css('height', (wh / 1.4) + 'px');
            $('.withoutEffect-hero-image').css('top', -(25) + 'px');
          }
          else if (wh >= 1080) {
            $('.hero-image.withoutEffect').css('height', (wh / 1.3) + 'px');
            $('.withoutEffect-hero-image').css('top', -(50) + 'px');
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
