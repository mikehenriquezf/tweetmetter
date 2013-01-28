$(document).ready(function() {

	// Cotto Twitter Slider
	$('#cotto-slide').flexslider({
		animation: "slide",
		easing: "swing",
		controlNav: false,
		animationLoop: false,
		pauseOnHover: true
	});

	// Trout Twitter Slider
	$('#trout-slide').flexslider({
		animation: "slide",
		easing: "swing",
		controlNav: false,
		animationLoop: false,
		reverse: true,
		pauseOnHover: true,
		initDelay: 5
	});


	// Fancybox menciones y tweets pasadas las 24 horas
	$('.boxers .fancy-general.mentions').click(function() {
		lightboxShowMentions24($(this).attr('twitter'));
	});
	$('.boxers .fancy-general.tweets').click(function() {
		lightboxShowTweets24($(this).attr('twitter'));
	});

	// TechCards
	$('.technical-cards a').click(function() {
		techCardShowStuff($(this).attr('card'));
	});
	$('.techcard a.close').click(function() {
		techCardHideStuff($(this).attr('card'));
	});

	// Tooltip
	$('a.tooltip[title]').qtip({
		position: {
			my: 'right center',
			at: 'left center',
			adjust: {
				x: -10
			}
		},
		style: {
			classes: 'qtip-red'
		},
		hide: 'mouseleave unfocus'
	});

	$('a.tooltip').click(function() {
		return false;
	});

	$('.fb-scroll').jScrollPane({autoReinitialise: true});

	// CSS3 animations
	$('#cotto .photo').addClass('animated fadeInLeftBig');
	$('#trout .photo').addClass('animated fadeInRightBig');
	$('#cotto .name').addClass('animated bounceInDown');
	$('#trout .name').addClass('animated bounceInDown');
	$('.versus').addClass('animated bounceIn');
	$('.versus').addClass('animated bounce');
	$('.mentions').addClass('animated flipInX');
	$('.followers').addClass('animated flipInX');
	$('.technical-cards').addClass('animated flipInX');

	// Countdown
	$.countdown.regional['es'] = {
		labels: ['A&ntilde;os', 'Meses', 'Semanas', 'D&iacute;as', 'Horas', 'Minutos', 'Segundos'],
		labels1: ['A&ntilde;os', 'Meses', 'Semanas', 'D&iacute;as', 'Horas', 'Minutos', 'Segundos'],
		compactLabels: ['a', 'm', 's', 'g'],
		timeSeparator: ':', isRTL: false};
	$.countdown.setDefaults($.countdown.regional['es']);
	var eventDate = new Date($('#div_countdown').attr('Y'), ($('#div_countdown').attr('m')-1), $('#div_countdown').attr('d'), $('#div_countdown').attr('H'), $('#div_countdown').attr('i'));
	$('#div_countdown').countdown({until: eventDate, onExpiry: roundByRoundLoad});

	// Tooltip scrool
	$(document).scroll(function() {
		var currScroll = $(document).scrollTop();
		var isAtBottom = ($(document).scrollTop() == ($(document).height()-$(window).height()));
		var currStep = 0;
		$('a.tooltip').removeClass('active');
		if (currScroll < $('#round_info').offset().top - 10) {
			currStep = 'uno';
		} else
		if (currScroll < $('#widgets').offset().top - 10) {
			currStep = 'dos';
		} else {
			currStep = 'tres';
		}
		if (isAtBottom) currStep = 'tres';
		$('a.tooltip.'+currStep).addClass('active');
	});

	statsExecuteRefresh();
});

function goToByScroll(id)
{	
	$('html,body').animate({scrollTop: $("#" + id).offset().top}, 'slow');
}

function techCardShowStuff(id) 
{
	element = document.getElementById(id);
	element.style.display = 'block';
	element.style.opacity = 0;
	element.style.filter = 'alpha(opacity=0)';
	valueop = 1;
	setTimeout("fadeIn()", 200);
}

function techCardHideStuff(id) 
{
	element = document.getElementById(id);
	valueop = 9;
	setTimeout("fadeOut()", 100);
}

function fadeOut() 
{
	if(valueop < 1) {
		element.style.display = 'none';
		return false;
	}
	element.style.opacity = valueop/10;
	element.style.filter = 'alpha(opacity='+(valueop*10)+')';
	valueop = valueop - 1;
	setTimeout("fadeOut()", 15);
}

function fadeIn() 
{
	if(valueop > 10) {
		return false;
	}
	element.style.opacity = valueop/10;
	element.style.filter = 'alpha(opacity='+(valueop*10)+')';
	valueop = valueop + 1;
	setTimeout("fadeIn()", 25);
} 

function roundByRoundLoad()
{
	$('#counter').fadeOut(function() {
		$('#rounds').fadeIn();
	});
}

function statsExecuteRefresh()
{
	$.ajax({
		url: WEB_PATH + 'app/frontend/ajax/data_refresh.php',
		async: true,
		dataType: 'json',
		success: function(data) {
			for (var i = 1; i <= 2; i++) {
				$('[id_person='+i+'] ');
				$('[id_person='+i+'] .hd h3').html(data.stats24[i].mentions);
				$('[id_person='+i+'] .followers  h4').html(data.persons[i-1].followers);
				$('[id_person='+i+'] .tweets_24').html(data.stats24[i].tweets);
				$('[id_person='+i+'] .retweets_24').html(data.stats24[i].retweets);
			}			
			setTimeout(statsExecuteRefresh, data.interval * 1000);
		}
	});
}