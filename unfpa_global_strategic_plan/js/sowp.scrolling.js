/**
 * @file
 * Contains logic responsible for sections scrolling, fading and navigation logic.
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
//Keep track of current active section
Drupal.sowp.current = 'Cover';
//Timers object
Drupal.sowp.timers = {};



/**
 * Scroll to a section if provided in the url.
 */
Drupal.sowp.scrollOnload = function () {
    var url = location.href;
    var hash = url.split('#!/');

    if (hash[1] != undefined) {
        var $target_section = $('#' + hash[1]);

        if ($target_section.length > 0) {
            //If target section is a hidden Featured Story then need to open it
            if ($target_section.hasClass('swp-story')) {
                $target_section.show(0);
                $target_section.parents('.swp-stories').find('.stories-list').hide();
            }
            
            setTimeout(function () {
                  var go1 = Math.round($target_section.offset().top) - 600;
                  var go2 = Math.round($target_section.offset().top + 1);
                Drupal.sowp.jq_body.scrollTop(go1).animate({scrollTop: go2}, 1000);
            }, 700);
        }
    }
};



/**
 * Position tracking function, called on $(window).scroll(). Handles the following:
 * - saving id of currently active (visible) section
 * - highlighting navigation items
 * - section animations
 * - sticky regular copy sidebars
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

    //If no section is active then we are above Cover section

    if (!id) {
        id = 'Cover';
    }

    //Stuff for section that just became active

    if (Drupal.sowp.current !== id) {
        Drupal.sowp.current = id;
        Drupal.sowp.jq_current = $('#' + id);

        // Set/remove navigation active class
        Drupal.sowp.jq_navlinks.removeClass('active');
        Drupal.sowp.jq_navlinks.filter('[href*=\\#' + id + ']').addClass('active');
    }

    // --- Current section intro animations --- //

    var $introbgw = Drupal.sowp.jq_current.find('.swp-intro-bg-wrap');
    var $introbg = Drupal.sowp.jq_current.find('.swp-intro-bg');
    var $introcontent = Drupal.sowp.jq_current.find('.swp-intro-content');
    var $introsubtitle = Drupal.sowp.jq_current.find('.swp-title-subtitle');
    var $continue_arr = Drupal.sowp.jq_current.find('.swp-continue');

    if (Drupal.sowp.deviceCheck.is768plus()) {
        var postop = Drupal.sowp.jq_current.offset().top;
        var scrolltop = $(document).scrollTop();

        //Pin section intro bg and title into place

        if (postop <= scrolltop) {
            $introbgw.css({'position': 'fixed'});
            $introcontent.css({'position': 'fixed'});
            $introsubtitle.css({'opacity': 0});
        }
        else {
            $introbgw.css({'position': 'absolute'});
            $introcontent.css({'position': 'absolute'});
        }

        //Animate section intro background and subtitle

        var moved = (scrolltop - postop);

        var fade = 1;
        var fade_subtitle = 0;

        if (moved >= 1) {
            fade = 1 - ((moved - 100) / 600) * (1 - 0.35);
            if (fade > 1)
                fade = 1;
            if (fade < 0.35)
                fade = 0.35;

            fade_subtitle = ((moved - 100) / 600);
            if (fade_subtitle > 1)
                fade_subtitle = 1;
            if (fade_subtitle < 0)
                fade_subtitle = 0;
        }
        $introbg.css({'opacity': fade});
        $introsubtitle.css({'opacity': fade_subtitle});

        //Animate continue arrow

        fade = 1;
        if (moved >= 1) {
            fade = 1 - ((moved - 800) / 300);
            if (fade > 1)
                fade = 1;
        }
        $continue_arr.css({'opacity': fade});

        //Animate section intro title

        var titleoff = 0;
        if (moved >= 800) {
            titleoff = moved - 800;
        }
        $introcontent.css({'margin-top': '-' + titleoff + 'px'});

        //Reset other sections to normal css styles

        $('.swp-section .swp-intro-bg-wrap').not($introbgw).css({'position': 'absolute'});
        $('.swp-section .swp-intro-bg').not($introbg).css({'opacity': 1});
        $('.swp-section .swp-intro-content').not($introcontent).css({'position': 'absolute', 'margin-top': 0});
    }
    else {
        $('.swp-section .swp-intro-bg-wrap').css({'position': 'absolute'});
        $('.swp-section .swp-intro-bg').css({'opacity': 0.9});//darken a bit for readability on mobile
        $('.swp-section .swp-intro-content').css({'position': 'relative', 'margin-top': 0});
    }

    // --- Sticky regular copy sidebars (if viewport 786 plus) --- //

    if (Drupal.sowp.deviceCheck.is768plus()) {
        Drupal.sowp.jq_current.find('.swp-body-side').each(function () {
            var $copy_side = $(this);
            var $sticky = $copy_side.find('.swp-body-side-sticky');
            var stick_from = $copy_side.offset().top;

            //30px is gutter, 7px is margin-top of .swp-body-side
            if (stick_from < (from_top + 30)) {
                var parent_height = $copy_side.parent().height();
                var sticky_height = $sticky.height();
                var stick_to = stick_from + parent_height - sticky_height;

                //stick to 'bottom' of parent container
                if (stick_to < (from_top + 30 + 7)) {
                    $sticky.css({'top': (parent_height - sticky_height - 7) + 'px'});
                }
                //stick to top of parent container
                else {
                    $sticky.css({'top': (from_top - stick_from + 30) + 'px'});
                }
            }
            //no sticking
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
 * Stuff to be done on window resize.
 */
Drupal.sowp.onResize = function () {
    clearTimeout(Drupal.sowp.timers.onResize);
    Drupal.sowp.timers.onResize = setTimeout(function () {
        //Do onScroll stuff on resize
        Drupal.sowp.onScroll();
    }, 100);
};



/**
 * Navigation link click behaviour.
 */
Drupal.behaviors.sowpNav = {
    attach: function (context, settings) {
        $('.swp-nav', context).once('sowpNav').each(function() {
            var $nav = $(this).find('ul a');

          if (!window.isDesktop) {
            $('html').on('touchstart', function() {
              $nav.blur().removeClass('focus');
            });
          }

            $nav.each(function () {
                var $a = $(this);

                $a.on('touchstart', function(event) {
                  event.stopPropagation();
                });
                
                $a.click(function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    //If we are non-desktop device and $a has not focus then expand nav item
                    if (!window.isDesktop && !$a.hasClass('focus')) {
                        //Blur all nav links
                        $nav.blur().removeClass('focus');
                        //Focus this link so it expands (uses css)
                        $a.focus().addClass('focus');
                    }

                    //If this is desktop, or is mobile and nav link is already expanded
                    else {
                        $a.focus();//for desktops

                        var section_id = $a.attr('href').replace(/#/, '');
                        var $section = $('section#' + section_id);

                        //do prescroll if we are within one of the sections
                        if (section_id != Drupal.sowp.current && Drupal.sowp.current) {
                            var current_weight = parseInt($('#' + Drupal.sowp.current).attr('data-weight'));
                            var thisone_weight = parseInt($section.attr('data-weight'));

                            //how much far (in sections+1) do we have to be to make prescroll.
                            //-set to 0 to always prescroll
                            //-set to 1, to only prescroll if clicked section is not right
                            // after or right before current section
                            //-set to a number higher than the number of sections-1 to never prescroll
                            var far = 0;

                            if ((thisone_weight != (current_weight + far)) &&
                                    (thisone_weight != (current_weight - far)) &&
                                    thisone_weight < current_weight) {
                                //gotta go up
                                var padd_top = parseInt($section.find('.swp-push').css('padding-top'));
                                Drupal.sowp.jq_body.scrollTop($section.offset().top + 600 + padd_top);
                            }

                            else if (((thisone_weight - far) != current_weight) &&
                                    ((thisone_weight + far) != current_weight) &&
                                    thisone_weight > current_weight) {
                                //gotta go down
                                Drupal.sowp.jq_body.scrollTop($section.offset().top - 600);
                            }
                        }

                        //or do prescroll if we are within none of them (which means we are at header/cover)
                        else if (!Drupal.sowp.current) {
                            //gotta go down
                            Drupal.sowp.jq_body.scrollTop($section.offset().top - 600);
                        }

                        location.href = '#!/' + section_id;

                        Drupal.sowp.jq_body.animate(
                                {scrollTop: $section.offset().top + 1},
                        1000,
                                function () {
                                    //after animation: blur menu link item so it animates to hide label
                                    $nav.blur().removeClass('focus');
                                }
                        );
                    }//end desktop / non-desktop
                });
            });
        });
    }
};



/**
 * Window On Load.
 */
$(window).on('load', function () {
    //Cache frequently used jQuery objects
    Drupal.sowp.jq_window = $(window);
    Drupal.sowp.jq_body = $('html, body');
    Drupal.sowp.jq_nav = $('.swp-nav');
    Drupal.sowp.jq_navlinks = $('.swp-nav ul a');
    Drupal.sowp.jq_sections = $('.swp-section');
    Drupal.sowp.jq_current = $('#Cover');
    Drupal.sowp.jq_sectionsmap = Drupal.sowp.jq_navlinks.map(function () {
        var item = $($(this).attr('href'));
        if (item.length) {
            return item;
        }
    });

    //Scroll to a section if provided in the url.
    Drupal.sowp.scrollOnload();

    //onScroll & onResize - on load (onScroll is triggered from onResize())
    Drupal.sowp.onResize();

    //Do stuff on window scroll
    Drupal.sowp.jq_window.scroll(function () {
        Drupal.sowp.onScroll();
    });

    //Do stuff on window resize
    Drupal.sowp.jq_window.resize(function () {
        Drupal.sowp.onResize();
    });
});



})(jQuery);
