<?php
//Check capabilities
if ( !current_user_can('edit_pages') && !current_user_can('edit_posts') )
	wp_die( __( 'You don\'t have permission to be doing that!', 'wpsc' ) );

$slider_tax = get_terms('slide_tax');

global $wpdb;
?>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>Stupidly Simple Slideshow Shortcode</title>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/jquery/jquery.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/tiny_mce_popup.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/mctabs.js"></script>
		<script language="javascript" type="text/javascript" src="<?php echo includes_url(); ?>js/tinymce/utils/form_utils.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo SS_SLIDESHOW_URL ?>/js/tinymce3/tinymce.js"></script>
	<base target="_self" />
	</head>
	<body>
		<form action="#" name="slider_shortcode">
			<p>
				<label for="slider_tax">Choose a term</label>
				<select name="slider_tax" id="slider_tax">
					<option disabled>Select a term</option>
					<?php foreach ( $slider_tax as $term ) : ?>
						<option value="<?php echo $term->slug ?>"><?php echo $term->name ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="posts_per_page">Select max. number of slides</label>
				<select name="posts_per_page" id="posts_per_page">
					<option disabled>Select a number</option>
					<option value="-1">unlimited</option>
					<?php for ( $i = 1; $i < 11; $i++ ) : ?>
						<option value="<?php echo $i ?>"><?php echo $i ?></option>
					<?php endfor; ?>
				</select>
			</p>
			<p>
				<input type="submit" name="insert" value="insert" id="insert_ssshortcode" />
			</p>
		</form>
	</body>
</html>
