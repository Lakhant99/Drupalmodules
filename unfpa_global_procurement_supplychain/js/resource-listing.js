(function ($, Drupal) {
  Drupal.behaviors.resource_listing = {
    attach: function (context,settings) {
      $('.resource-document',context).once('resource').on('change',function() {
        var language_url = $('option:selected', this).attr('url');
        console.log(language_url);
        $(this).parents('.sessionDropdown').siblings('.doc-download-icon').find('a').attr('href',language_url);
      });
    }
  }
})(jQuery, Drupal);