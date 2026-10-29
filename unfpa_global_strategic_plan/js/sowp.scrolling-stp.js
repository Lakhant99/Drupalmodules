/**
 * @file
 * Contains 2017 override of Drupal.sowp.onScroll().
 *
 * Contains stuff like:
 * - scrolling to section on load
 * - setting current active section id variable on scroll
 * - highlighting navigation items on scroll
 * - section intro animations
 * - sticky regular copy sidebars
 * - navigation links click handling
 */
(function ($) {



/**
 * Initialize Drupal.sowp object if it doesn't exist.
 */
Drupal.sowp = Drupal.sowp || {};



/**
 * Variables.
 */
// Keep track of current active section.
Drupal.sowp.current = 'Cover';



/**
 * Need to pass some translatables here.
 *
 * Because the script that uses them isn't added using drupal API,
 * and thus these won't be detected otherwise.
 */
Drupal.t('state of world population 2017');
Drupal.t('worlds');
Drupal.t('apart');
Drupal.t('Reproductive health and rights<br />in an age of inequality');



/**
 * Calculates parallax shift.
 *
 * @arg x_min: minimal value. if x_value is less, it will be treated as this value.
 * @arg x_max: maximal value. if x_value is more, it will be treated as this value.
 * @arg x_value: the value to process.
 * @arg out_min: output minimal value.
 * @arg out_max: output maximal value.
 *
 * @returns The x_value is converted to percent and then converted to point on the
 * new scale with range from out_min to out_max.
 */
Drupal.sowp.calcShift = function(x_min, x_max, x_value, out_min, out_max) {
  if (x_value <= x_min) {
    return out_min;
  }
  else if (x_value >= x_max) {
    return out_max;
  }
  else {
    var fraction = (x_value - x_min) / (x_max - x_min);
    return (out_max - out_min) * fraction + out_min;
  }
};



/**
 * Position tracking function, called on $(window).scroll().
 *
 * Handles the following:
 * - saving id of currently active (visible) section
 * - highlighting navigation items
 * - section animations
 * - sticky regular copy sidebars.
 */
Drupal.sowp.onScroll = function () {
  var from_top = Drupal.sowp.jq_window.scrollTop();
  $('.swp-title-subtitle').css({'opacity': 0});

  // --- Get id of current section item --- //

  var cur = Drupal.sowp.jq_sectionsmap.map(function () {
    if ($(this).offset().top < (from_top))
      return this;
  });
  cur = cur[cur.length - 1];
  var id = cur && cur.length ? cur[0].id : "";

  // If no section is active then we are above Cover section.

  if (!id) {
    id = 'Cover';
  }

  // Stuff for section that just became active.

  if (Drupal.sowp.current !== id) {
    Drupal.sowp.current = id;
    Drupal.sowp.jq_current = $('#' + id);

    // Set/remove navigation active class.
    Drupal.sowp.jq_navlinks.removeClass('active');
    Drupal.sowp.jq_navlinks.filter('[href*=\\#' + id + ']').addClass('active');
  }

  // --- Current section intro animations --- //

  var $introbgw = Drupal.sowp.jq_current.find('.swp-intro-bg-wrap');
  var $introbg = Drupal.sowp.jq_current.find('.swp-intro-bg');
  var $introbg2 = Drupal.sowp.jq_current.find('.swp-intro-bg-2');
  var $introcontent = Drupal.sowp.jq_current.find('.swp-intro-content');
  var $introsubtitle = Drupal.sowp.jq_current.find('.swp-title-subtitle');
  var $continue_arr = Drupal.sowp.jq_current.find('.swp-continue');
  
  var postop = Drupal.sowp.jq_current.offset().top;
  var scrolltop = $(document).scrollTop();

  // Pin section intro bg and title into place.

  if (postop <= scrolltop) {
    $introbgw.css({'position': 'fixed'});
    $introcontent.css({'position': 'fixed'});
    $introsubtitle.css({'opacity': 0});
  }
  else {
    $introbgw.css({'position': 'absolute'});
    $introcontent.css({'position': 'absolute'});
  }
  
  // Prapare some animation variables.

  var moved = (scrolltop - postop);
  var length = 1600;

  // Animate section intro background and subtitle.

  var fade_bg = 1;
  var fade_bg_2 = 1;
  var fade_subtitle = 0;

  if (moved >= 1) {
    // Calculate fade for bg photo.
    fade_bg = 1 - Drupal.sowp.calcShift(1000, 1400, moved, 0, 0.65);

    // Calculate fade for bg illustration.
    fade_bg_2 = Drupal.sowp.calcShift(100, 800, moved, 1, 0);

    // Calculate fade for subtitle.
    fade_subtitle = Drupal.sowp.calcShift(1000, 1400, moved, 0, 1);
    
    if (fade_subtitle > 1) {
      fade_subtitle = 1;
    }
    if (fade_subtitle < 0) {
      fade_subtitle = 0;
    }
  }
  
  $introbg.css({'opacity': fade_bg});
  $introbg2.css({'opacity': fade_bg_2});
  $introsubtitle.css({'opacity': fade_subtitle});

  // Animate continue arrow.

  var fade_arr = 1;
  
  if (moved >= 1) {
    fade_arr = 1 - ((moved - 800) / 300);
    
    if (fade_arr > 1) {
      fade_arr = 1;
    }
  }
  $continue_arr.css({'opacity': fade_arr});

  // Animate section intro title.

  var titleoff = 0;
  if (moved >= length) {
    titleoff = moved - length;
  }
  $introcontent.css({'margin-top': '-' + titleoff + 'px'});

  // Reset other sections to normal css styles.

  $('.swp-section .swp-intro-bg-wrap').not($introbgw).css({'position': 'absolute'});
  $('.swp-section .swp-intro-bg').not($introbg).css({'opacity': 1});
  $('.swp-section .swp-intro-content').not($introcontent).css({'position': 'absolute', 'margin-top': 0});

  // --- Sticky regular copy sidebars (if viewport 786 plus) --- //

  if (Drupal.sowp.deviceCheck.is768plus()) {
    Drupal.sowp.jq_current.find('.swp-body-side').each(function () {
      var $copy_side = $(this);
      var $sticky = $copy_side.find('.swp-body-side-sticky');
      var stick_from = $copy_side.offset().top;

      // 30px is gutter, 7px is margin-top of .swp-body-side.
      if (stick_from < (from_top + 30)) {
        var parent_height = $copy_side.parent().height();
        var sticky_height = $sticky.height();
        var stick_to = stick_from + parent_height - sticky_height;

        // Stick to 'bottom' of parent container.
        if (stick_to < (from_top + 30 + 7)) {
          $sticky.css({'top': (parent_height - sticky_height - 7) + 'px'});
        }
        // Stick to top of parent container.
        else {
          $sticky.css({'top': (from_top - stick_from + 30) + 'px'});
        }
      }
      // No sticking.
      else {
        $sticky.css({'top': 0, 'bottom': 'auto'});
      }
    });
  }
  else {
    $('.swp-body-side-sticky').css({'top': 0, 'bottom': 'auto'});
  }
};



/**
 * Section intro behaviour.
 */
Drupal.behaviors.sowpSectionIntro = {
  attach: function (context, settings) {
    $('.swp-intro', context).once('sowpSectionIntro').each(function() {
      if (!Drupal.sowp.deviceCheck.isMobile()) {
        return;
      }
      
      var $intro = $(this);
      var $intro_bg = $intro.find('.swp-intro-bg-wrap');
      var $intro_content = $intro.find('.swp-intro-content');
      var $measure = $('.stp-vh-measure');

      var viewport_height = $measure.height();
      
      var set_sizes = function() {
        $intro.css({'height': viewport_height + 'px', 'min-height': viewport_height + 'px'});
        $intro_bg.css({'height': viewport_height + 'px'});
        $intro_content.css({'height': viewport_height + 'px'});
      };
      
      set_sizes();
      
      setInterval(function() {
        if (viewport_height < $measure.height()) {
          viewport_height = $measure.height();
          set_sizes();
        }
      }, 500);

      window.addEventListener("orientationchange", function() {
        viewport_height = $measure.height();
        set_sizes();
      });
    });
  }
};
      



})(jQuery);
