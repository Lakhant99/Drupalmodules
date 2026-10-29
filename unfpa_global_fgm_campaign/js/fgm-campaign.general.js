/**
 * @file
 * UNFPA Global FGM Campaign general js logic.
 */
(function ($) {



/**
 * Initialize FGM object if it doesn't exist.
 */
Drupal.unfpaGlobalFgmCampaign = Drupal.unfpaGlobalFgmCampaign || {};



/**
 * Measures real viewport width.
 *
 * Measuring just document width would return width without scrollbar
 * but we want width including scrollbar so it is consistent with
 * how CSS media querries work.
 *
 * @return
 *   Viewport width in pixels.
 */
Drupal.unfpaGlobalFgmCampaign.getViewportWidth = function () {
  var element = window;
  var property = 'innerWidth';

  if (!('innerWidth' in window)) {
    element = document.documentElement || document.body;
    property = 'clientWidth';
  }

  return element[property];
};



/**
 * Measures real viewport height.
 *
 * Measuring just document width would return width without scrollbar
 * but we want width including scrollbar so it is consistent with
 * how CSS media querries work.
 *
 * @return
 *   Viewport height in pixels.
 */
Drupal.unfpaGlobalFgmCampaign.getViewportHeight = function () {
  var element = window;
  var property = 'innerHeight';

  if (!('innerHeight' in window)) {
    element = document.documentElement || document.body;
    property = 'clientHeight';
  }

  return element[property];
};



/**
 * Device viewport detection.
 *
 * @return
 *   True is viewport is wide, false otherwise.
 */
Drupal.unfpaGlobalFgmCampaign.isWideViewport = function () {
  return (Drupal.unfpaGlobalFgmCampaign.getViewportWidth() >= 768);
};



/**
 * Device type detection.
 *
 * @return boolean
 *   True if device is touch mobile, false otherwise.
 */
Drupal.unfpaGlobalFgmCampaign.isTouchMobile = function () {
  var mobile = (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
  var touch = ('ontouchstart' in window) || ('msmaxtouchpoints' in window.navigator);

  return (mobile && touch);
};



/**
 * Checks if element is vertically within the viewport.
 *
 * See http://stackoverflow.com/a/21627295.
 *
 * @param el
 *   DOM element to test against.
 * @param delay
 *   Allows to to delay (in pixels) the moment when el becomes
 *   treated as visible/invisible in viewport.
 *
 * @return boolean
 *   True if element is visible in viewport, false otherwise.
 */
Drupal.unfpaGlobalFgmCampaign.isElementVisible = function (el, delay) {
  var top = el.getBoundingClientRect().top;
  var bottom = el.getBoundingClientRect().bottom;

  // Take into account that this object might be not visible
  // because of parent container overflow hidden.
  var rect;
  el = el.parentNode;

  do {
    rect = el.getBoundingClientRect();
    if (top <= rect.bottom === false) {
      return false;
    }
    el = el.parentNode;
  } while (el != document.body);

  // If object is visible in its parent container then return regular calculation.
  return top + delay <= document.documentElement.clientHeight && bottom >= 0 + delay;
};



/**
 * Function for calculating parallax movements.
 *
 * The x_value is converted to percent and then converted to point on the
 * new scale with range from out_min to out_max.
 *
 * @param x_min
 *   Scale minimal value. If x_value is less, it will be treated as this value.
 * @param x_max
 *   Scale maximal value. if x_value is more, it will be treated as this value.
 * @param x_value
 *   The value to process.
 * @param out_min
 *   Output scale minimal value.
 * @param out_max
 *   Output scale maximal value.
 *
 * @return float
 *   Point on the new scale with range from out_min to out_max.
 */
Drupal.unfpaGlobalFgmCampaign.calculateShift = function (x_min, x_max, x_value, out_min, out_max) {
  // If x_min is bigger than x_max then input is invalid so return out_min.
  if (x_min > x_max) {
    return out_min;
  }
  // Process remaining normal calculations.
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



/**
 * Sets equal height for provided jquery items.
 *
 * @param $items object
 *   jQuery object containing items to select equal height for.
 */
Drupal.unfpaGlobalFgmCampaign.equalHeight = function ($items) {
  if ($items.length < 2) {
    return;
  }

  // First reset whatever might have been previously set.
  $items.css({'min-height': 0});

  // Get top height.
  var top_height = 0;
  $items.each(function () {
    var current_height = $(this).height();
    if (top_height < current_height) {
      top_height = current_height;
    }
  });

  // Set new min height.
  $items.css({'min-height': top_height});
};



})(jQuery);
