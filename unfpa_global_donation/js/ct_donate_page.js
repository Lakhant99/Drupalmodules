(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.unfpa_global_donation = {
    attach: function (context, settings) {
      jQuery('body', context).once('unfpa_global_donation').each(function (index) {
        document.body.classList.add("donate-page");

        //Js for expand/collapse content.

        // Hero image
        var dp_hero_image = drupalSettings.dp_hero_image;
        var dp_mobile_hero_image = drupalSettings.dp_mobile_hero_image;
        var dp_large_desk_img = drupalSettings.dp_large_desk_img;
        var dp_medium_desk_img = drupalSettings.dp_medium_desk_img;
        var dp_iPad_mobile_img = drupalSettings.dp_iPad_mobile_img;
        var dp_effect_type = drupalSettings.dp_effect_type;

        var addRemoveImage = function () {
          var ww = document.body.clientWidth;
          var wh = document.body.clientHeight;

          //With effect images
          if (dp_effect_type == 'withEffect') {
            if (ww < 640) {
              jQuery('.hero-image.withEffect').css({'background': 'url(' + dp_mobile_hero_image + ') center 20% no-repeat #666'});
            }
            else if (ww >= 640 && ww <= 768) {
              jQuery('.hero-image.withEffect').css({'background': 'url(' + dp_iPad_mobile_img + ') center 20% no-repeat #666'});
            }
            if (ww > 768 && ww < 1440) {
              jQuery('.hero-image.withEffect').css({'background': 'url(' + dp_hero_image + ') center 20% no-repeat #666'});
            }
            else if (ww >= 1440) {
              jQuery('.hero-image.withEffect').css({'background': 'url(' + dp_hero_image + ') center 20% no-repeat #666'});
            }
          }
          else if (dp_effect_type == 'withoutHeader') {
            if (ww < 640) {
              jQuery('.withoutHeader').prop('src', dp_mobile_hero_image);
            }
            else if (ww >= 640 && ww <= 768) {
              jQuery('.withoutHeader').prop('src', dp_iPad_mobile_img);
            }
            else if (ww > 768 && ww < 1440) {
              jQuery('.withoutHeader').prop('src', dp_medium_desk_img);
            }
            else if (ww >= 1440) {
              jQuery('.withoutHeader').prop('src', dp_hero_image);
            }
          }
          else {
            // Without effect images
            if (ww < 640) {
              jQuery('.hero-image.withoutEffect').css({'background': 'url(' + dp_mobile_hero_image + ') center 0 no-repeat'});
            }
            else if (ww >= 640 && ww <= 768) {
              jQuery('.hero-image.withoutEffect').css({'background': 'url(' + dp_iPad_mobile_img + ') center center no-repeat'});
            }
            else if (ww >= 1024 && wh === 1366) {
              jQuery('.hero-image.withoutEffect').css({'background': 'url(' + dp_iPad_mobile_img + ') center center no-repeat'});
            }
            else if (ww >= 769 && ww < 1440) {
              jQuery('.hero-image.withoutEffect').css({'background': 'url(' + dp_medium_desk_img + ') center center no-repeat'});
            }
            else if (ww > 1440) {
              jQuery('.hero-image.withoutEffect').css({'background': 'url(' + dp_large_desk_img + ') center center no-repeat'});
            }
          }
        };

        // load and resize function
        jQuery(window).bind("load resize", function () {
          addRemoveImage();

          var wh = document.body.clientHeight;
          var ww = document.body.clientWidth;

          //width parallax effect image positioning
          if (wh <= 568) {
            jQuery('.hero-image.withEffect').css('height', (500) + 'px');
            jQuery('.withEffect-hero-image').css('top', (400) + 'px');
          }
          if (wh <= 640 && wh > 568) {
            jQuery('.hero-image.withEffect').css('height', (520) + 'px');
            jQuery('.withEffect-hero-image').css('top', (400) + 'px');
          }
          else if (wh >= 640 && wh < 767) {
            jQuery('.hero-image.withEffect').css('height', (600) + 'px');
            jQuery('.withEffect-hero-image').css('top', (450) + 'px');
          }
          else if (wh <= 667 && ww > 640) {
            jQuery('.hero-image.withEffect').css('height', (620) + 'px');
            jQuery('.withEffect-hero-image').css('top', (520) + 'px');
          }
          else if (wh <= 823 && wh > 667) {
            jQuery('.hero-image.withEffect').css('height', (wh / 1.2) + 'px');
            jQuery('.withEffect-hero-image').css('top', (wh / 1.6) + 'px');
          }
          else if (wh >= 768 && wh < 800) {
            jQuery('.hero-image.withEffect').css('height', (wh / 1.18) + 'px');
            jQuery('.withEffect-hero-image').css('top', (wh / 1.45) + 'px');
          }
          else if (wh >= 800 && wh <= 1024) {
            jQuery('.hero-image.withEffect').css('height', (wh / 1.18) + 'px');
            jQuery('.withEffect-hero-image').css('top', (wh / 1.48) + 'px');
          }
          else if (wh >= 1080) {
            jQuery('.hero-image.withEffect').css('height', (wh / 1.28) + 'px');
            jQuery('.withEffect-hero-image').css('top', (wh / 1.55) + 'px');
          }

          //Without parallax effect image positioning
          if (wh <= 640) {
            jQuery('.hero-image.withoutEffect').css('height', (500) + 'px');
          }
          else if (wh >= 640 && wh < 767) {
            jQuery('.hero-image.withoutEffect').css('height', (500) + 'px');
          }
          else if (wh <= 667 && ww <= 411) {
            jQuery('.hero-image.withoutEffect').css('height', (460) + 'px');
          }
          else if (wh <= 667 && ww > 640) {
            jQuery('.hero-image.withoutEffect').css('height', (520) + 'px');
          }
          else if (wh < 768 && wh > 667) {
            jQuery('.hero-image.withoutEffect').css('height', (wh / 1.5) + 'px');
          }
          else if (wh <= 823 && wh > 667) {
            jQuery('.hero-image.withoutEffect').css('height', (wh / 1.5) + 'px');
          }
          else if (wh >= 768 && wh < 1079) {
            jQuery('.hero-image.withoutEffect').css('height', (wh / 1.4) + 'px');
          }
          else if (wh >= 1080) {
            jQuery('.hero-image.withoutEffect').css('height', (wh / 1.3) + 'px');
          }
        });

      });
    }
  };
})($, Drupal, drupalSettings);
