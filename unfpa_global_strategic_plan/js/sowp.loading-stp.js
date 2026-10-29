/**
* @file
* Image preloading and loading bar support (SOWP 2017).
*/
(function ($) {



/**
 * Initialize Drupal.sowp object if it doesn't exist.
 */
Drupal.sowp = Drupal.sowp || {};



/**
 * Create preloader HTML element and create ProgressBar class item.
 */
Drupal.sowp.preloaderPrepare = function () {
  var $preloader = $(
    '<div class="stp-load-overlay"></div>' +

    '<div class="stp-load">' +
    '<div class="stp-load-inner">' +
    '<div class="stp-load-inner-2">' +
    '<div class="stp-load-content">' +
    '<div class="stp-load-content-inner">' +
    '<div class="stp-load-content-inner-2">' +

    '<div class="stp-load-progress" id="stp-load-progress"></div>' +
    
    '<div class="stp-load-title b-font">' +
    '<div class="stp-load-title-inner">' +
    Drupal.t('Vision 2030') +
    '</div>' +
    '</div>' +

    '</div>' +
    '</div>' +
    '</div>' +
    '</div>'
  );

  $('body').prepend($preloader);

  // Uses lib.progressbar.js.
  Drupal.sowp.stpProgressBar = new ProgressBar.Circle('#stp-load-progress', {
    color: '#fff',
    strokeWidth: 7,
    trailColor: '#7f99b4',
    trailWidth: 7,
    duration: 800,
    easing: 'easeOut'
  });
};



/**
 * Create preloader HTML element right away, without using Drupal behaviours.
 */
Drupal.sowp.preloaderPrepare();



/**
 * Preload required images.
 */
Drupal.sowp.preloaderPreload = function () {
  var images = [];

  $('img').each(function() {
    var src = $(this).attr('src');
    images.push(src);
  });

  Drupal.sowp.preloadImages(images, Drupal.sowp.preloaderFinished);
};



/**
 * Preloads images and then calls specified callback function.
 */
Drupal.sowp.preloadImages = function(images, callback) {
  var checklist = images.length;
  var count = checklist;
  var url = location.href;
  var hash = url.split('#!/');
  
  // Get selectors.
  var $loading = $('.swp-load');
  var $progress_bar = $('.swp-load-bar-inner');
  var $count_display = $('.swp-load-count');
  
  // Function to call upon single image load (or fail to retrieve image).
  var react = function() {
    checklist--;

    // Progress indicator handling.
    var percent = Math.floor((count - checklist) / count * 100);

    // Percent display.
    //$count_display.text(percent + '%');
    
    // Progress bar.
    Drupal.sowp.stpProgressBar.animate(percent / 100);

    // Keep scrolling on top (if no url hash).
    if (hash[1] == undefined) {
      window.scrollTo(0, 0);
    }

    // If we are done with preloading.
    if (checklist == 0) {
      callback();
    }
  };
  
  $.each(images, function() {
    $('<img>')
      .on('load', function() {
        react();
      })
      .on('error', function() {
        react();
      })
      .attr({src: this});
  });
};



/**
 * Callback to run after images loaded.
 */
Drupal.sowp.preloaderFinished = function () {
  // Fade out loading screen after a moment.
  setTimeout(function() {
    $('.stp-load-overlay').fadeOut(4000);
    $('.stp-load').fadeOut(4000, function() {
      if ($(window).scrollTop() == 0) {
        $('.stp-entry-autoplay').click();
      }
    });
  }, 800);
};



/**
 * Launch preloader on DOM ready, without using Drupal behaviours.
 */
$(document).ready(function() {
  Drupal.sowp.preloaderPreload();
});



})(jQuery);
