jQuery(document).ready(function($) {

    $('table.widefat:not(.tags)').sortable({

		update: function(event, ui) {

			var sliderOrder = $('table.widefat').sortable('serialize');

			var data = {
				action: 'ss_slideshow_order',
				slider_order: sliderOrder
			};

			$.post(ajaxurl, data, function(returned_data) {});
		},

		items: 'tbody tr',
		axis: 'y',
		containment: 'table.widefat tbody',
		cursor: 'move',
		cancel: 'tr.inline-edit-slides',
		handle: '.column-order img'
    });
});
