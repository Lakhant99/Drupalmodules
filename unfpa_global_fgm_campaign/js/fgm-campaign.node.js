/**
 * @file
 * UNFPA Global FGM Campaign node subpage logic.
 */
(function ($) {




/**
 * Node subpage image slider behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignSubpageSlider = {
  attach: function (context, settings) {
    $('.fgm-subpage-slider', context).once('unfpaGlobalFgmCampaignSubpageSlider').each(function () {
      var $wrapper = $(this);
      var $slider = $wrapper.find('.fgm-subpage-slider-slider');
      var $slides = $wrapper.find('.fgm-subpage-slider-slider li');
      var $cycle = $wrapper.find('.fgm-subpage-slider-slider ul');
      var $caption = $wrapper.find('.fgm-subpage-slider-caption');
      var $credit = $wrapper.find('.fgm-subpage-slider-credit');

      // No slider if just one slide.
      if (!$slides.length > 1) {
        return;
      }

      // Build controls.
      var $prev = $('<a class="fgm-subpage-slider-prev" href="javascript:;"></a>');
      $prev.appendTo($slider);
      var $next = $('<a class="fgm-subpage-slider-next" href="javascript:;"></a>');
      $next.appendTo($slider);
      var $pager = $('<div class="fgm-subpage-slider-pager"></div>');
      $pager.appendTo($slider);

      // Cycle.
      $cycle.cycle({
        pauseOnHover: true,
        speed: 1000,
        timeout: 0,
        fx: 'fade',
        slides: '> li',
        swipe: true,
        autoHeight: 'calc',
        prev: $prev,
        next: $next,
        pager: $pager,
        log: false
      });

      // Animate charts after slide change.
      $cycle.on('cycle-after',
        function (event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag) {
          var $curr_slide = $(incomingSlideEl);
          var $slide_caption = $curr_slide.find('.fgm-subpage-slider-slide-caption');
          var $slide_credit = $curr_slide.find('.fgm-subpage-slider-slide-credit');

          $caption.html($slide_caption.html());
          $credit.html($slide_credit.html());
        }
      );
    });
  }
};




})(jQuery);
