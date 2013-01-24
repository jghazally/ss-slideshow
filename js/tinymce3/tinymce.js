jQuery(document).ready(function($) {

	$('#insert_ssshortcode').on('click', function() {
		var tagText;
		var sliderTax = $('#slider_tax').val();
		var postsPerPage = $('#posts_per_page').val();

		var tags = ['ss_slideshow'];

		tags.push('slider_tax="' + sliderTax + '"');
		tags.push('posts_per_page="' + postsPerPage + '"');
		tags.push('from_shortcode="true"');

		tagText = '[' + tags.join(' ') + ']';

		if ( window.tinyMCE ) {
			window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagText);
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.close();
		}

		return false;
	});
});
