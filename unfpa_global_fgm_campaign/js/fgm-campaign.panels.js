/**
 * @file
 * UNFPA Global FGM Campaign panels section js logic.
 */
(function ($) {




/**
 * Panels section behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignPanels = {
  attach: function (context, settings) {
    $('.fgm-panels', context).once('unfpaGlobalFgmCampaignPanels').each(function () {
      var $window = $(window);
      var $document = $(document);
      var $wrapper = $(this);
      var wrapper_dom = $wrapper.get(0);
      var $lock = $wrapper.find('.fgm-panels-lock');
      var $inner = $wrapper.find('.fgm-panels-inner');
      var $panels = $wrapper.find('.fgm-panel');
      var already_animated = false;

      // If we are non-desktop touch device then keep hover class.
      if (Drupal.unfpaGlobalFgmCampaign.isTouchMobile()) {
        $panels.find('a').addClass('fgm-hover');
      }

      // Animate function.
      var animate = function() {
        if (Drupal.unfpaGlobalFgmCampaign.isTouchMobile()) {
          return;
        }

        var postop = $wrapper.offset().top;
        var height = $wrapper.innerHeight();
        var scrolltop = $document.scrollTop();

        if ((postop <= scrolltop) && (postop + height > scrolltop) && !already_animated) {
          already_animated = true;

          var delay_in = 100;
          var delay_out = 2000;

          $panels.each(function () {
            var $panel = $(this);
            var $a = $panel.find('a');

            setTimeout(function() {
              $a.addClass('fgm-hover');
            }, delay_in);

            setTimeout(function () {
              $a.removeClass('fgm-hover');
            }, delay_out);

            delay_in = delay_in + 300;
            delay_out = delay_out + 300;
          });
        }
      };

      // Deanimate function.
      var deanimate = function() {
        already_animated = false;
      };

      // Panels section locking disabling/enabling.
      var locking_enable_disable = function() {
        // If bigger viewport then enable locking.
        if (Drupal.unfpaGlobalFgmCampaign.getViewportWidth() >= 850) {
          var viewport_height = Drupal.unfpaGlobalFgmCampaign.getViewportHeight();
          var inner_height = $inner.outerHeight();
          if (inner_height > viewport_height) {
            $wrapper.css('height', (inner_height * 1.7) + 'px');
          }
          else {
            $wrapper.css('height', '170vh');
          }
        }
        // If smaller viewport then disable locking.
        else {
          $wrapper.css('height', 'auto');
          $lock.css({'position': 'static'});
        }
      };

      // Panels section locking function.
      var locking_react = function() {
        // Locking is for bigger viewports only.
        if (!(Drupal.unfpaGlobalFgmCampaign.getViewportWidth() >= 850)) {
          return;
        }

        var postop = $wrapper.offset().top;
        var height = $wrapper.innerHeight();
        var inner_height = $inner.innerHeight();
        var scrolltop = $document.scrollTop();

        if ((postop <= scrolltop) && (postop + height - inner_height > scrolltop)) {
          $lock.css({'position': 'fixed', 'top': 0, 'bottom': 'auto'});
        }
        else if ((postop <= scrolltop) && (postop + height > scrolltop)) {
          $lock.css({'position': 'absolute', 'top': 'auto', 'bottom': 0});
        }
        else {
          $lock.css({'position': 'static'});
        }
      };

      // On load.
      $window.on('load',function () {
        // Locking.
        locking_enable_disable();
        window.requestAnimationFrame(locking_react);

        // Animate / deanimate.
        if (Drupal.unfpaGlobalFgmCampaign.isElementVisible(wrapper_dom, 0)) {
          window.requestAnimationFrame(animate);
        }
        else {
          window.requestAnimationFrame(deanimate);
        }
      });

      // Do stuff on window scroll.
      $window.scroll(function () {
        // Locking.
        window.requestAnimationFrame(locking_react);

        // Animate / deanimate.
        if (Drupal.unfpaGlobalFgmCampaign.isElementVisible(wrapper_dom, 0)) {
          window.requestAnimationFrame(animate);
        }
        else {
          window.requestAnimationFrame(deanimate);
        }
      });

      // Do stuff on window resize.
      var resize_timeout = null;
      $window.resize(function () {
        clearTimeout(resize_timeout);
        resize_timeout = setTimeout(function () {
          // Locking.
          locking_enable_disable();
          window.requestAnimationFrame(locking_react);

          // Animate / deanimate.
          if (Drupal.unfpaGlobalFgmCampaign.isElementVisible(wrapper_dom, 0)) {
            window.requestAnimationFrame(animate);
          }
          else {
            window.requestAnimationFrame(deanimate);
          }
        }, 100);
      });
    });
  }
};



})(jQuery);
