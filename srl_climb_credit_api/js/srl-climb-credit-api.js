(function ($, Drupal, drupalSettings) {
    'use strict';
    Drupal.behaviors.srl_climb_credit_api = {
      attach: function (context, settings) {
        $('#table_id',context).DataTable();
      }
    };
})(jQuery, Drupal, drupalSettings);