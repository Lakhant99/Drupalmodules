//function to click on nav button and show side navigation on mobile
(function ($) {
  $(document).ready(function () {

    // Search by currency (currency modal).
    $("#currency-search-bar").keyup(function() {
      // Retrieve the input field text and reset the count to zero
      var filter = $(this).val(),
      count = 0;

      // Loop through the comment list
      $('#currency-list li').each(function() {
        // If the list item does not contain the text phrase fade it out
        if ($(this).text().search(new RegExp(filter, "i")) < 0) {
          $(this).hide();  // MY CHANGE

          // Show the list item if the phrase matches and increase the count by 1
        } else {
          $(this).show(); // MY CHANGE
          count++;
        }

      });

    });
    // maintaining window scroll position and lock scroll.
    var $window = $(window), previousScrollTop = 60, scrollLock = false;
    $window.scroll(function (event) {
      if (scrollLock) {
        $window.scrollTop(previousScrollTop);
      }
      previousScrollTop = $window.scrollTop();
    });

    //share popup function
    $("#btn_share, #add-voice, #mk-donation").click(function () {
      $('.overlay-white').show();
      $('.popup-wrapper').show();
      scrollLock = !scrollLock;
    });

    $("#otp-currency-btn, #mp-currency-btn").click(function () {
      $('.overlay-white').show();
      $('.popup-wrapper').show();
      scrollLock = !scrollLock;
    });

    $("#btnClose").click(function () {
      $('.overlay-white').hide();
      $('.popup-wrapper').hide();
      scrollLock = !scrollLock;
    });

    // wrap the input in a new div that we can style to hide the default checkboxes
    $(".option").wrap("<div class='new'></div>");

    // add a span after each checkbox that we can style as our new checkboxes
    $('input[type="checkbox"]').after('<span class="check-box"></span>');
    $('input[type="radio"]').after('<span class="check-box"></span>');

    //
    $(".btn-group > .btn").click(function () {
      $(this).addClass("active");
      $(this).siblings().removeClass("active");
    });

    // code to show and hide donate type one-time or monthly.
    $('.one-time-wrapper').show();
    $('.one-time').click(function () {
      $(this).addClass('active');
      $(this).siblings().removeClass('active');
      if ($(this).hasClass("active")) {
        $('.one-time-wrapper').show();
        $('.monthly-wrapper').hide();
      }
      else {
        $('.one-time-wrapper').hide();
      }
    });
    $('.monthly').click(function () {
      $(this).addClass('active');
      $(this).siblings().removeClass('active');
      if ($(this).hasClass("active")) {
        $('.monthly-wrapper').show();
        $('.one-time-wrapper').hide();
      }
      else {
        $('.monthly-wrapper').hide();
      }
    });

    //code for donate amount tab and separate container.
    $('.mkd-tab-one .btn').click(function () {
      var tab_id = $(this).attr('data-tab');

      $('.mkd-tab-one .btn').removeClass('current');
      $('.tab-content-one').removeClass('current');

      $(this).addClass('current');
      $("#" + tab_id).addClass('current');
    })

    $('.mkd-tab-month .btn').click(function () {
      var tab_id = $(this).attr('data-tab');

      $('.mkd-tab-month .btn').removeClass('current');
      $('.tab-content-month').removeClass('current');

      $(this).addClass('current');
      $("#" + tab_id).addClass('current');
    })

    $('#firstname-error-wrap').hide();
    $('#otheramount-error-wrap').hide();
    $('#lastname-error-wrap').hide();
    $('#email-error-wrap').hide();

    $(".btn-blk .primary-btn").click(function(){
      $(".amount02").slideToggle();
      $(".amount-info").toggleClass("active");
      $(".btn-blk .primary-btn").hide();
    });

    var project1 = $('#makeDonation').offset();
    var $window = $(window);
    $window.scroll(function() {
        if ( $window.scrollTop() >= project1.top) {
            $(".heart").addClass("start");
        }
    });

    $("#MonthlyPayClick").click(function() {
      $('html,body').animate({
          scrollTop: $(".content02 h2").offset().top},
          'slow');
      $(".heart").addClass("start");
    });

    //function for donate page half banner button scroll to page.
    $(".hero__button").click(function () {
        $('html, body').animate({
            scrollTop: $("#makeDonation").offset().top
        }, 2000);
    });
    $(".hero__anchor").click(function () {
        $('html, body').animate({
            scrollTop: $("#readContent").offset().top
        }, 2000);
    });

    $(".donate-nowBtn").click(function () {
        $('html, body').animate({
            scrollTop: $("#makeDonation").offset().top
        }, 1000);
    });

    // Donate popup overlay toggle
    $("#otp-currency-btn").click(function(){
      $(".overley-white").show();
    });
    $("#btnClose").click(function(){
      $(".overley-white").hide();
    });

  });
})(jQuery);
