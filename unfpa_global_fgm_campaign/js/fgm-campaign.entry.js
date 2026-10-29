/**
 * @file
 * UNFPA Global FGM Campaign entry section js logic.
 */
(function ($) {




/**
 * Entry section behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignEntry = {
  attach: function (context, settings) {
    $('.fgm-entry', context).once('unfpaGlobalFgmCampaignEntry').each(function () {
      var $window = $(window);
      var $document = $(document);
      var $entry = $(this);
      var $entry_dom = $entry.get(0);
      var $triggers = $entry.find('.fgm-entry-triggers span.has-scene');
      var $stick = $entry.find('.fgm-entry-stick');
      var resize_timeout;

      // Cache scenes items and their animated children.
      var $scenes = $entry.find('.fgm-entry-scene');
      var $scene_copies = $scenes.find('.fgm-entry-copy');
      var $scene_tints = $scenes.find('.fgm-entry-tint');
      var $scene_bgs = $scenes.find('.fgm-entry-bg');

      // Scene #1 specific.
      var $scene_1_title = $scenes.find('.fgm-entry-title');
      var $scene_1_downlabel = $scenes.find('.fgm-entry-downlabel');
      var $scene_1_arrow = $scenes.find('.fgm-entry-downarrow');

      // Animate on scroll.
      var entry_animate = function () {
        var entry_postop = $entry.offset().top;
        var entry_height = $entry.innerHeight();
        var scrolltop = $document.scrollTop();

        if ((entry_postop <= scrolltop) && (entry_postop + entry_height > scrolltop)) {
          $stick.css({'position': 'fixed'});
        }
        else {
          $stick.css({'position': 'absolute', 'top': 0, 'bottom': 'auto'});
        }

        $triggers.each(function (index) {
          var $trigger = $(this);
          var postop = $trigger.offset().top;
          var height = $trigger.height();

          // Get all needed scene data/items.
          var $scene = $scenes.eq(index);
          var $copy = $scene_copies.eq(index);
          var $tint = $scene_tints.eq(index);
          var $bg = $scene_bgs.eq(index);

          // Animate scenes 2-4.
          if (index !== 0) {
            // Animate scene incoming.
            if (postop + 10 < scrolltop) {
              $scene.addClass('fgm-animated');
            }
            else {
              $scene.removeClass('fgm-animated');
            }

            // Animate scene bg.
            var opacity = Drupal.unfpaGlobalFgmCampaign.calculateShift(postop, (postop + height * 0.25), scrolltop, 0, 1);
            $bg.css('opacity', opacity);

            // Animate scene copy.
            var opacity = Drupal.unfpaGlobalFgmCampaign.calculateShift((postop + height * 0.25), (postop + height * 0.5), scrolltop, 0, 1);
            if (scrolltop > (postop + height * 0.75)) {
              opacity = Drupal.unfpaGlobalFgmCampaign.calculateShift((postop + height * 0.75), (postop + height * 0.9), scrolltop, 1, 0);
            }
            $copy.css('opacity', opacity);
          }

          // Animate scene 1.
          else {
            var opacity = 1;
            if (scrolltop > (postop + height * 0.5)) {
              opacity = Drupal.unfpaGlobalFgmCampaign.calculateShift((postop + height * 0.5), (postop + height * 0.9), scrolltop, 1, 0);
            }
            $scene_1_title.css('opacity', opacity);
            $copy.css('opacity', opacity);

            // Animate scene arrow.
            $scene_1_downlabel.css('opacity', opacity);
            $scene_1_arrow.css('opacity', opacity);
          }

          // Animate scene tint.
          var opacity = Drupal.unfpaGlobalFgmCampaign.calculateShift((postop + height * 0.75), (postop + height * 1), scrolltop, 0.4, 0.9);
          $tint.css('opacity', opacity);
        });
      };

      // React function called on load, scroll, resize, etc.
      var entry_react = function() {
        window.requestAnimationFrame(entry_animate);
      };

      // On load.
      $entry.addClass('fgm-entry-js');
      $window.on('load',function () {
        entry_react();
      });

      // Do stuff on window scroll.
      $window.scroll(function () {
        entry_react();
      });

      // Do stuff on window resize.
      $window.resize(function () {
        clearTimeout(resize_timeout);
        resize_timeout = setTimeout(function () {
          entry_react();
        }, 100);
      });

      /* --- Skip Function --- */

      // Skip link.
      $('.fgm-nav-skip').click(function () {
        var scroll_to = $('.fgm-after-entry').offset().top + 1;

        $('html, body').animate({
          scrollTop: scroll_to
          }, {
          // Keep duration 0 so it doesn't chop in safari mobile.
          duration: 0
        });
      });
    });
  }
};



})(jQuery);
