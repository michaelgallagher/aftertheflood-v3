$(document).ready(function (){

	$('#devbar .open').click(function() {
		if ($(this).hasClass("active")) {
			$('#devbar').animate({left: "-100%"});
			$(this).removeClass("active")
		} else {
			$('#devbar').animate({left: "-26px"});
			$(this).addClass("active")
		}
	});

	$('#devbar select').change(function () {
		$(".dev-tools-overlay").hide();
		if ($('#devbar select').val()!="-1") {
			$(".dev-tools-overlay").eq($('#devbar select').val()).show();
		}
	});
});
