/**
 * @file
 * UNFPA Global FGM Campaign standalone map section additional js logic.
 */
(function ($) {



/**
 * Standalone map section additional behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignMapStandalone = {
  attach: function (context, settings) {
    $('.fgm-map', context).once('unfpaGlobalFgmCampaignMapStandalone').each(function () {
      var $wrapper = $(this);
      var $form = $wrapper.find('.fgm-form-form');
      var $success = $wrapper.find('.fgm-form-success');
      var $result = $wrapper.find('.form-item-result');
      var $form_text = $wrapper.find('.form-text');
      var $form_select = $wrapper.find('.form-select');
      var reset_timeout = null;
      var idle_timeout = null;

      // Function that resets the form.
      var reset_form = function() {
        // Clear form fields.
        // Invoke blur and change events so labels reset their position.
        $form_text.val('').blur().change();
        $form_select.val('').blur().change().trigger('chosen:updated');

        // Remove error classes.
        $wrapper.find('.error').removeClass('error');

        // Hide submit messages, show back form.
        $success.addClass('element-hidden');
        $wrapper.find('.messages').remove();
        $form.removeClass('fgm-form-form-hidden');
      };

      // React on form being submitted successfully.
      // When there are no validation errors, Drupal ajax command changes
      // the value of this hidden field. When that happens, start countdown
      // timer, that will reset the form and hide submit message after 10 sec.
      $result.change(function() {
        clearTimeout(reset_timeout);
        reset_timeout = setTimeout(function() {
          reset_form();
        }, 10000);
      });

      // Additionally if user is being idle for two minutes, then it means
      // they have probably left. In such case, scroll the page back to top,
      // so when a new user comes into the browser, they will see top of page.
      var reset_idle = function() {
        clearTimeout(idle_timeout);
        idle_timeout = setTimeout(function() {
          $('html, body').animate({ scrollTop: 0 }, 500);
          reset_form();
        }, 120000);
      };

      // If user is interacting with the page, then keep resetting idle timeout.
      document.onload = reset_idle;
      document.onmousemove = reset_idle;
      document.onmousedown = reset_idle;
      document.ontouchstart = reset_idle;
      document.onclick = reset_idle;
      document.onscroll = reset_idle;
      document.onkeypress = reset_idle;
    });
  }
};



})(jQuery);
