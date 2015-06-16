// @codekit-prepend "third-party/jquery-1.11.1.min.js";
/* 
	modernizr includes defaults, plus:
	Inline SVG, SVG, Media Queries, Touch Events
*/
// @codekit-prepend "third-party/modernizr.custom.js";
// @codekit-prepend "third-party/matchmedia.js";
// @codekit-prepend "third-party/picturefill.min.js";
// @codekit-prepend "third-party/viewport-units-buggyfill.js";
// @codekit-prepend "third-party/owl-carousel-2/owl.carousel.js";

// set JS class for CSS
//$('HTML').addClass('JS');

$(document).ready(function (){

	// CAN USE THIS TO DETECT IE11 IF IT COMES TO IT!
	if (!!navigator.userAgent.match(/Trident\/7\./)) {
		$("html").addClass("ie11");
	};

	old_ie = false;
	if ($("html").hasClass("ie8") || $("html").hasClass("ie7")) {
		old_ie = true;
	}

	window.viewportUnitsBuggyfill.init();

	/******************************************************************************************
	SCREEN SIZE CHANGES
	******************************************************************************************/

	var medium_screen = 650;
	var large_screen = 1000;
	var nav_break_point = 650;
	
	var screen_size;

	set_screen_size = function () {
		if (Modernizr.mq('only all and (min-width: ' + medium_screen + 'px)') && screen_size != "large") {
			screen_size = "large";
			// actions for when switching to large screen here...
		} if (Modernizr.mq('only all and (min-width: ' + medium_screen + 'px) and (max-width: ' + (large_screen - 1) + 'px)') && screen_size != "medium") {
			screen_size = "medium";
			// actions for when switching to medium screen here...
		} else if (Modernizr.mq('only all and (max-width: ' + (medium_screen - 1) + 'px)') && screen_size != "small") {
			screen_size = "small";
			// actions for when switching to small screen here...
		}
	};
	if (!old_ie) {
		$(window).resize(function() {
			set_screen_size();
		});
		set_screen_size();
	}
	

	/******************************************************************************************
	NAV
	******************************************************************************************/

	$(".header .title").click(function(e) {
		$(".header").toggleClass("open");
		// clicking "ATF" on mobile opens and closes nav rather than taking you home
		if (screen_size == "small") {
			e.preventDefault();
		}
	});
	
	// clicking outside of menu closes it
	$("html").click(function() {
		$(".header").removeClass("open");
	});
	
	$(".header").click(function(e){
		e.stopPropagation();
	});

	// show title only after scrolling page a bit
	$(window).on("scroll", function() {
		if ($(this).scrollTop() < 100) {
			$("body").removeClass("scrolled");
		} else {
			$("body").addClass("scrolled");			
		}
	});


	/******************************************************************************************
	PROJECTS LIST
	******************************************************************************************/
	
	if ($("body.projects.list").length) {

		// equal height headings for projects lists
		
		set_project_list_heading_heights = function() {
			$(".projects-list h2").css("height", "");
			var new_height = Math.max($(".projects-list h2").eq(0).height(), $(".projects-list h2").eq(1).height());
			$(".projects-list h2").height(new_height);
		}
		
		$(window).load(function () {
			set_project_list_heading_heights();
		});
		$(window).resize(function () {
			set_project_list_heading_heights();
		});	
	}

	/******************************************************************************************
	CONTACT FORM 
	******************************************************************************************/

    var contact_form = $('form.contact');
    var contact_form_container = $('.section.contact');

	contact_form.submit(function(e){
		$.post(
			//form url (Freeform autodetects ajax)
			contact_form.attr("action"),
			//form params
			contact_form.serialize(),
			//data handler
			function(data) {
				if (data.success == false) {
					// clear previous errors
					$("input, textarea").removeClass("empty");
					//data.errors
					$.each(data.errors, function(i, item){
						$('[name="' + i + '"]').addClass("empty");
					});
					$("html, body").animate({scrollTop: $(".empty").first().prev("label").offset().top - $(".header").height() - 10}, 400);
//					$(".empty").first().focus();
					
				} else if (data.success) {
					// keep form same height
					contact_form_container.css("height", contact_form_container.height());
					$(".footer").fadeOut();
					contact_form_container.children().fadeOut(400, function() {
						if ($(this).is("form")) {
							$("html, body").animate({scrollTop: 0}, 1);
							contact_form_container.css("height", "");
							contact_form_container.append('<p class="thankyou" style="display: none">Thank you for your message<p>');
							$('p.thankyou').fadeIn();
						}
					});
				}
			}
		);
	
		e.preventDefault();
		return false;
	});

	// when an input is changed, remove empty class if it's not empty
	contact_form.find("input, textarea").change(function() {
		if ($(this).val()!="") {
			$(this).removeClass("empty");
		}
	})


	/******************************************************************************************
	MISC
	******************************************************************************************/

	// PARAGRAPH INDENTS
	
	$(".body p").each(function() {
		if (!$(this).prev().is("p:not(.date, .intro, .note)") || $(this).prev().html()=="&nbsp;") {
			$(this).addClass("first");
		}
	});
	
	// LOGO FOR NON-SVG BROWSERS
	
	if (!Modernizr.svg) {
		$("body.home .header h1 img").attr("src", "/images/site/atf-logo.png");
	}

	// unless we're on Contact page, hide footer when page is pulled down (has negative scroll) to stop it popping up behind top of content
	
	if (!$("body").hasClass("contact")) {
		$(window).on("scroll", function() {
			if ($("body").scrollTop() < 0) {
				$(".footer").hide();
			} else {
				$(".footer").show();			
			}
		});
	}
	
	// SAFARI 6.1 screws up height of main images in projects, as does iOS6
	
	if ($("body.projects").length && navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
		safari_project_img_fix = function() {
			// set heading-wrap to be height of screen (or iOS6)
			$(".heading-wrap").height($(window).height() - $(".header .title").height());
			// set heaading to be height of heading-wrap, or text block if taller
			$(".heading-wrap .heading").height(Math.max($(".heading-wrap .text").innerHeight(), $(".heading-wrap").height()));
		}
		$(window).on("load resize", function() {
			safari_project_img_fix();
		});
	}
	
});

function loaded() {
/*
	myScroll = new IScroll('body', {
	    mouseWheel: true,
	    scrollbars: true
	});
*/
};
