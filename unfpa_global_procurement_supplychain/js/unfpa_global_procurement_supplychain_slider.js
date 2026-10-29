(function ($, Drupal, drupalSettings) {

  'use strict';
  Drupal.behaviors.unfpa_global_procurement_supplychain = {
    attach: function (context, settings) {
      $('.autoplay.global-result-slider',context).once('unfpa_global_procurement_supplychain').not('.slick-initialized').slick({
        slidesToShow: 3,
        slidesToScroll: 3,
        autoplay: true,
        autoplaySpeed: 8000,
        responsive: [
          {
            breakpoint: 768,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2
            }
          },
          {
            breakpoint: 480,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          }
        ]
      });
      //keys results slider
      $('.slider.country-result-slide',context).once('unfpa_global_procurement_supplychain').not('.slick-initialized').slick({
        slidesToShow: 4,
        slidesToScroll: 4,
        autoplay: true,
        autoplaySpeed: 8000,
        responsive: [
          {
            breakpoint: 768,
            settings: {
              slidesToShow: 2,
              slidesToScroll: 2
            }
          },
          {
            breakpoint: 480,
            settings: {
              slidesToShow: 1,
              slidesToScroll: 1
            }
          }
        ]
      });

      // Close wrapper result selector
      $('.close-indi', context).once('unfpa_global_procurement_supplychain').click(function () {
        $(".wrapper-indicators").slideUp(300);
        $(".wrapper-result-selector").removeClass("active");
      });
      
      // Display country popover.
      $('.pencil',context).once('unfpa_global_procurement_supplychain').on('click', function () {
        $('.thepopover').toggle();
      });
      // Close country popover.
      $('.close-thepopover', context).once('unfpa_global_procurement_supplychain').click(function () {
        $(".thepopover").hide();
      });

      // Indicators title arrow click.
      function title_arrow() {
        $(".wrapper-indicators").slideToggle();
        $('.wrapper-result-selector').toggleClass('active');
      }
      $('.slct-indicators', context).once('unfpa_global_procurement_supplychain').click(function () {
        title_arrow();
      });

      $(".wrapper-result-selector", context).once('unfpa_global_procurement_supplychain').click(function () {
        $(".wrapper-indicators").slideToggle(800);
        $(".thepopover").css('display', 'none');
        $(this).toggleClass('active');
      });

      // Hides if outside results div.
      $(document).mouseup(function (e) {
        var container = $(".wrapper-indicators");
        var container2 = $(".wrapper-result-selector");
        // If the target of the click isn't the container nor a descendant of the container.
        if (!container.is(e.target) && container.has(e.target).length === 0 && container2.has(e.target).length === 0)
        {
          container.css("display", "none");
          $(".wrapper-result-selector").removeClass("active");
        } else if (container2.is(e.target) && container2.has(e.target).length === 0) {
          if (container2.hasClass('active')) {
            container.css("display", "none");
            $(".wrapper-result-selector").removeClass("active");
          } else {
            container.css("display", "block");
            $(".wrapper-result-selector").addClass("active");
          }
        }
      });

       // Show hide technical notes.
      $(".tech-note", context).once('unfpa_global_procurement_supplychain').on("click", function () {
        $(this).toggleClass('active');
        if ($(this).hasClass("active")) {
          $('.tech-dtl').slideDown();
        } else {
          $('.tech-dtl').slideUp();
        }
      });

      // function for smooth scroll go to and stop
      $('.animate-indi label, .countries-wrapper .col-countries-item',context).click(function () {
        $('html, body').animate({
          scrollTop: $('#map').offset().top
        }, 500);
        return false;
      });

       $("#AreaClickId .area h3", context).once('unfpa_global_procurement_supplychain').on("click", function () {
        var id =  $(this).attr('id');
        var region =  $(this).attr('data-region');
        update_archieved_map_results(id, region);
        $(".thepopover").hide();
      });
    }
  };
})(jQuery, Drupal, drupalSettings);


