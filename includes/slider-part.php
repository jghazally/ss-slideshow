<?php
if ( !empty($slides) && is_array($slides) ) : ?>

	<ul class="slides">
<?php 
	$i = 1;
	foreach ( $slides as $slide) :
		$url              = get_post_meta($slide->ID , '_ss_slider_url' , true);
		$url_before       = '';
		$url_after        = '';
		$video_class      = '';
		$video_link_class = '';

		if ( !empty($url) ) :

			if ( preg_match('/youtube|vimeo|blip/', $url) ) :
				$video_class = 'video';
				$video_link_class = 'fancybox-media';
			endif;

			$url_before = "\t<a class = '".$video_link_class."' href = '" . esc_url($url) . "' title = '" . esc_attr($slide->post_title) . "'>\n\t\t";
			$url_after  = "\n\t</a>";
		endif;

		$src     = wp_get_attachment_image_src(get_post_thumbnail_id($slide->ID) , 'full');
		$caption = get_post_meta($slide->ID, '_ss_slider_read_more_text', true);

		if ( !empty($src) ) : ?>

			<li class='slide polaroid thick <?php echo $video_class ?>'>
				<?php echo $url_before ?>

				<img src="<?php echo $src[0] ?>" class="slide-img" alt="<?php echo $slide->post_title ?>">

				<?php if ( !empty($video_class) ) : ?>
					<a class="play-button screen-reader-text <?php echo $video_link_class ?>" href="<?php echo esc_url($url) ?>">play video</a>
				<?php endif ?>

				<?php if ( !empty($caption) ) : ?>
					<div class="slide-overlay">
						<?php if ( !empty($caption) ) : ?>
							<h5 class="overlay-caption"><?php echo wptexturize($caption) ?></h5>
						<?php endif ?>
						<?php if ( !empty($slide->post_content) ) : ?>
							<p><?php echo wptexturize($slide->post_content) ?></p>
						<?php endif ?>
					</div>
				<?php endif ?>

				<?php echo $url_after; ?>
			</li>
		<?php endif;
	endforeach; ?>
	</ul>
	<div id="pager"></div>

<?php endif; ?>
