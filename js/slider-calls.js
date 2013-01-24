jQuery(document).ready(function($) {

	if ( $('.slides').length > 0 && $('.slides').cycle ) {
		$('.slides').after('<div class="slide-pagination">').cycle({
			fx		: 	'fade',
			speed	:	300,
			timeout : 	5500,
			width	: 	'100%',
			height	: 	'auto',
			fit		: 	1,
			pager 	:	'.slide-pagination',
			pause	:	1
		});
	}
});
