/**
 * @file
 * UNFPA Global FGM Campaign map section js logic.
 */
(function ($) {



/**
 * Prevalance map behaviour.
 */
Drupal.behaviors.unfpaGlobalFgmCampaignPrevalance = {
  attach: function (context, settings) {
    $('.fgm-prevalance', context).once('unfpaGlobalFgmCampaignPrevalance').each(function () {
      var $wrapper = $(this);

      // Initializes prevalance map.
      simplemaps_worldmap_mapdata.main_settings.div = 'fgm-prevalance-worldmap';
      var map = simplemaps_worldmap.create();
      map.load();

      // Get map data from drupal settings.
      var data = drupalSettings.unfpa_global_fgm_campaign.prevalance_data;

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

      // Function that returns legend color for provided color id.
      var get_hex_color = function(color_id) {
        if (color_id === 3) {
          return '#329cab';
        }
        else if (color_id === 2) {
          return '#5932ab';
        }
        else if (color_id === 1) {
          return '#e1a33d';
        }

        return '#cbcccc';
      };

      // Function that sets map colors based on pledges.
      var map_set_colors = function() {
        // Process map data.
        $.each(data, function(country_id, country_data) {
          var color_id = parseInt(country_data.color);
          var color = get_hex_color(color_id);
          var value = country_data.value;

          var country_missing = check_country_missing(country_id);
          var country_small = check_country_small(country_id);

          // If country is missing (marked as locaiton).
          if (check_country_missing(country_id)) {
            map.mapdata.locations[country_id]['color'] = color;
            map.mapdata.locations[country_id]['hover_color'] = color;
            map.mapdata.locations[country_id]['description'] = value;
          }
          // If country is small (marked as country + location).
          else if (check_country_small(country_id)) {
            map.mapdata.locations[country_id]['color'] = color;
            map.mapdata.locations[country_id]['hover_color'] = color;
            map.mapdata.locations[country_id]['description'] = value;
            map.mapdata.state_specific[country_id]['color'] = color;
            map.mapdata.state_specific[country_id]['hover_color'] = color;
            map.mapdata.state_specific[country_id]['description'] = value;
          }
          // Normal country (not small, not missing).
          else {
            map.mapdata.state_specific[country_id]['color'] = color;
            map.mapdata.state_specific[country_id]['hover_color'] = color;
            map.mapdata.state_specific[country_id]['description'] = value;
          }
        });

        // Refrsh map.
        map.refresh();
      };

      // Set map colors on load.
      map_set_colors();
    });
  }
};



})(jQuery);
