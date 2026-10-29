jQuery(document).ready(function($){
    console.log('custom player loaded');
    loadScript();
});

/**plugin function 
 * include YT library
*/
function loadScript() {
	if (typeof(YT) == 'undefined' || typeof(YT.Player) == 'undefined') {
		var tag = document.createElement('script');
		tag.src = "https://www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
	}
}
function loadPlayer() {
    window.onYouTubePlayerAPIReady = function() {
        onYouTubePlayer();
    };
}
jQuery(function () {
    loadScript();
});
var player;
function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
    height: '390',
    width: '640',
    // videoId: 'M7lc1UVf-VE',
    playerVars: {
        'playsinline': 1
    },
    events: {
        'onReady': onPlayerReady,
        'onStateChange': onPlayerStateChange
    }
    });
}
// 4. The API will call this function when the video player is ready.
function onPlayerReady(event) {
    player.playVideo();
    console.log('player ready');
}
// 5. The API calls this function when the player's state changes.
//    The function indicates that when playing a video (state=1),
//    the player should play for six seconds and then stop.
var done = false;
function onPlayerStateChange(event) {
    if(event.data == 0){
        // var url = baseUrl+'payment';
        // var transaction_id = $('#Application_Transaction_ID').val();
        // startAssessment(transaction_id);

        // var NWin = window.open(url, '', 'height=800,width=800');
        // if (window.focus) 
        // {
        //     NWin.focus();
        // }
        // return false;
	    // jQuery('#default-assessment-section').css('display','block');
        // jQuery('#course-video-container').html('');
        document.location.href= '/order-summary';
    }

}
function stopVideo() {
    console.log('video stop');
    player.stopVideo();

}
// player.addEventListener("onStateChange", function(state){
//     console.log('add event');
//     if(state === 0){
//         console.log('video completed');

//     }
// });

function redirecToPayment(url){
	var NWin = window.open(url, '', 'height=800,width=800');
	if (window.focus) 
	{
		NWin.focus();
	}
	return false;
}
