/**
 * @file
 * UNFPA Global FGM Campaign Actions logic.
 */
(function ($) {




/**
 * Actions behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignActions = {
  attach: function (context, settings) {
    $('.fgm-actions', context).once('unfpaGlobalFgmCampaignActions').each(function () {
      var $wrapper = $(this);
      var $contents = $wrapper.find('.fgm-action-content');
      var timeout = null;

      // Function for reacting on resize, onload.
      var react = function() {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
          if (Drupal.unfpaGlobalFgmCampaign.isWideViewport()) {
            Drupal.unfpaGlobalFgmCampaign.equalHeight($contents);
          }
          else {
            $contents.css('min-height', 0);
          }

        }, 500);
      };

      // React on load.
      react();

      // React on resize.
      $(window).resize(react);
    });
  }
};




})(jQuery);
