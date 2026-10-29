/**
 * @file
 * UNFPA Global FGM Campaign map section js logic.
 */
(function ($) {



/**
 * Map section behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignMap = {
  attach: function (context, settings) {
    $('.fgm-map', context).once('unfpaGlobalFgmCampaignMap').each(function () {
      var $wrapper = $(this);
      var $form = $wrapper.find('form');
      var $select = $wrapper.find('.form-item-country select');
      var $select_label = $wrapper.find('.form-item-country label');
      var $result = $wrapper.find('.form-item-result');

      // Initializes pledge map.
      var pledge_map = simplemaps_worldmap.create();
      pledge_map.load();

      // Get map pledges from drupal settings.
      var pledges = settings.unfpa_global_fgm_campaign.pledge_count;

      // Define which countries are missing from the map plugin.
      // These countries we have manually marked on the map as locations.
      // They require different handling when in comes to setting their
      // ammounts of pledges and when zooming to them.
      var missing_countries = ['BH', 'MO', 'GU', 'KI', 'MH', 'FM', 'WS', 'SM', 'TV'];

      // Define small countries which exist in map plugin but have additional
      // locations marked on the map for easier hovering.
      var small_countries = ['CV', 'KM', 'MV', 'SC', 'ST'];

      // Function for checking if country is missing country.
      var check_country_missing = function(country_id) {
        return missing_countries.indexOf(country_id) > -1;
      };

      // Function for checking if country is small country.
      var check_country_small = function(country_id) {
        return small_countries.indexOf(country_id) > -1;
      };

      // Function that returns legend color for provided ammount of pledges.
      var get_ammount_color = function(ammount) {
        if (ammount >= 201) {
          return '#8f52a0';
        }
        else if (ammount >= 151) {
          return '#b282b9';
        }
        else if (ammount >= 101) {
          return '#c49bc9';
        }
        else if (ammount >= 51) {
          return '#d2b5d6';
        }
        else if (ammount >= 1) {
          return '#dfcce4';
        }

        return '#cbcccc';
      };

      // Function that sets map colors based on pledges.
      var map_set_colors = function() {
        // Process pledges.
        $.each(pledges, function(country_id, ammount) {
          // Treat Kosovo as Serbia.
          if (country_id == 'XK') {
            country_id = 'RS';
          }
          var color = get_ammount_color(ammount);
          var country_missing = check_country_missing(country_id);
          var country_small = check_country_small(country_id);

          // Append space to ammount. This fixes simplemaps bug where ammounts
          // of '0' were not showing.
          ammount = ammount.toString() + ' ';

          // If country is missing (marked as locaiton).
          if (check_country_missing(country_id)) {
            pledge_map.mapdata.locations[country_id]['color'] = color;
            pledge_map.mapdata.locations[country_id]['hover_color'] = color;
            pledge_map.mapdata.locations[country_id]['description'] = ammount;
          }
          // If country is small (marked as country + location).
          else if (check_country_small(country_id)) {
            pledge_map.mapdata.locations[country_id]['color'] = color;
            pledge_map.mapdata.locations[country_id]['hover_color'] = color;
            pledge_map.mapdata.locations[country_id]['description'] = ammount;
            pledge_map.mapdata.state_specific[country_id]['color'] = color;
            pledge_map.mapdata.state_specific[country_id]['hover_color'] = color;
            pledge_map.mapdata.state_specific[country_id]['description'] = ammount;
          }
          // Normal country (not small, not missing).
          else {
            pledge_map.mapdata.state_specific[country_id]['color'] = color;
            pledge_map.mapdata.state_specific[country_id]['hover_color'] = color;
            pledge_map.mapdata.state_specific[country_id]['description'] = ammount;
          }
        });

        // Refrsh map.
        pledge_map.refresh();
      };

      // Function that zoom map to selected country or zoom out if no id.
      var map_zoom_country = function(country_id) {
        // Zoom out if no country.
        if (country_id == '') {
          pledge_map.back();
        }
        // If country then zoom in.
        else {
          // Treat Kosovo as Serbia.
          if (country_id == 'XK') {
            country_id = 'RS';
          }
          pledge_map.back(function() {
            // If missing country then zoom to marked location.
            if (check_country_missing(country_id)) {
              pledge_map.location_zoom(country_id, 4);
            }
            // Normal country (not small, not missing).
            else {
              pledge_map.state_zoom(country_id);
            }
          });
        }
      };

      // Function that updates the map pledges count on pledge form submit.
      // Argument is country for which there should be +1 pledge.
      var map_plus_one = function(country_id) {
        // Treat Kosovo as Serbia.
        if (country_id == 'XK') {
          country_id = 'RS';
        }
        pledges[country_id] = pledges[country_id] + 1;
        map_set_colors();
      };

      // Set map colors on load.
      map_set_colors();

      // React on user selecting country.
      $select.change(function() {
        var country_id = $select.val();
        map_zoom_country(country_id);
      });

      // React on form being submitted successfully.
      // When there are no validation errors, Drupal ajax command changes
      // the value of this hidden field, from 0 to 1 or 2:
      // - 1 means form submitted successfully and country got +1.
      // - 2 means form submitted successfully but no +1 for country.
      $result.change(function() {
        // If country got plus one too (value of 1) then +1 the country on map.
        if ($result.val() === '1') {
          var country_id = $select.val();
          map_plus_one(country_id);
        }

        // Map should always zoom out on successfull submission.
        map_zoom_country('');
      });

      // Improved look for for mselect.
      $select.chosen({
        placeholder_text_single: ' ',
        no_results_text: Drupal.t('No results match'),
        'width': '100%'
      });

      // Fix for chosen select not opening on field label click.
      $select_label.click(function(event) {
        $form.find('.chosen-container a').mousedown();
      });
    });
  }
};



/**
 * Additional behaviour for styling pledge form.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignPledgeForm = {
  attach: function (context, settings) {
    $('.fgm-form', context).once('unfpaGlobalFgmCampaignPledgeForm').each(function () {
      var $form = $(this);
      var $items = $form.find('.fgm-form-item');

      $items.each(function () {
        var $item = $(this);
        var $field = $item.find('.form-text, .form-select');

        var react = function() {
          if ($field.val() != '') {
            $item.addClass('fgm-form-filled');
          }
          else {
            $item.removeClass('fgm-form-filled');
          }
        };

        react();

        $field.focus(function() {
          $item.addClass('fgm-form-filled');
        });

        $field.blur(function() {
          react();
        });

        $field.change(function() {
          react();
        });
      });
    });
  }
};



})(jQuery);
