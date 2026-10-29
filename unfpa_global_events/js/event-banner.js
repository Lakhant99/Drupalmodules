(function ($, Drupal) {
  Drupal.behaviors.unfpaGlobalEvent = {
    attach: function (context, drupalSettings) {
      $('body', context).once('unfpaGlobalEvent').each(function () {
        // Add class to body tag.
        document.body.classList.add("event-page");
        // Hero image
        var fieldBannerImageLarge = drupalSettings.field_banner_image_large;
        var fieldBannerImageMedium = drupalSettings.field_banner_image_medium;
        var fieldBannerImageMobileIpad = drupalSettings.field_banner_image_mobile_ipad;
        var fieldBannerImageMobile = drupalSettings.field_banner_image_mobile;

        var addRemoveImage = function () {
          var windowWidth = document.body.clientWidth;
          // Without effect hero images
          if (windowWidth < 640 && fieldBannerImageMobile) {
            $('.hero-image.withoutEffect').css({ 'background': 'url(' + fieldBannerImageMobile + ') center 0 no-repeat' });
          } else if ((windowWidth >= 640 && windowWidth <= 768) && fieldBannerImageMobileIpad) {
            $('.hero-image.withoutEffect').css({ 'background': 'url(' + fieldBannerImageMobileIpad + ') center center no-repeat' });
          } else if ((windowWidth >= 769 && windowWidth < 1440) && fieldBannerImageMedium) {
            $('.hero-image.withoutEffect').css({ 'background': 'url(' + fieldBannerImageMedium + ') center center no-repeat' });
          } else if (windowWidth >= 1440 && fieldBannerImageLarge) {
            $('.hero-image.withoutEffect').css({ 'background': 'url(' + fieldBannerImageLarge + ') center center no-repeat' });
          }
          else {
            $('.hero-image.withoutEffect').hide();
          }
        };

        // load and resize function.
        $(window).bind("load resize", function () {
          addRemoveImage();
          var windowHeight = document.body.clientHeight;
          var windowWidth = document.body.clientWidth;
          //widthout parallax effect image positioning
          if (windowHeight <= 640) {
            $('.hero-image.withoutEffect').css('height', (500) + 'px');
            $('.withoutEffect-hero-image').css('top', -(50) + 'px');
          } else if (windowHeight >= 640 && windowHeight < 767) {
            $('.hero-image.withoutEffect').css('height', (500) + 'px');
            $('.withoutEffect-hero-image').css('top', -(25) + 'px');
          } else if (windowHeight <= 667 && windowWidth <= 411) {
            $('.hero-image.withoutEffect').css('height', (460) + 'px');
            $('.withoutEffect-hero-image').css('top', -(25) + 'px');
          } else if (windowHeight <= 667 && windowWidth > 640) {
            $('.hero-image.withoutEffect').css('height', (520) + 'px');
            $('.withoutEffect-hero-image').css('top', -(50) + 'px');
          } else if (windowHeight < 768 && windowHeight > 667) {
            $('.hero-image.withoutEffect').css('height', (windowHeight / 1.5) + 'px');
            $('.withoutEffect-hero-image').css('top', -(35) + 'px');
          } else if (windowHeight <= 823 && windowHeight > 667) {
            $('.hero-image.withoutEffect').css('height', (windowHeight / 1.5) + 'px');
            $('.withoutEffect-hero-image').css('top', -(15) + 'px');
          } else if (windowHeight <= 823 && windowHeight > 667 && windowWidth <= 411) {
            $('.withoutEffect-hero-image').css('top', -(15) + 'px');
          } else if (windowHeight >= 768 && windowHeight < 1079) {
            $('.hero-image.withoutEffect').css('height', (windowHeight / 1.4) + 'px');
            $('.withoutEffect-hero-image').css('top', -(25) + 'px');
          } else if (windowHeight >= 1080) {
            $('.hero-image.withoutEffect').css('height', (windowHeight / 1.3) + 'px');
            $('.withoutEffect-hero-image').css('top', -(50) + 'px');
          }

        });

        // Parallax image effect
        function parallax() {
          var scrolled = $(window).scrollTop();
          $('.hero-image.withEffect').css('top', -(scrolled * 0.0315) + 'rem');
          $('.hero-image.withEffect').css('opacity', 1 - (scrolled * .00095));
          $('.hero-image.withEffect > .hero-image__content').css('top', +(scrolled * -0.015) + 'rem');
          $('.hero-image.withEffect > .hero-image__content').css('opacity', 1 - (scrolled * .00175));
        };
        $(window).bind("load resize", function () {
          $(window).scroll(function (e) {
            parallax();
          });
        });

      });// EOF once each function.

      // Document download behaviour
      $(".main-document", context).change(function () {
        var selectedValue = $(this).children("option:selected").val();
        var targetElement = $(this).parent().parent().next().find("a");
        targetElement.attr("href", selectedValue);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
