/**
 * @file
 * UNFPA Global FGM Campaign Navigation logic.
 */
(function ($) {




/**
 * Navigation behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignNav = {
  attach: function (context, settings) {
    $('.fgm-nav', context).once('unfpaGlobalFgmCampaignNav').each(function () {
      var $window = $(window);
      var $document = $(document);
      var $nav = $(this);
      var $nav_bar = $nav.find('.fgm-navbar');

      /* --- Nav Bar Reacting On Scrolling  --- */

      var $controls = $nav.find('.fgm-nav-controls');
      var $entry = $('.fgm-entry');
      var $subpage_header = $('.fgm-subpage-header');

      // Function that shows/hides skip link when needed.
      var toggle_skip_link = function() {
        if ($controls.length == 0 || $entry.length == 0) {
          return;
        }

        var entry_postop = $entry.offset().top;
        var entry_height = $entry.innerHeight();
        var scrolltop = $document.scrollTop();

        if ((entry_postop <= scrolltop) && (entry_postop + entry_height > scrolltop)) {
          $controls.stop().fadeIn(300);
        }
        else {
          $controls.stop().fadeOut(300);
        }
      };

      // Function that changes nav background color class.
      var toggle_nav_background = function() {
        // Get html element where nav should be transparent.
        var $element = $entry;
        if ($subpage_header.length) {
          $element = $subpage_header;
        }

        var element_postop = $element.offset().top;
        var element_height = $element.innerHeight();
        var scrolltop = $document.scrollTop();

        if ((element_postop + element_height > scrolltop)) {
          $nav_bar.addClass('fgm-navbar-transparent');
        }
        else {
          $nav_bar.removeClass('fgm-navbar-transparent');
        }
      };

      // React function, called on load, on scroll and on resize.
      var nav_react = function() {
        window.requestAnimationFrame(function() {
          toggle_skip_link();
          toggle_nav_background();
        });
      };

      $window.on('load',function () {
        nav_react();
      });

      // Do stuff on window scroll.
      $window.scroll(function () {
        nav_react();
      });

      // Do stuff on window resize.
      var resize_timeout = null;
      $window.resize(function () {
        clearTimeout(resize_timeout);
        resize_timeout = setTimeout(function () {
          nav_react();
        }, 100);
      });

      /* --- Nav Menu Opening / Clicking  --- */

      var $expand = $nav.find('.fgm-nav-expand');
      var $menu = $nav.find('.fgm-menu');
      var $menu_links = $menu.find('a');
      var $nav_skip = $nav.find('.fgm-nav-skip');

      $expand.click(function() {
        if ($expand.hasClass('fgm-nav-close')) {
          $expand.removeClass('fgm-nav-close');
          $menu.removeClass('fgm-menu-opened');
          $nav_bar.removeClass('fgm-navbar-menu-opened');
        }
        else {
          $expand.addClass('fgm-nav-close');
          $menu.addClass('fgm-menu-opened');
          $nav_bar.addClass('fgm-navbar-menu-opened');
        }
      });

      // Close menu on link click.
      $menu_links.click(function() {
        $expand.removeClass('fgm-nav-close');
        $menu.removeClass('fgm-menu-opened');
        $nav_bar.removeClass('fgm-navbar-menu-opened');
      });

      // Also close menu on skip link click.
      $nav_skip.click(function() {
        $expand.removeClass('fgm-nav-close');
        $menu.removeClass('fgm-menu-opened');
        $nav_bar.removeClass('fgm-navbar-menu-opened');
      });
    });
  }
};




})(jQuery);
