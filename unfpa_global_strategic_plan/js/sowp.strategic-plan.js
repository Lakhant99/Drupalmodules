/**
* @file
* UNFPA Strategic Plan related javascript behaviours.
*/
(function ($) {




/**
 * Entry Section behaviour.
 */
Drupal.behaviors.sowpStpEntry = {
  attach: function (context, settings) {
    $('.stp-entry', context).once('sowpStpEntry').each(function() {
      var $window = $(window);
      var $document = $(document);
      var $entry = $(this);
      var $entry_dom = $entry.get(0);
      var $triggers = $entry.find('.stp-entry-trigger span.has-scene');
      var $stick = $entry.find('.stp-entry-stick');
      var $autoplay = $entry.find('.stp-entry-autoplay');
      var $skip = $entry.find('.stp-entry-skip');
      var resize_timeout;
      var is_autoplaying = false;

      // Cache scenes items and their children.
      var $scenes = $entry.find('.stp-entry-scene');
      var $scene_bgs = $scenes.find('.stp-entry-bg-2');
      var $scene_tints = $scenes.find('.stp-entry-tint');
      var $scene_contents = $scenes.find('.stp-entry-content');
      var $scene_items_2 = $scenes.find('.stp-entry-item-2');
      var $scene_items_3 = $scenes.find('.stp-entry-item-3');

      // Function for calculating movement.
      // Arguments:
      // min: minimal value. if x_value is less, it will be treated as this value.
      // x_max: maximal value. if x_value is more, it will be treated as this value.
      // x_value: the value to process.
      // out_min: output minimal value.
      // out_max: output maximal value.
      // Returns:
      // The x_value is converted to percent and then converted to point on the
      // new scale with range from out_min to out_max.
      var entry_calc_shift = function (x_min, x_max, x_value, out_min, out_max) {
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

      // Animate on scroll.
      var entry_on_scroll = function () {
        var entry_postop = $entry.offset().top;
        var entry_height = $entry.innerHeight();
        var scrolltop = $document.scrollTop();

        // Pin entry section as fixed when needed.
        if ((entry_postop <= scrolltop) && (entry_postop + entry_height > scrolltop)) {
          $stick.css({'position': 'fixed'});
        }
        else {
          $stick.css({'position': 'absolute'});
        }

        $triggers.each(function (index) {
          var $trigger = $(this);
          var postop = $trigger.offset().top;
          var height = $trigger.height();

          // Get all needed scene data/items.
          var $scene = $scenes.eq(index);
          var $bg = $scene_bgs.eq(index);
          var $tint = $scene_tints.eq(index);
          var $content = $scene_contents.eq(index);
          var $item_2 = $scene_items_2.eq(index);
          var $item_3 = $scene_items_3.eq(index);

          // Animate scene fadein incoming animation (for scenes 2-5).
          if (index !== 0) {
            var opacity = entry_calc_shift(postop, (postop + height * 0.2), scrolltop, 0, 1);
            $scene.css('opacity', opacity);
          }

          // Animated background.
          var opacity = entry_calc_shift((postop + height * 0.2), (postop + height * 0.4), scrolltop, 0, 1);
          $bg.css('opacity', opacity);

          // Animate tint layer fadein.
          var opacity = entry_calc_shift((postop + height * 0.4), (postop + height * 0.6), scrolltop, 0, 0.7);
          $tint.css('opacity', opacity);

          // Animte content layer fadein.
          var opacity = entry_calc_shift((postop + height * 0.5), (postop + height * 0.7), scrolltop, 0, 1);
          $content.css('opacity', opacity);
          
          // Animate content items.
          var opacity = entry_calc_shift((postop + height * 0.6), (postop + height * 0.8), scrolltop, 0, 1);
          $item_2.css('opacity', opacity);

          var opacity = entry_calc_shift((postop + height * 0.7), (postop + height * 0.9), scrolltop, 0, 1);
          $item_3.css('opacity', opacity);
        });
      };
      
      // Reverts entry section to static.
      var entry_unanimate = function () {
        $scenes.css({'opacity': 1});
        $stick.css({'position': 'relative'});
        $scene_bgs.css({'opacity': 1});
        $scene_tints.css({'opacity': 0});
        $scene_contents.css({'opacity': 1});
        $scene_items_2.css({'opacity': 1});
        $scene_items_3.css({'opacity': 1});
      };

      // Stops autoplay.
      var entry_stop_autoplay = function() {
        if (is_autoplaying) {
          $('html, body').stop();
          is_autoplaying = false;
          $autoplay.removeClass('is-playing');
        }
      };

      // On load.
      $entry.addClass('stp-entry-js');
      $window.on('load',function () {
        var is_in_viewport = Drupal.sowp.elemIsVisible($entry_dom, 0);
        if (is_in_viewport) {
          window.requestAnimationFrame(entry_on_scroll);
        }
      });

      // Do stuff on window scroll.
      $window.scroll(function () {
        var is_in_viewport = Drupal.sowp.elemIsVisible($entry_dom, 0);
        if (is_in_viewport) {
          window.requestAnimationFrame(entry_on_scroll);
        }
        else {
          entry_unanimate();
        }
      });

      // Do stuff on window resize.
      $window.resize(function () {
        clearTimeout(resize_timeout);
        resize_timeout = setTimeout(function () {
          var is_in_viewport = Drupal.sowp.elemIsVisible($entry_dom, 0);
          if (is_in_viewport) {
            window.requestAnimationFrame(entry_on_scroll);
          }
          else {
            entry_unanimate();
          }
        }, 100);
      });
      
      // Skip link.
      $skip.click(function () {
        if (is_autoplaying) {
          is_autoplaying = false;
          $autoplay.removeClass('is-playing');
          $('html, body').stop();
        }

        var scroll_to = $('.stp-after-entry').offset().top + 1;

        $('html, body').animate({
          scrollTop: scroll_to
        }, {
          // Keep duration 0 so it doesn't chop in safari mobile.
          duration: 0
        });
        
        
      });

      // Autoplay link.
      $autoplay.click(function () {
        if (!is_autoplaying) {
          is_autoplaying = true;
          $autoplay.addClass('is-playing');

          // Calculate autoplay duration.
          var scroll_top = $window.scrollTop();
          var entry_height = $entry.height();
          var entry_top = $entry.offset().top;
          var entry_bottom = entry_top + entry_height;
          var viewport_height = $window.height();

          // Negotiate initial duration and to which point should we autoplay.
          var duration = 40000;
          var scroll_to = $('.stp-after-entry').offset().top + 1;

          if (entry_top < scroll_top && entry_bottom > scroll_top) {
            duration = duration - Math.floor(entry_calc_shift(0, entry_bottom, scroll_top, 0, duration));
          }

          // Do autoplay animation.
          $('html, body').animate({
            scrollTop: scroll_to
          }, {
            duration: duration,
            easing: 'linear',
            complete: function () {
              entry_stop_autoplay();
            }
          });
        }
        else {
          entry_stop_autoplay();
        }
      });

      // Stop autoplay on user scroll or window resize.
      $window.bind('mousewheel DOMMouseScroll resize', function () {
        entry_stop_autoplay();
      });

      // Advance to next illustration on click.
      $scenes.click(function() {
        if (!is_autoplaying) {
          is_autoplaying = true;
          $autoplay.addClass('is-playing');

          // Find out to which point we should autoplay.
          var autoplay_to;
          var $trigger;
          var viewport_height = $window.height();
          var substract = viewport_height - 1;

          var duration = 8000;

          // If we are at the end - do $autoplay.click() so the site
          // scrolls down to the page title.
          if (Drupal.sowp.elemIsVisible($entry.find('.entry-trigger-6').get(0), substract)) {
            is_autoplaying = false;
            $autoplay.click();
            return;
          }

          else if (Drupal.sowp.elemIsVisible($triggers.eq(4).get(0), substract)) {
            $trigger = $triggers.eq(4);
          }
          else if (Drupal.sowp.elemIsVisible($triggers.eq(3).get(0), substract)) {
            $trigger = $triggers.eq(3);
          }
          else if (Drupal.sowp.elemIsVisible($triggers.eq(2).get(0), substract)) {
            $trigger = $triggers.eq(2);
          }
          else if (Drupal.sowp.elemIsVisible($triggers.eq(1).get(0), substract)) {
            $trigger = $triggers.eq(1);
          }
          else if (Drupal.sowp.elemIsVisible($triggers.eq(0).get(0), 0)) {
            $trigger = $triggers.eq(0);
            duration = duration / 400 * 266;
          }

          if (!($trigger instanceof jQuery)) {
            return;
          }

          // Calculate autoplay duration.
          var scroll_top = $window.scrollTop();
          var trigger_height = $trigger.height();
          var trigger_top = $trigger.offset().top;
          var trigger_bottom = trigger_top + trigger_height;
          if (trigger_top < scroll_top && trigger_bottom > scroll_top) {
            duration = duration - Math.floor(entry_calc_shift(trigger_top, trigger_bottom, scroll_top, 0, duration));
          }

          // Do autoplay animation.
          $('html, body').animate({
            scrollTop: $trigger.offset().top + $trigger.height()
          }, {
            duration: duration,
            easing: 'linear',
            complete: function () {
              entry_stop_autoplay();
            }
          });
        }
        else {
          entry_stop_autoplay();
        }
      });
      
    });
  }
};



/**
 * Strategic Plan Intro behaviour.
 */
Drupal.behaviors.sowpStpIntro = {
  attach: function (context, settings) {
    $('.stp-intro', context).once('sowpStpIntro').each(function() {
      var $intro = $(this);
      var $video = $intro.find('.stp-intro-video');
      var $video_placeholder = $intro.find('.stp-intro-video-placeholder');
      var $video_video = $intro.find('.stp-intro-video-video');

      // Animate intro
      var animate = function() {
        $video.animate({'opacity': 1}, 500);
      };

      // Deanimate intro.
      var deanimate = function() {
        $video.css({'opacity': 0});
      };

      // Deanimate on load.
      deanimate();

      // In viewport animation offset.
      var offset = 0;
      if (Drupal.sowp.deviceCheck.is768plus()) {
        offset = 150;
      }

      // On load animation.
      setTimeout(function () {
        var is_in_viewport = Drupal.sowp.elemIsVisible($video.get(0), offset);
        if (is_in_viewport) {
          animate();
        }
      }, 1000);

      // On scroll animation.
      var timeout;
      $(document).scroll(function () {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
          var is_in_viewport = Drupal.sowp.elemIsVisible($video.get(0), offset);

          if (is_in_viewport) {
            animate();
          }
          else if (!Drupal.sowp.elemIsVisible($video.get(0), -300)) {
            deanimate();
          }
        }, 50);
      });
      
      // Video palyback logic.
      $video_placeholder.click(function() {
        var video_id = $video_video.attr('data-video-id');

        // Show video div on click.
        $video_video.show();

        // Replace #stp-intro-video-player with actual player.
        var player = new YT.Player('stp-intro-video-player', {
          height: '1034',
          width: '581',
          videoId: video_id,
          autoplay: true,
          events: {
            // Play video on iframe load.
            onReady: function(event) {
              event.target.playVideo();
            },
            // Once video finishes remove iframe and revert #stp-intro-video-player.
            onStateChange: function(event) {
              if (event.data == YT.PlayerState.ENDED) {
                $video_video.hide();
                $video_video.find('iframe').remove();
                $video_video.html('<div id="stp-intro-video-player"></div>');
              }
            }
          }
        });

      });
    });
  }
};



/**
 * SDG Wheel behaviour.
 */
Drupal.behaviors.sowpSdgWheel = {
  attach: function (context, settings) {
    $('.stp-wheel', context).once('sowpSdgWheel').each(function() {
      var $wrapper = $(this);
      var $svg = $wrapper.find('.stp-wheel-svg svg');
      var $icons = $wrapper.find('.stp-wheel-icons img');
      var $trigger = $wrapper.find('.stp-wheel-trigger');
      var $copyitems = $wrapper.find('.stp-wheel-copy-item');
      // Track which is active part so we know to which to advance to.
      var active_part = 0;
      // False if wheel didn't do initial spin.
      var wheel_has_spinned = false;
      // How many full rotations spinned so far (as angles).
      var angle = 0;
      // How many full rotations to add on each spin (as angles).
      var angle_add = 720;
      // Timeout for after spinning animations and for in viewport.
      var timeout;
      var timeout_2;
      
      // Wheel parts ids object for easier processing of parts later.
      var parts = {};
      for (var i = 1; i <= 17; i++) {
        parts[i] = $svg.find('#part-' + i);
      }
      
      // Define our css translates used by zoomable pieces.
      var css_translate = {
        '3': '11px, -9px',
        '5': '12px, 2px',
        '10': '-6px, 11px',
        '16': '-8px, -11px',
        '17': '-5px, -11.5px'
      };

      // Define our css rotation of wheel used by zoomable pieces.
      var css_rotate = {
        '3': -53.2,
        '5': -95.3,
        '10': -201,
        '16': -328.5,
        '17': -349.5
      };
      
      // Apply default styles to svg wheel parts.
      $.each(parts, function(index, $part) {
        $part.css('transition', 'fill-opacity 0.8s');
      });
      
      // Gets new base angle to which to spin the wheel.
      var base_angle = function() {
        angle = angle + angle_add;

        return angle;
      };

      // Spins wheel when it comes into viewport.
      var spin_wheel = function() {
        if (wheel_has_spinned == true) {
          return;
        }

        wheel_has_spinned = true;
        
        // Spin for 10 seconds.
        $svg.css('transition', 'transform 5s ease-out');
        setTimeout(function() {
          // Spin to tile 3.
          $svg.css('transform', 'rotate(' + css_rotate[3] + 'deg)');
        }, 50);

        // After spining fade in play button and activate section 3.
        setTimeout(function() {
          $trigger.fadeTo(400, 1);
          activate_one_part(3, false);
        }, 5050);
      };

      // Prepares wheel for a new spin (called when wheel goes out of viewport).
      var prepare_wheel_for_spin = function() {
        $svg.css('transition', 'none');
        $trigger.fadeTo(0, 0);
        angle = 0;
        wheel_has_spinned = false;

        $.each(parts, function(index, $part) {
          $part.css('transform', 'translate(0, 0)');
          $part.attr('fill-opacity', 1);
        });

        $icons.not('.stp-wheel-icon-0').fadeTo(600, 0);
        $icons.filter('.stp-wheel-icon-0').fadeTo(600, 1);

        $copyitems.not('.stp-wheel-copy-item-0')
          .fadeOut(600).promise().done(function() {
          $copyitems.filter('.stp-wheel-copy-item-0').fadeIn(600);
        });

        setTimeout(function() {
          $svg.css('transform', 'rotate(-800deg)');
        }, 50);
        
        setTimeout(function() {
          $svg.css('transition', 'transform 5s ease-out');
        }, 100);
      };

      // Resets wheel, parts, icons and copies to initial states.
      var reset_wheel = function() {
        angle = angle + 360;
        $svg.css('transform', 'rotate(' + angle + 'deg)');
        
        $.each(parts, function(index, $part) {
          $part.css('transform', 'translate(0, 0)');
          $part.attr('fill-opacity', 1);
        });

        clearTimeout(timeout);
        timeout = setTimeout(function() {
          // Switch wheel inner icon.
          $icons.not('.stp-wheel-icon-0').fadeTo(600, 0);
          $icons.filter('.stp-wheel-icon-0').fadeTo(600, 1);

          // Switch copy item.
          $copyitems.not('.stp-wheel-copy-item-0')
            .fadeOut(600).promise().done(function() {
            $copyitems.filter('.stp-wheel-copy-item-0').fadeIn(600);
          });
        }, 2200);
      };
      
      // Activate selected wheel part.
      var activate_one_part = function(part_no, add_base_angle) {
        // Fade out all wheel parts.
        $.each(parts, function(index, $part) {
          $part.attr('fill-opacity', 0.5);
          $part.css('transform', 'translate(0, 0)');
        });
        
        // Get part to activate.
        var $part = parts[part_no];
        
        // Keep track of active part.
        active_part = part_no;

        // Make part fully opaque.
        $part.attr('fill-opacity', 1);

        // Rotate svg wheel.
        if (add_base_angle) {
          $svg.css({'transform': 'rotate(' + (base_angle() + css_rotate[part_no]) + 'deg)'});
          var delay = 2200;
        }
        else {
          $svg.css({'transform': 'rotate(' + css_rotate[part_no] + 'deg)'});
          var delay = 0;
        }
        

        // Do the following things after the wheel has rotated.
        clearTimeout(timeout);
        timeout = setTimeout(function() {
          // Zoom in and move out wheel part.
          var bbox = $part.get(0).getBBox();
          var center_x = bbox.x + bbox.width / 2;
          var center_y = bbox.y + bbox.height / 2;
          
          $part.css('transform-origin', center_x + 'px ' + center_y + 'px');
          $part.css('transform', 'scale(1.3) translate(' + css_translate[part_no] + ')');
          
          // Switch wheel inner icon.
          $icons.not('.stp-wheel-icon-' + part_no).fadeTo(600, 0);
          $icons.filter('.stp-wheel-icon-' + part_no).fadeTo(600, 1);

          // Switch copy item.
          $copyitems.not('.stp-wheel-copy-item-' + part_no)
            .fadeOut(600).promise().done(function() {
            $copyitems.filter('.stp-wheel-copy-item-' + part_no).fadeIn(600);
          });
        }, delay);
      };

      // Play trigger link clicking.
      $trigger.click(function() {
        // Change svg transition time.
        $svg.css('transition', 'transform 2s ease-out');
        
        if (active_part == 0) {
          active_part = 3;
          activate_one_part(active_part, true);
        }
        else if (active_part == 3) {
          active_part = 5;
          activate_one_part(active_part, true);
        }
        else if (active_part == 5) {
          active_part = 10;
          activate_one_part(active_part, true);
        }
        else if (active_part == 10) {
          active_part = 16;
          activate_one_part(active_part, true);
        }
        else if (active_part == 16) {
          active_part = 17;
          activate_one_part(active_part, true);
        }
        else {
          active_part = 0;
          reset_wheel();
        }
      });
      
      // On load wheel spinning animation.
      setTimeout(function () {
        var is_in_viewport = Drupal.sowp.elemIsVisible($svg.get(0), 150);
        if (is_in_viewport) {
          spin_wheel();
        }
      }, 1000);

      // On scroll wheel spinning animation.
      $(document).scroll(function () {
        clearTimeout(timeout_2);
        timeout_2 = setTimeout(function() {
          if (Drupal.sowp.deviceCheck.isMobile()) {
            var is_in_viewport = Drupal.sowp.elemIsVisible($svg.get(0), 150);
          }
          else {
            var is_in_viewport = Drupal.sowp.elemIsVisible($svg.get(0), 300);
          }

          if (is_in_viewport) {
            spin_wheel();
          }
          // Need to make sure we are already 300px away.
          else if (!Drupal.sowp.elemIsVisible($svg.get(0), -300)) {
            prepare_wheel_for_spin();
          }
        }, 50);
      });
    });
  }
};



/**
 * Census Map behaviour.
 */
Drupal.behaviors.sowpCensusMap = {
  attach: function (context, settings) {
    $('.stp-census-map', context).once('sowpCensusMap').each(function() {
      var $wrapper = $(this);
      var $labels = $wrapper.find('.stp-census-years span');
      var $slider = $wrapper.find('.stp-census-slider');
      var $year = $wrapper.find('.stp-census-year');
      var $count = $wrapper.find('.stp-census-count');
      
      // Define in what year will which country have census.
      var countries = {
        // Nigeria.
        NG: 2018,
        // Algeria.
        DZ: 2018,
        // Sudan.
        SD: 2018,
        // Liberia.
        LR: 2018,
        // Malawi.
        MW: 2018,
        // El Salvador.
        SV: 2018,
        // Colombia.
        CO: 2018,
        // Democratic People's Republic of Korea.
        KP: 2018,
        // Guatemala.
        GT: 2018,
        // Congo, Republic of.
        CG: 2018,
        // Nicaragua.
        NI: 2018,
        // Haiti.
        HT: 2018,
        // Israel.
        IL: 2018,
        // Slovenia.
        SI: 2018,
        // New Zealand.
        NZ: 2018,
        // Burkina Faso.
        BF: 2018,
        // Ethiopia.
        ET: 2018,
        // Madagascar.
        MG: 2018,
        // Cameroon.
        CM: 2018,

        // Azerbaijan.
        AZ: 2019,
        // Belarus.
        BY: 2019,
        // Cambodia
        KH: 2019,
        // Chad
        TD: 2019,
        // Congo, Democratic Republic of the
        CD: 2019,
        // Djibouti
        DJ: 2019,
        // Guinea-Bissau.
        GW: 2019,
        // Kenya.
        KE: 2019,
        // Mali
        ML: 2019,
        // Vietnam
        VN: 2019,
        // New Caledonia.
        NC: 2019,
        // Solomon Islands
        SB: 2019,
        // Vanuatu
        VU: 2019,
        // Kyrgyz Republic.
        KG: 2019,
        // Maldives.
        MV: 2019,
        
        // Argentina.
        AR: 2020,
        // Aruba.
        AW: 2020,
        // Bahamas.
        BS: 2020,
        // Barbados.
        BB: 2020,
        // Belize.
        BZ: 2020,
        // Bermuda.
        BM: 2020,
        // Brazil.
        BR: 2020,
        // British Virgin Islands.
        VG: 2020,
        // Cape Verde.
        CV: 2020,
        // Cayman Islands.
        KY: 2020,
        // China.
        CN: 2020,
        // Dominican Republic.
        DO: 2020,
        // Ecuador.
        EC: 2020,
        // Liechtenstein.
        LI: 2020,
        // Ghana.
        GH: 2020,
        // Indonesia.
        ID: 2020,
        // Japan.
        JP: 2020,
        // Kazakhstan.
        KZ: 2020,
        // Kuwait.
        KW: 2020,
        // Malaysia.
        MY: 2020,
        // Mexico.
        MX: 2020,
        // Mongolia.
        MN: 2020,
        // Oman.
        OM: 2020,
        // Panama.
        PA: 2020,
        // Mauritius.
        MU: 2020,
        // Papua New Guinea.
        PG: 2020,
        // Philippines.
        PH: 2020,
        // Puerto Rico.
        PR: 2020,
        // Qatar.
        QA: 2020,
        // Russia.
        RU: 2020,
        // Saudi Arabia.
        SA: 2020,
        // Seychelles.
        SC: 2020,
        // Singapore.
        SG: 2020,
        // Saint Lucia.
        LC: 2020,
        // Tajikistan.
        TJ: 2020,
        // Thailand.
        TH: 2020,
        // Togo.
        TG: 2020,
        // Ukraine.
        UA: 2020,
        // United Arab Emirates.
        AE: 2020,
        // United States of America.
        US: 2020,
        // United States Virgin Islands.
        VI: 2020,
        // Zambia.
        ZM: 2020,
        
        // Albania.
        AL: 2021,
        // Andorra.
        AD: 2021,
        // Anguilla.
        AI: 2021,
        // Antigua and Barbuda.
        AG: 2021,
        // Armenia.
        AM: 2021,
        // Australia.
        AU: 2021,
        // Austria.
        AT: 2021,
        // Bangladesh.
        BD: 2021,
        // Belgium.
        BE: 2021,
        // Botswana.
        BW: 2021,
        // Brunei Darussalam.
        BN: 2021,
        // Bulgaria.
        BG: 2021,
        // Canada.
        CA: 2021,
        // Hong Kong.
        HK: 2021,
        // Costa Rica.
        CR: 2021,
        // Croatia.
        HR: 2021,
        // Curacao.
        CW: 2021,
        // Cyprus.
        CY: 2021,
        // Czechia.
        CZ: 2021,
        // Dominica.
        DM: 2021,
        // Estonia.
        EE: 2021,
        // Faeroe Islands.
        FO: 2021,
        // Finland.
        FI: 2021,
        // Germany.
        DE: 2021,
        // Greece.
        GR: 2021,
        // Greenland.
        GL: 2021,
        // Grenada.
        GD: 2021,
        // Hungary.
        HU: 2021,
        // Iceland.
        IS: 2021,
        // India.
        IN: 2021,
        // Italy.
        IT: 2021,
        // Jamaica.
        JM: 2021,
        // Kosovo.
        XK: 2021,
        // Latvia.
        LV: 2021,
        // Luxemburg.
        LU: 2021,
        // Malta.
        MT: 2021,
        // Slovenia.
        SI: 2021,
        // Saint Martin (French).
        MF: 2021,
        // Montenegro.
        ME: 2021,
        // Namibia.
        NA: 2021,
        // Nauru.
        NR: 2021,
        // Nepal.
        NP: 2021,
        // Netherlands.
        NL: 2021,
        // Norway.
        NO: 2021,
        // Poland.
        PL: 2021,
        // Portugal.
        PT: 2021,
        // Romania.
        RO: 2021,
        // Sao Tome-and-Principe.
        ST: 2021,
        // Serbia.
        RS: 2021,
        // Saint Martin (Dutch).
        SX: 2021,
        // Slovakia.
        SK: 2021,
        // South Africa.
        ZA: 2021,
        // Spain.
        ES: 2021,
        // St. Kitts and Nevis.
        KN: 2021,
        // St. Vincent and the Grenadines.
        VC: 2021,
        // Sweden.
        SE: 2021,
        // Switzerland.
        CH: 2021,
        // Trinidad and Tobago.
        TT: 2021,
        // Turkey.
        TR: 2021,
        // United Kingdom.
        GB: 2021,
        // Venezuela.
        VE: 2021,
        // Uruguay.
        UY: 2021,
        // Ireland.
        IE: 2021,
        // Lithuania.
        LT: 2021
      };
      
      // Define how many countries in which year.
      var counts = {
        2018: 20,
        2019: 17,
        2020: 48,
        2021: 71
      };
      
      // Function for colorizing countries based on year selected.
      function activate_map_year(year) {
        // Set colors for each country.
        $.each(countries, function(country_id, country_year) {
          if (country_year == year) {
            census_map.mapdata.state_specific[country_id]['color'] = '#f8941d';
            census_map.mapdata.state_specific[country_id]['hover_color'] = '#f8941d';
          }
          else {
            census_map.mapdata.state_specific[country_id]['color'] = '#bcbdc0';
            census_map.mapdata.state_specific[country_id]['hover_color'] = '#bcbdc0';
          }
        });

        // Refrsh map.
       census_map.refresh();
        
        // Indicate active year.
        $labels.removeClass('stp-census-year-active');
        $labels.filter('.stp-census-year-' + year.toString()).addClass('stp-census-year-active');
        
        // Update info text.
        $year.text(year);
        $count.text(counts[year]);
      }
      
      // jQuery UI slider for selecting year.
      $slider.slider({
        orientation: 'horizontal',
        min: 2018,
        max: 2021,
        value: 2018,
        step: 1,
        slide: function(event, ui) {
          activate_map_year(ui.value);
        }
      });
      
      // Activate first year on load.
      activate_map_year(2018);
    });
  }
};



/**
 * Key Principles behaviour.
 */
Drupal.behaviors.sowpKeyPrinc = {
  attach: function (context, settings) {
    $('.stp-keyprinc', context).once('sowpKeyPrinc').each(function() {
      var $wrapper = $(this);
      var $title = $wrapper.find('h4');
      var $list = $wrapper.find('.stp-keyprinc-list-wrap');
      var $items = $wrapper.find('.stp-keyprinc-list li');
      var $imgs = $wrapper.find('.stp-keyprinc-img');
      var delay_1 = 0;
      var delay_2 = 1300;
      var delay_3 = 2000;
      var timeout;
      var $tooltips = $wrapper.find('.stp-keyprinc-tip');

      // Animate section.
      var animate_list = function() {
        $title.addClass('has-animated');

        $items.each(function() {
          var $item = $(this);
          var $img = $item.find('.stp-keyprinc-img');

          setTimeout(function() {
            $item.addClass('has-animated');
          }, delay_1);

          setTimeout(function() {
            $img.addClass('half-animated');
          }, delay_2);

          setTimeout(function() {
            $img.addClass('has-animated');
          }, delay_3);

          delay_1 += 100;
          delay_2 += 100;
          delay_3 += 100;
        });
      };

      // Unanimate section.
      var unanimate = function() {
        $title.removeClass('has-animated');
        $items.removeClass('has-animated');
        $imgs.removeClass('has-animated').removeClass('half-animated');
        delay_1 = 0;
        delay_2 = 1300;
        delay_3 = 2000;
      };

      // On Load animation.
      setTimeout(function () {
        if ($title.length > 0) {
          var is_in_viewport = Drupal.sowp.elemIsVisible($title.get(0), 150);
          if (is_in_viewport) {
            $title.addClass('has-animated');
          }
        }
        if ($list.length > 0) {
          var is_in_viewport = Drupal.sowp.elemIsVisible($list.get(0), 150);
          if (is_in_viewport) {
            animate_list();
          }
        }
      }, 1000);

      // On scroll animations.
      $(document).scroll(function () {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
          if ($title.length > 0) {
            var is_in_viewport = Drupal.sowp.elemIsVisible($title.get(0), 150);
            if (is_in_viewport) {
              $title.addClass('has-animated');
            }
          }
          if ($list.length > 0) {
            var is_in_viewport = Drupal.sowp.elemIsVisible($list.get(0), 150);
            if (is_in_viewport) {
              animate_list();
            }
          }
          // Need to make sure we are already 300px away.
          else if (!Drupal.sowp.elemIsVisible($wrapper.get(0), -300)) {
            unanimate();
          }
        }, 50);
      });

      // Capitalize first letter used by tooltips.
      var ucfirst = function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
      };

      // Tooltips for some texts.
      $tooltips.each(function() {
        var $tooltip = $(this);

        $tooltip.qtip({
          content: {
            title: ucfirst($tooltip.text()),
            text: $tooltip.attr('data-tooltip')
          },
          position: {
            target: 'mouse',
            my: 'bottom center',
            at: 'top center'
          }
        });
      });
    });
  }
};



/**
 * Resources Slider behaviour.
 */
Drupal.behaviors.sowpStpSlider = {
  attach: function (context, settings) {
    $('.stp-slider-wrap', context).once('sowpStpSlider').each(function() {
      var $wrapper = $(this);
      var $ul = $wrapper.find('.stp-slider-items');
      
      // Append controls.
      var $prev = $(
        '<button class="stp-slider-prev" title="' + Drupal.t('Previous') + '">' + 
        '<span>&nbsp;</span>' + 
        '</button>'
      );
      $prev.appendTo($wrapper);
      var $next = $(
        '<button class="stp-slider-next" title="' + Drupal.t('Next') + '">' +
        '<span>&nbsp;</span>' +
        '</button>'
      );
      $next.appendTo($wrapper);

      // Enables cycle of slider.
      var enable_cycle = function() {
        $ul.addClass('stp-slider-items-cycle');
        $prev.css({'display': 'block'});
        $next.css({'display': 'block'});

        $ul.cycle({
          pauseOnHover: true,
          speed: 1000,
          timeout: 0,
          fx: 'scrollHorz',
          slides: '> li',
          swipe: true,
          autoHeight: 'calc',
          prev: $prev,
          next: $next,
          log: false
        });
      };

      // Disables cycle.
      var disable_cycle = function() {
        $ul.removeClass('stp-slider-items-cycle');
        $prev.css({'display': 'none'});
        $next.css({'display': 'none'});

        $ul.cycle('destroy');
      };

      // On load activation.
      if (Drupal.sowp.deviceCheck.is768plus()) {
        enable_cycle();
      }
      else {
        disable_cycle();
      }
      
      // On resize enabling/disabling.
      var resize_timeout;
      $(window).resize(function() {
        clearTimeout(resize_timeout);
        resize_timeout = setTimeout(function() {
          if (Drupal.sowp.deviceCheck.is768plus()) {
            enable_cycle();
          }
          else {
            disable_cycle();
          }
        }, 100);
      });
    });
  }
};



/**
 * Multivalue Pie Chart behaviour.
 */
Drupal.behaviors.sowpPieMulti = {
  attach: function (context, settings) {
    $('.stp-piemulti', context).once('sowpPieMulti').each(function() {
      var $chart = $(this);
      var label_default = $chart.html();
      var chart_js = false;
      var cutout = 50;
      var hover_timeout;
      var current_label = label_default;

      // Create all required markup.
      $chart.html(
        '<div class="stp-piemulti-inner">' +
        '<canvas class="stp-piemulti-chart"></canvas>' +
        '<span class="stp-piemulti-tooltip"></span>' +
        '<span class="stp-piemulti-label">' +
        '<span class="stp-piemulti-label-inner b-font">' +
        label_default +
        '</span></span>' +
        '</div>'
      );
      
      // Get all newly created html items.
      var $inner = $chart.find('.stp-piemulti-inner');
      var $canvas = $chart.find('canvas');
      var $tooltip = $chart.find('.stp-piemulti-tooltip');
      var $label = $chart.find('.stp-piemulti-label');
      var $label_inner = $chart.find('.stp-piemulti-label-inner');

      // Set label size.
      $label_inner.css({'width': cutout + '%'});

      // Read chart data values from HTML data attribute.
      var data = $chart.data('chart-data');
      
      var labels = [];
      var dataset = {
        data: [],
        backgroundColor: [],
        hoverBackgroundColor: []
      };
      
      $.each(data, function(index, item) {
        var item = item.split('|');
        var hover = tinycolor(item[1]).saturate(10).brighten(10).toString();

        labels[index] = item[2];
        dataset.data[index] = item[0];
        dataset.backgroundColor[index] = item[1];
        dataset.hoverBackgroundColor[index] = hover;
      });

      // Renders chart.
      var render_pie_chart = function() {
        if (!chart_js) {
          chart_js = new Chart($canvas.get(0).getContext('2d'), {
            type: 'pie',
            data: {
              labels: labels,
              datasets: [dataset]
            },
            options: {
              cutoutPercentage: cutout,
              legend: {
                display: false
              },
              elements: {
                arc: {
                  borderWidth: 0
                }
              },
              animation: {
                duration: 2000,
                easing: 'easeOutBounce'
              },
              responsive: true,
              maintainAspectRatio: false,
              tooltips: {
                enabled: false
              }
            }
          });

          $canvas.css({'width': '100%', 'height': '100%'});

          $canvas.get(0).onmousemove = function(event) {
            var element = chart_js.getElementsAtEvent(event);
            
            if (element[0] != undefined) {
              var label = element[0]['_view']['label'];
              
              label = '<span>' + label.replace(' ', '</span><span>') + '</span>';
              
              if (label != current_label) {
                $label_inner.stop().fadeTo(100, 0, function() {
                  $label_inner.html(label);
                  $label_inner.stop().fadeTo(100, 1);
                  current_label = label;
                });
              }
            }
            else if (label_default != current_label) {
              $label_inner.stop().fadeTo(100, 0, function() {
                $label_inner.html(label_default);
                $label_inner.stop().fadeTo(100, 1);
                current_label = label_default;
              });
            }
          };
          
          $canvas.mouseout(function() {
            $label_inner.stop().fadeTo(100, 0, function() {
              $label_inner.html(label_default);
              $label_inner.stop().fadeTo(100, 1);
              current_label = label_default;
            });
          });
        }
      };

      // Removes chart so new chart is added again and animates again.
      var unrender_pie_chart = function() {
        $canvas.remove();
        $inner.append('<canvas class="stp-piemulti-chart"></canvas>');
        $canvas = $chart.find('canvas');
        chart_js = false;
      };

      // On load animation.
      // Check if pie chart is in viewport + 150 pixels.
      setTimeout(function () {
        var is_in_viewport = Drupal.sowp.elemIsVisible($chart.get(0), 100);

        if (is_in_viewport || !Drupal.sowp.deviceCheck.is768plus()) {
          render_pie_chart();
        }
      }, 1000);
      
      // On scroll animation.
      $(document).scroll(function () {
        if (!Drupal.sowp.deviceCheck.is768plus()) {
          return;
        }
        
        // Check if pie chart is in viewport + 100 pixels.
        var is_in_viewport = Drupal.sowp.elemIsVisible($chart.get(0), 100);

        if (is_in_viewport) {
          setTimeout(function () {
            render_pie_chart();
          }, 500);
        }
        // Not in viewport - un-render chart so it animtes again when comes back into view.
        else if (!Drupal.sowp.elemIsVisible($chart.get(0), -300)) {
          unrender_pie_chart();
        }
      });
    });
  }
};



/**
 * Override of Div Slider behaviour from 2016.
 */
Drupal.behaviors.sowpDivSlider = {
  attach: function (context, settings) {
    $('.swp-divslider', context).once('sowpDivSlider').each(function() {
      var $slider = $(this);
      var $slides = $slider.find('> li');
      var $content = $slider.find('.divslider-content');
      var $content_alt = $slider.find('.divslider-alt-content');
      var activated = false;
      
      var all_closed_width = 370;
      var closed_width = 70;
      var total_width = $slider.width();

      // Slides click handling.
      $slides.each(function() {
        var $slide = $(this);
        var $button = $slide.find('button');
        var bg_src = $slide.find('.divslider-bg').attr('src');

        $button.click(function() {
          total_width = $slider.width();
          
          $slides.removeClass('active').css({
            'background-image': 'none',
            'width': closed_width + 'px'
          });
          $slide.addClass('active').css({
            'background-image': 'url(' + bg_src + ')',
            'width': (total_width - all_closed_width) + 'px'
          });

          if (Drupal.sowp.viewportWidth() <= 900 && activated == true) {
            $('html, body').animate({
              scrollTop: $slide.offset().top - 100
            }, 1000);
          }
          else {
            activated = true;
          }
        });
      });

      // Adapt to viewport width.
      var adaptWidth = function() {
        // Adapt slide items width.
        total_width = $slider.width();
        
        $slides.css({
          'background-image': 'none',
          'width': closed_width + 'px'
        });
        $slides.each(function() {
          var $slide = $(this);
          if ($slide.hasClass('active')) {
            var bg_src = $slide.find('.divslider-bg').attr('src');
            $slide.css({
              'background-image': 'url(' + bg_src + ')',
              'width': (total_width - all_closed_width) + 'px'
            });
          }
        });

        // Adapt slide content width.
        var slider_width = $slider.outerWidth();
        var collapsed_width = $slides.not('.active').eq(0).outerWidth(true);
        var content_width = slider_width - (collapsed_width * 5);

        $content.css({'width': content_width.toString() + 'px'});
        $content_alt.css({'width': content_width.toString() + 'px'});
      };

      $(window).resize(function() {
        setTimeout(function() {
          adaptWidth();
        }, 500);
      });

      setTimeout(function() {
        adaptWidth();
      }, 500);

      // Activate .active item on load.
      var $active = $slides.filter('.active');
      if ($active.length) {
        $active.eq(0).find('button').click();
      }
    });
  }
};



/**
 * Flip Cards behavior copied from 2016.
 */
Drupal.behaviors.sowpFlipCards = {
  attach: function (context, settings) {
    $('.swp-flipcards', context).once('sowpFlipCards').each(function() {
      var $flipcards = $(this);
      var $cards = $flipcards.find('li');
      var $flip_items = $flipcards.find('.flip-item');

      $cards.each(function() {
        var $card = $(this);
        var $flip_item = $card.find('.flip-item');

        $flip_item.click(function() {
          if ($flip_item.hasClass('flipped')) {
            $flip_item.removeClass('flipped');
            $card.removeClass('flipped');
          }
          else {
            $flip_items.not($flip_item).removeClass('flipped');
            $cards.not($card).removeClass('flipped');
            $flip_item.addClass('flipped');
            $card.addClass('flipped');
          }
        });
      });
    });
  }
};



/**
 * Share Cards behavior copied from 2017.
 */
Drupal.behaviors.sowpShareCards = {
    attach: function (context, settings) {
      $('.swp-sharecards-cards', context).once('sowpShareCards').each(function() {
        var $wrapper = $(this);
        var $cards = $wrapper.find('li .swp-sharecard-content');

        // Cards masonry.
        var cardsMasonryInit = function() {
          $wrapper.masonry({
            itemSelector: 'li',
            gutter: 0
          });
          $wrapper.masonry('reloadItems');
        };

        setTimeout(function() {
          cardsMasonryInit();
        }, 500);

        $(window).resize(function() {
          cardsMasonryInit();
        });


        // In viewport animation.
        $cards.each(function() {
          var $card = $(this);

          // On load animation.
          if (Drupal.sowp.deviceCheck.is768plus()) {
            $card.addClass('card-unanimated');

            // Check if card is in viewport + 150 pixels.
            setTimeout(function () {
              var is_in_viewport = Drupal.sowp.elemIsVisible($card.get(0), 150);

              if (is_in_viewport) {
                $card.removeClass('card-unanimated');
              }
            }, 1000);
          }

          // On scroll animation.
          $(document).scroll(function () {
            if (Drupal.sowp.deviceCheck.is768plus()) {
              // Check if card is in viewport + 150 pixels.
              var is_in_viewport = Drupal.sowp.elemIsVisible($card.get(0), 150);
              var wrapper_in_viewport = Drupal.sowp.elemIsVisible($wrapper.get(0), 150);

              if (is_in_viewport) {
                $card.removeClass('card-unanimated');
              }
              // Not in viewport - hide it so it animtes again when comes back into view.
              else {
                // Need to make sure we are already 300px away.
                if (!Drupal.sowp.elemIsVisible($wrapper.get(0), -300)) {
                  $cards.addClass('card-unanimated');
                }
              }
            }
          });
        });

        // Make sure cards are visible for small screens.
        var timeout;
        $(window).resize(function() {
          clearTimeout(timeout);
          timeout = setTimeout(function() {
            if (!Drupal.sowp.deviceCheck.is768plus()) {
              $cards.removeClass('card-unanimated');
            }
          }, 200);
        });
      });
    }
  };



})(jQuery);
