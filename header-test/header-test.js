// @codekit-prepend "../js/third-party/jquery-1.11.1.min.js";

$(document).ready(function (){
			
	var banner_updates = 0;
	
	adjust_home_banner = function() {

		banner_updates = banner_updates + 1;
		$(".info").text(banner_updates + " banner updates");
					
		var page_offset = $(window).scrollTop(); 
		var banner_height = $(".banner").height();
		var page_height = $(".page").height();
		
		if (page_offset < banner_height) {
			$("body").css("height", page_height);
			$(".page").addClass("fixed");
		} else {
			$("body").css("height", "");
			$(".page").removeClass("fixed");
		}
	}

	$(window).on("load resize scroll", function() {
		adjust_home_banner();
	});
	
/*
	setInterval(function() {
		adjust_home_banner();
	}, 10);
*/
/*
	$('body').on({
		'touchmove': function(e) { 
			adjust_home_banner();
		}
	});
*/
/*
	$("body").addEventListener('gesturechange', function() {
		adjust_home_banner();
	});
*/
		
});
