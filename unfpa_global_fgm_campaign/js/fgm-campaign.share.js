/**
 * @file
 * UNFPA Global FGM Campaign Share Modal logic.
 */
(function ($) {



/**
 * Share Link behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignShareLink = {
  attach: function (context, settings) {
    $('.fgm-sharelink a', context).once('unfpaGlobalFgmCampaignShareLink').each(function () {
      var $link = $(this);

      $link.click(function (event) {
        // Get modal.
        var $modal = $('.fgm-sharemodal').eq(0);
        var $modal_img = $modal.find('.fgm-sharemodal-image img');
        var $modal_description = $modal.find('.fgm-sharemodal-description');
        var $modal_facebook = $modal.find('.fgm-sharemodal-button.facebook');
        var $modal_twitter = $modal.find('.fgm-sharemodal-button.twitter');

        // Get details from share link.
        var share_url_fb = $link.data('url-fb');
        var share_url_tw = $link.data('url-tw');
        var share_img = $link.data('img');
        var share_description = $link.find('.fgm-sharelink-description').text();

        // Apply details to modal.
        $modal_img.attr('src', share_img);
        $modal_description.html(share_description);
        $modal_facebook.attr('href', share_url_fb);
        $modal_twitter.attr('href', share_url_tw);

        // Show the modal.
        $modal.addClass('fgm-sharemodal-shown');
      });
    });
  }
};



/**
 * Share Modal behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignShareModal = {
  attach: function (context, settings) {
    $('.fgm-sharemodal', context).once('unfpaGlobalFgmCampaignShareModal').each(function () {
      var $modal = $(this);
      var $close = $modal.find('.fgm-sharemodal-close, .fgm-sharemodal-bg');
      var $share_button = $modal.find('.fgm-sharemodal-button');

      // Closing of modal.
      $close.click(function (event) {
        $modal.removeClass('fgm-sharemodal-shown');
      });

      // Opening share popup.
      $share_button.click(function(event) {
        event.preventDefault();

        var url = $(this).attr('href');

        // Setup popup options.
        var options = 'width=600,height=600,toolbars=0,scrollbars=1,resizable=1';

        // Open popup window.
        window.open(url, Drupal.t('Sharing'), options);
      });
    });
  }
};




})(jQuery);
