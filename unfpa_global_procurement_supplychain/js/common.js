(function ($) {
    Drupal.behaviors.common = {
        attach: function (context, settings) {
            "use strict";

            // Procurement tab open though id past on url.
            $( ".sidebar__nav > li" ).each(function( index ) {
                var hash = $(location).attr('hash');
                var href = $(this).find('a').attr("href");
                if(hash == href) {
                    $(this).find('a').click();
                }
              });

            // Procurement tab add hash in url.
              $('.sidebar__nav > li a').click(function() { 
                var hash = $(this).attr('href');
                var url = window.location.href;
                var res = url.split("#");
                var link = res[0];
                window.location = link+hash;
                var tabs_offset = $(".tab-content").offset();
                scrollTo(tabs_offset.left, tabs_offset.top);
            });

            // Populate images from data attributes.
            var scrolled = $(window).scrollTop();
            $('.hero-image', context).once('common').each(function (index) {
                var imageSrc = $(this).data('image-src');
                var imageHeight = $(this).data('height');
                $(this).css('background-image', 'url(' + imageSrc + ')');
                $(this).css('height', imageHeight);

                // Adjust the background position.
                var initY = $(this).offset().top;
                var height = $(this).height();
                var diff = scrolled - initY;
                var ratio = Math.round((diff / height) * 100);
                $(this).css('background-position', 'center ' + parseInt(-(ratio * 1.2)) + 'px');
            });

            // See Related Resource button JS
            $(".res-btn", context).click(function() {
                $('html, body').animate({
                    scrollTop: $("#related-resources-content").offset().top
                }, 1000);
            });

            // Attach scroll event to window. Calculate the scroll ratio of each element
            // and change the image position with that ratio.
            $(window).scroll(function () {
                var scrolled = $(window).scrollTop();
                $('.hero-image').each(function (index, element) {
                    var initY = $(this).offset().top;
                    var height = $(this).height();
                    var endY = initY + $(this).height();

                    // Check if the element is in the viewport.
                    var visible = isInViewport(this);
                    if (visible) {
                        var diff = scrolled - initY;
                        var ratio = Math.round((diff / height) * 100);
                        $(this).css('background-position', 'center ' + parseInt(-(ratio * 1.2)) + 'px');
                    }
                });
            });
            
            $('.modal-body .close').on("click", function () {
              $('body,#successModal').css('overflow', 'auto');
            });

            // Check if the element is in the viewport.
            function isInViewport(node) {
                // Am I visible? Height and Width are not explicitly necessary in visibility
                // detection, the bottom, right, top and left are the essential checks. If an
                // image is 0x0, it is technically not visible, so it should not be marked as
                // such. That is why either width or height have to be > 0.
                var rect = node.getBoundingClientRect();
                return (
                        (rect.height > 0 || rect.width > 0) &&
                        rect.bottom >= 0 &&
                        rect.right >= 0 &&
                        rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
                        rect.left <= (window.innerWidth || document.documentElement.clientWidth)
                        );
            }
        }
    };
})(jQuery);