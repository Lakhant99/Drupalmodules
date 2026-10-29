/**
* @file
* General logic used by this module.
*/
(function ($) {



/**
 * Initialize Drupal.sowp object if it doesn't exist.
 */
Drupal.sowp = Drupal.sowp || {};



/**
 * Additional global variables.
 */
Drupal.sowp.timers = {
  equalHeightStories: null
};



/**
 * Measures viewport width instead of document width.
 * Document width would return width without scrollbar.
 */
Drupal.sowp.viewportWidth = function () {
  var e = window;
  var a = 'inner';

  if (!('innerWidth' in window)) {
    a = 'client';
    e = document.documentElement || document.body;
  }

  return e[a + 'Width'];
};



/**
 * Device type detection.
 */
Drupal.sowp.deviceCheck = {
  init: function () {
    window.isMobile = this.isMobile();
    window.isIpad = this.isIpad();
    window.isDesktop = this.isDesktop();
  },
  isMobile: function () {
    return (jQuery.browser.mobile ? true : false);
  },
  isIpad: function () {
    return (navigator.userAgent.match(/iPad/i) != null);
  },
  isDesktop: function () {
    return (jQuery.browser.mobile || Drupal.sowp.deviceCheck.isIpad() ? false : true);
  },
  is768plus: function () {
    return (Drupal.sowp.viewportWidth() > 768);
  },
  backgroundSize: function () {
    return (Modernizr.backgroundsize ? true : false);
  }
};



/**
 * Call device detection right away.
 */
Drupal.sowp.deviceCheck.init();



/**
 * Equal height.
 */
Drupal.sowp.equalHeight = function ($items) {
  if ($items.length > 0) {
    // First reset whatever might have been previously set.
    $items.css({'min-height': 0});

    // Work out equal height.
    var top_height = 0;
    $items.each(function () {
      var current_height = $(this).outerHeight();
      if (top_height < current_height) {
        top_height = current_height;
      }
    });
    $items.css({'min-height': top_height});
  }
};



/**
 * Featured stories equal height.
 */
Drupal.sowp.equalHeightStories = function () {
  clearTimeout(Drupal.sowp.timers.equalHeightStories);

  Drupal.sowp.timers.equalHeightStories = setTimeout(function () {
    $('.swp-stories').each(function () {
      var $items = $(this).find('.stories-list .js-equal-height');

      // Now match height of all items.
      if (Drupal.sowp.deviceCheck.is768plus()) {
        Drupal.sowp.equalHeight($items);
      }
    });
  }, 200);
};



/**
 * Social sites sharing popup.
 */
Drupal.sowp.sharePopup = function (url) {
  // Setup popup window.
  var width = 600;
  var height = 600;

  var opts =
    'width=' + width + ',height=' + height +
    ',toolbars=0,scrollbars=1,resizable=1';

  // Open popup window.
  window.open(url, Drupal.t('Sharing'), opts);
};



/**
 * Checks if element is vertically within the viewport.
 *
 * 'el' param - is DOM element, not jQuery one.
 *
 * 'delay' param - allows to to delay (in pixels) the moment when el becomes
 * treated as visible/invisible in viewport.
 *
 * http://stackoverflow.com/a/21627295
 */
Drupal.sowp.elemIsVisible = function (el, delay) {
  var top = el.getBoundingClientRect().top;
  var bottom = el.getBoundingClientRect().bottom;

  // Take into account that this object might be not visible
  // because of parent container overflow hidden.
  var rect;
  el = el.parentNode;

  do {
    rect = el.getBoundingClientRect();
    if (top <= rect.bottom === false) {
      return false;
    }
    el = el.parentNode;
  }
  while (el != document.body);

  // If object is visible in its parent container then return regular calculation.
  return top + delay <= document.documentElement.clientHeight && bottom >= 0 + delay;
};



/**
 * Check if image has loaded.
 */
Drupal.sowp.checkImageLoaded = function (img) {
  return img.complete && img.naturalHeight !== 0;
};



/**
 * Get random integer within a defined range.
 */
Drupal.sowp.getRandomInt = function (min, max) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
};



/* --- Global Behaviours --- */



/**
 * SOWP Initialize.
 */
Drupal.behaviors.sowpInitialize = {
  attach: function (context, settings) {
    $('.swp', context).once('sowpInitialize').each(function() {
      var $swp = $(this);

      // Add class to body (used to enable css transitions upon full page load).
      $swp.addClass('swp-loaded');

      // Set device class to .swp (used by css).
      if (window.isDesktop) {
        $swp.addClass('swp-desktop');
      }
      else {
        $swp.addClass('swp-mobile');
      }
    });
  }
};



/**
 * Navigation on load animation and on scroll fade in.
 */
Drupal.behaviors.sowpNavAnimate = {
  attach: function (context, settings) {
    $('.swp-nav', context).once('sowpNavAnimate').each(function() {
      var $nav = $(this);
      var $swp = $('.swp');
      var $links = $nav.find('ul a');
      var cls = 'js-hover';
      var already_animated = false;

      // Navigation on load animation using transitions for report 2015.
      if (!$swp.hasClass('2016')) {
        setTimeout(function () {
          $links.eq(0).addClass(cls)
        }, 1000);
        setTimeout(function () {
          $links.eq(1).addClass(cls)
        }, 1100);
        setTimeout(function () {
          $links.eq(2).addClass(cls)
        }, 1200);
        setTimeout(function () {
          $links.eq(3).addClass(cls)
        }, 1300);
        setTimeout(function () {
          $links.eq(4).addClass(cls)
        }, 1400);
        setTimeout(function () {
          $links.eq(0).removeClass(cls)
        }, 1500);
        setTimeout(function () {
          $links.eq(1).removeClass(cls)
        }, 1600);
        setTimeout(function () {
          $links.eq(2).removeClass(cls)
        }, 1700);
        setTimeout(function () {
          $links.eq(3).removeClass(cls)
        }, 1800);
        setTimeout(function () {
          $links.eq(4).removeClass(cls)
        }, 1900);
      }

      var animate_onshow = function() {
        if (already_animated) {
          return;
        }
        setTimeout(function () {
          $links.eq(0).addClass(cls)
        }, 1000);
        setTimeout(function () {
          $links.eq(1).addClass(cls)
        }, 1100);
        setTimeout(function () {
          $links.eq(2).addClass(cls)
        }, 1200);
        setTimeout(function () {
          $links.eq(3).addClass(cls)
        }, 1300);
        setTimeout(function () {
          $links.eq(4).addClass(cls)
        }, 1400);
        setTimeout(function () {
          $links.eq(5).addClass(cls)
        }, 1500);
        setTimeout(function () {
          $links.eq(0).removeClass(cls)
        }, 2000);
        setTimeout(function () {
          $links.eq(1).removeClass(cls)
        }, 2100);
        setTimeout(function () {
          $links.eq(2).removeClass(cls)
        }, 2200);
        setTimeout(function () {
          $links.eq(3).removeClass(cls)
        }, 2300);
        setTimeout(function () {
          $links.eq(4).removeClass(cls)
        }, 2400);
        setTimeout(function () {
          $links.eq(5).removeClass(cls)
        }, 2500);
      };

      // Navigation on scroll fade in and fade out (2016 and 2017).
      if ($swp.hasClass('swp-2016')) {
        var nav_displayed = false;
        var $trigger_element = $('.js-nav-fadein').eq(0);
        var $window = $(window);

        // Handles checking if nav should fade in or out, and does so.
        var nav_fading = function() {
          var window_scroll_top = $window.scrollTop();
          var element_scroll_top = $trigger_element.offset().top;

          if (window_scroll_top > element_scroll_top && !nav_displayed) {
            $nav.fadeIn();
            animate_onshow();
            nav_displayed = true;
          }
          else if (element_scroll_top > window_scroll_top && nav_displayed) {
            $nav.fadeOut();
            nav_displayed = false;
          }
        };

        $window
          .on('load', nav_fading)
          .scroll(nav_fading)
          .resize(nav_fading);
      }
    });
  }
};



/**
 * Social links behaviour.
 */
Drupal.behaviors.sowpSocialLinks = {
  attach: function (context, settings) {
    $('.swp-share a', context).once('sowpSocialLinks').each(function() {
      var $lnk = $(this);

      // If we are here then js is working in browser - remove target _blank attribute
      // and replace the value of href so these links use only their onclick attribute.
      $lnk.attr({'target': '', 'href': 'javascript:;'});
    });
  }
};



/**
 * Back to top link behaviour.
 */
Drupal.behaviors.sowpBackToTop = {
  attach: function (context, settings) {
    $('.swp-gotop', context).once('sowpBackToTop').each(function() {

      var $link = $(this);
      var $window = $(window);
      var $swp = $('.swp');

      if (!$swp.hasClass('swp-2017')) {
        function showHide() {
          if ($window.scrollTop() > 200) {
            $link.fadeIn(500);
          }
          else {
            $link.fadeOut(500);
          }
        }

        // Show/hide on load.
        showHide();

        // Window scroll.
        $window.scroll(function () {
          showHide();
        });
      }

      // Links click.
      $link.click(function (e) {
        e.preventDefault();
        $link.blur();

        $('html, body').scrollTop(4000).animate(
          {scrollTop: $('.section-cover').offset().top + 3},
          {duration: 1000, easing: 'easeInOutExpo'}
        );

        // Also remove hash if its there.
        history.pushState("", document.title, window.location.pathname);
      });

      // Links hover effect.
      $link.mouseenter(function () {
        $link.animate({'opacity': 0.1}, 100, function () {
          $link.css({'opacity': 1});
        });
      });
    });
  }
};



/**
 * Featured stories opening and expanding.
 */
Drupal.behaviors.sowpFeatStories = {
  attach: function (context, settings) {
    // Set height.
    $('.goalBanner').css({'height': (($(window).height()) - 150) + 'px'});

    // Featured stories equal height.

    $('html', context).once('sowpFeatStories').each(function() {
      Drupal.sowp.equalHeightStories();
      $(window).resize(function () {
        Drupal.sowp.equalHeightStories();
      });
    });

    // Showing and hiding of featured story details, body expanding.

    $('.swp-stories', context).once('sowpFeatStories').each(function() {
      var $wrapper = $(this);
      var $links_list = $wrapper.find('.stories-list');
      var $links = $wrapper.find('.stories-list button');
      var $stories = $wrapper.find('.swp-story');
      var $story_close = $stories.find('.story-close');

      // Remove thumb image title attribute.
      $links_list.find('img').attr('title', null);

      // Story opening, body scrolling.

      $links.each(function (index) {
        $(this).click(function () {
          $stories.hide();
          $links_list.hide();

          var $story = $stories.eq(index);

          // Story FadeIn.
          $story.fadeIn();

          // Story body scrolling.
          var $body = $story.find('.story-body');
          var $body_inner = $body.find('.story-body-inner');

          var $showlinks_wrap = $story.find('.story-showall');
          var $showlink = $story.find('.story-showall .st-show');
          var $hidelink = $story.find('.story-showall .st-hide');

          $showlinks_wrap.hide();
          $showlink.hide();
          $hidelink.hide();

          if ($body.height() < $body_inner.height()) {
            $showlinks_wrap.show(0);
            $showlink.show(0);
            $showlink.click(function () {
              $body.css({'max-height': 'none'});
              $showlink.hide();
              $hidelink.show(0);
            });
            $hidelink.click(function () {
              $body.attr('style', '');
              $hidelink.hide();
              $showlink.show(0);
              $('body, html').animate({scrollTop: $body.offset().top - 60}, 1);
            });
          }
        });
      });

      // Story closing.
      $story_close.click(function () {
        $stories.fadeOut();
        $links_list.fadeIn();
        $stories.find('.story-body').css({'max-height': ''})
      });
    });

    // Featured story image cycle.

    $('.story-img-slider', context).once('sowpFeatStories').each(function() {
      var $slider = $(this);
      var $slides = $slider.find('.story-images ul');

      if ($slides.find('li').length > 1) {
        // Build controls.
        $slider.append('<a class="imgs-prev color-bg" href="javascript:;" title="' +
          Drupal.t('Previous image') + '"><span class="triangle"></span></a>');
        $slider.append('<a class="imgs-next color-bg" href="javascript:;" title="' +
          Drupal.t('Next image') + '"><span class="triangle"></span></a>');
        $slider.append('<div class="imgs-pager"></div>');

        // Cycle.
        $slides.cycle({
          pauseOnHover: true,
          speed: 1200,
          timeout: 0,
          fx: 'scrollHorz',
          slides: '> li',
          swipe: true,
          autoHeight: 'calc',
          prev: $slider.find('.imgs-prev'),
          next: $slider.find('.imgs-next'),
          pager: $slider.find('.imgs-pager'),
          log: false
        });

        $slider.find('.imgs-pager span').each(function (index) {
          $(this)
            .addClass('color-bg')
            .attr({'title': Drupal.t('Display image no ') + (index + 1)});
        });
      }
    });
  }
};



/**
 * Animate charts onload, onscroll and do things onresize.
 */
Drupal.behaviors.sowpAnimatedCharts = {
  attach: function (context, settings) {
    $('.swp-multisvg', context).once('sowpAnimatedCharts').each(function() {
      var $multisvg = $(this);
      var $svgs = $multisvg.find('img.anim');
      var $svg_1 = $multisvg.find('.anim-base');
      var $svg_2 = $multisvg.find('.anim-grid');
      var $svg_3 = $multisvg.find('.anim-bars');
      var $xlabel = $multisvg.find('.swp-multisvg-xlabel img');
      var timeout;

      // On load stuff.

      if (Drupal.sowp.deviceCheck.is768plus()) {
        $svgs.hide();
        $svg_2.height('0%');
        $svg_3.height('0%');
        $xlabel.css({'opacity': 0});

        // Check if chart is in viewport + 150 pixels.
        setTimeout(function () {
          var is_in_viewport = Drupal.sowp.elemIsVisible($multisvg.get(0), 150);

          if (is_in_viewport) {
            $xlabel.animate({'opacity': 1}, 300);
            $svg_1.fadeIn(300, function () {
              $svg_2.show(0).animate({'height': '100%'}, 800, 'easeOutBounce', function () {
                $svg_3.show(0).animate({'height': '100%'}, 800, 'easeOutCirc');
              });
            });
          }
        }, 200);
      }

      // On Scroll Stuff.

      // Show/hide chart svgs on scroll.
        $(document).scroll(function () {
        if (Drupal.sowp.deviceCheck.is768plus()) {
          clearTimeout(timeout);
          timeout = setTimeout(function () {
            // Check if chart is in viewport + 150 pixels.
            var is_in_viewport = Drupal.sowp.elemIsVisible($multisvg.get(0), 150);

            if (is_in_viewport) {
              $xlabel.animate({'opacity': 1}, 300);
              $svg_1.fadeIn(300, function () {
                $svg_2.show(0).animate({'height': '100%'}, 800, 'easeOutBounce', function () {
                  $svg_3.show(0).animate({'height': '100%'}, 800, 'easeOutCirc');
                });
              });
            }
            // Not in viewport - hide it so it animtes again when comes back into view.
            else if ($svg_2.is(':visible')) {
              // Need to make sure we are already 300px away.
              if (!Drupal.sowp.elemIsVisible($multisvg.get(0), -300)) {
                $svgs.hide();
                $svg_2.height('0%');
                $svg_3.height('0%');
                $xlabel.css({'opacity': 0});
              }
            }
          }, 200);
        }
      });

      // On Resize

      $(document).resize(function () {
        if (!Drupal.sowp.deviceCheck.is768plus()) {
          $svgs.show(0).height('100%');
        }
      });
    });
  }
};



/**
 * Causes animation of specific chart. Used by chart slider to animate on slide change.
 */
Drupal.sowp.animateChart = function ($multisvg, wait) {
  var $svgs = $multisvg.find('img.anim');
  var $svg_1 = $multisvg.find('.anim-base');
  var $svg_2 = $multisvg.find('.anim-grid');
  var $svg_3 = $multisvg.find('.anim-bars');
  var $xlabel = $multisvg.find('.swp-multisvg-xlabel img');

  if (Drupal.sowp.deviceCheck.is768plus()) {
    $svgs.hide();
    $svg_2.height('0%');
    $svg_3.height('0%');
    $xlabel.css({'opacity': 0});

    setTimeout(function () {
      $xlabel.animate({'opacity': 1}, 300);
      $svg_1.fadeIn(300, function () {
        $svg_2.show(0).animate({'height': '100%'}, 800, 'easeOutBounce', function () {
          $svg_3.show(0).animate({'height': '100%'}, 800, 'easeOutCirc');
        });
      });
    }, wait);
  }
};



/**
 * Animated charts prev/next slider.
 */
Drupal.behaviors.sowpChartsSlider = {
  attach: function (context, settings) {
    $('.charts-changer', context).once('sowpChartsSlider').each(function() {
      // jQuqery selectors.
      var $wrapper = $(this);
      var $parent = $wrapper.parents('.swp-chart-charts');
      var $slider = $wrapper.find('.charts-changer-items');
      var $slides = $slider.find('.swp-chart-chart');

      // Cycle Animation.

      if ($slides.length > 1) {
        // Build controls.
        $wrapper.after('<div class="chart-controls"><div class="chart-controls-inner"></div></div>');
        var $controls = $parent.find('.chart-controls-inner');

        var $prev = $('<a class="chart-prev color-text" href="javascript:;" title="' +
          Drupal.t('Previous chart') + '"><span class="icon-go-prev"></span></a>');
        $prev.appendTo($controls);
        var $next = $('<a class="chart-next color-text" href="javascript:;" title="' +
          Drupal.t('Next chart') + '"><span class="icon-go-next"></span></a>');
        $next.appendTo($controls);
        var $pager = $('<div class="chart-pager"></div>');
        $pager.appendTo($controls);

        // Cycle.
        $slider.cycle({
          pauseOnHover: true,
          speed: 1000,
          timeout: 0,
          fx: 'fade',
          slides: '> .swp-chart-chart',
          swipe: true,
          autoHeight: 'calc',
          prev: $prev,
          next: $next,
          pager: $pager,
          log: false
        });

        // Customize pager.
        $pager.find('span').each(function (index) {
          $(this)
            .addClass('color-bg')
            .attr({'title': Drupal.t('Display chart no ') + (index + 1)});
        });

        // Animate charts after slide change.
        $slider.on('cycle-before',
          function (event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag) {
            var $curr_slide = $(incomingSlideEl);
            var $multisvg = $curr_slide.find('.swp-multisvg');
            Drupal.sowp.animateChart($multisvg, 1000);
          }
        );
      }
    });
  }
};



/**
 * Chart node: Image Slider.
 */
Drupal.behaviors.sowpImageSlider = {
  attach: function (context, settings) {
    $('.swp-chart-imgslider', context).once('sowpImageSlider').each(function() {
      // Outer div.
      var $wrapper = $(this);
      // <ul> wrapper div.
      var $slider = $wrapper.find('.swp-imgslider');
      // <ul>.
      var $slides = $slider.find('ul');

      if ($slides.find('li').length > 1) {
        // Build controls.
        $slider.append('<a class="imgs-prev color-text" href="javascript:;" title="' +
          Drupal.t('Previous image') + '"><span class="icon-go-prev"></span></a>');
        $slider.append('<a class="imgs-next color-text" href="javascript:;" title="' +
          Drupal.t('Next image') + '"><span class="icon-go-next"></span></a>');
        $wrapper.append('<div class="imgs-pager"></div>');

        // Create .slide-caption with captions - first caption visible initially.
        $wrapper.append('<div class="imgslider-captions sb-font"></div>');
        var $captions = $wrapper.find('.imgslider-captions');

        $slides.find('li').each(function (index) {
          var caption = $(this).find('.slide-caption-src').html();
          var $cp = $('<div>' + caption + '</div>');

          if (index > 0) {
            $cp.fadeTo(0, 0);
          }

          $captions.append($cp);
        });
        var $caption_divs = $wrapper.find('.imgslider-captions > *');

        // $captions div needs js set min height same as tallest caption div.
        var captionsTimeout;
        var captionsHeight = function () {
          clearTimeout(captionsTimeout);
          captionsTimeout = setTimeout(function () {
            var h = 0;
            $caption_divs.each(function () {
              var h2 = $(this).height();
              if (h2 > h) {
                h = h2;
              }
            });
            $captions.css({'min-height': h + 'px'});
          }, 200);
        };
        captionsHeight();
        $(window).resize(captionsHeight());

        // Cycle.
        $slides.cycle({
          pauseOnHover: true,
          speed: 1200,
          fx: 'scrollHorz',
          slides: '> li',
          swipe: true,
          autoHeight: 'calc',
          prev: $slider.find('.imgs-prev'),
          next: $slider.find('.imgs-next'),
          pager: $wrapper.find('.imgs-pager'),
          log: false
        });

        // Customize pager.
        $wrapper.find('.imgs-pager span').each(function (index) {
          $(this)
            .addClass('color-bg')
            .attr({'title': Drupal.t('Display image no ') + (index + 1)});
        });

        // Switch display of captions after slide changed.
        $slides.on('cycle-after',
          function (event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag) {
            var index = $(incomingSlideEl).attr('data-slide-index');
            $caption_divs.fadeTo(0, 0);
            $caption_divs.eq(index).fadeTo(500, 1);
          }
        );
      }
    });
  }
};



})(jQuery);
