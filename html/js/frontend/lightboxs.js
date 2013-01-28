function lightboxActivate(html)
{
	$('#div_lightbox').html(html);
	$('.fancy-general').fancybox({
//		maxWidth	: 473,
//		maxHeight	: 468,
//		fitToView	: false,
//		autoSize	: false,
//		closeClick	: false,
//		openEffect	: 'none',
//		closeEffect	: 'none',
		closeClick	: false
	});
	$('.fancy-scroll').jScrollPane({ autoReinitialise: true });
}

function lightboxShowMentions24(twitter_user)
{
	lightboxActivate('');
	$.ajax({
		url: WEB_PATH + 'app/frontend/ajax/lightbox_mentions24.php',
		data: { twitter_user: twitter_user},
		success: function(html) {
			lightboxActivate(html);
		}
	});
}

function lightboxShowTweets24(twitter_user)
{
	lightboxActivate('');
	$.ajax({
		url: WEB_PATH + 'app/frontend/ajax/lightbox_tweets24.php',
		data: { twitter_user: twitter_user},
		success: function(html) {
			lightboxActivate(html);
		}
	});
}