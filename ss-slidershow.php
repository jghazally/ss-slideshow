<?php
/**
 * Plugin Name: Stupidly Simple Slideshow
 * Plugin URI:  http://www.screamingcodemonkey.com
 * Description: A really simple slideshow plugin
 * Author:      jghazally
 * Version:     1.0
 */

/**
 * Stupidly Simple Slideshow Plugin begins here.
 * This Plugin includes
 * - Custom Slideshow Post Type
 * - Slideshow Taxonomy
 * - Permalink redirects for slides
 * - A template tag with arguments
 * - A shortcode
 * - TinyMCE button
 **/

class SS_Slideshow {
	private $post_type = 'slide';
	private $taxonomy  = 'slide_tax';

	/**
	 * We start our plugin off by
	 * defining some constants
	 * registering a shortcode
	 * including in the admin interface
	 **/
	public function ss_slideshow() {
		$this->define_constants();
		add_action('plugins_loaded', array($this, 'plugins_loaded'));
		add_shortcode('ss_slideshow', 'ss_slideshow');
		require_once SS_SLIDESHOW_PATH . 'includes/admin.php';

	}

	/**
	 * We define a couple of handy constants.
	 **/
	public function define_constants() {
		if ( !defined('SS_SLIDESHOW_VERSION') ) {
			define('SS_SLIDESHOW_VERSION', '1.0');
		}
		if ( !defined('SS_SLIDESHOW_URL') ) {
			define('SS_SLIDESHOW_URL', plugins_url('', __FILE__));
		}
		if ( !defined('SS_SLIDESHOW_PATH') ) {
			define('SS_SLIDESHOW_PATH', plugin_dir_path(__FILE__));
		}
	}

	/**
	 * When all plugins have been loaded, we can proceed with
	 * registering the custom post type
	 * registering the admin section
	 * running the init function
	 **/
	public function plugins_loaded() {
		add_action('init', array($this, 'register_slider_cpt_taxonomy'));

		if ( is_admin() ) {
			$this->admin = new SS_Admin($this->post_type, $this->taxonomy);
		}

		add_action('init', array($this, 'init'));
	}

	/**
	 * Theres not much we need to do here, except enqueue the front end
	 * scripts
	 **/
	public function init() {
		$this->enqueue_frontend_scripts();
	}

	/**
	 * We enqueue the cycle plugin, the slider calls and the basic
	 * styles
	 *
	 **/
	private function enqueue_frontend_scripts() {
		wp_enqueue_script(
			'cycle',
			SS_SLIDESHOW_URL . '/js/jquery.cycle.all.js',
			array('jquery'),
			SS_SLIDESHOW_VERSION,
			true
		);
		wp_enqueue_script(
			'ss-slideshow-calls',
			SS_SLIDESHOW_URL . '/js/slider-calls.js',
			array('cycle'),
			SS_SLIDESHOW_VERSION,
			true
		);
		wp_enqueue_style(
			'ss-slider-styles',
			SS_SLIDESHOW_URL . '/css/ss-slider-styles.css'
		);
	}

	/**
	 * Registering the slide custom post type and the taxonomy
	 **/
	public function register_slider_cpt_taxonomy() {
		$labels = array(
			'name'               => _x('Slide', 'post type name', 'ss_slideshow'),
			'singular_name'      => _x('Slide', 'post type singular name', 'ss_slideshow'),
			'add_new'            => _x('Add New', 'admin menu: add new slide', 'ss_slideshow'),
			'add_new_item'       => __('Add New Slide', 'ss_slideshow'),
			'edit_item'          => __('Edit Slide', 'ss_slideshow'),
			'new_item'           => __('New Slides', 'ss_slideshow'),
			'view_item'          => __('View Slides', 'ss_slideshow'),
			'search_items'       => __('Search Slides', 'ss_slideshow'),
			'not_found'          => __('No Slides found', 'ss_slideshow'),
			'not_found_in_trash' => __('No Slides found in Trash', 'ss_slideshow'),
			'parent_item_colon'  => '',
			'menu_name'          => __('Slides', 'ss_slideshow')
		);

		// slide
		register_post_type(
			$this->post_type,
			array(
				'capability_type'     => 'post',
				'hierarchical'        => true,
				'exclude_from_search' => true,
				'public'              => true,
				'show_ui'             => true,
				'show_in_nav_menus'   => true,
				'labels'              => $labels,
				'query_var'           => true,
				'supports'            => array(
						'thumbnail',
						'title',
					),
				'register_meta_box_cb' => array('SS_Admin', 'meta_boxes'),
				'rewrite'              => array(
					'slug'       => 'slide',
					'with_front' => false,
				)
			)
		);

		register_taxonomy(
			$this->taxonomy,
			array('slide'),
			array(
				'label'   => __('Slide Terms', 'ss_slideshow'),
				'sort'    => true,
				'args'    => array('orderby' => 'term_order'),
				'hierarchical' => true,
				'rewrite' => false,
				'labels'  => array(
					'name'              => __('Slide Terms', 'ss_slideshow'),
					'singular_name'     => __('Slide Terms', 'ss_slideshow'),
					'search_items'      => __('Search slider taxonomies', 'ss_slideshow'),
					'popular_items'     => __('Popular slider taxonomies', 'ss_slideshow'),
					'all_items'         => __('All Slider Taxonomies', 'ss_slideshow'),
					'parent_item'       => __('Slider Taxonomy', 'ss_slideshow'),
					'parent_item_colon' => __('Slider Taxonomy:', 'ss_slideshow'),
					'edit_item'         => __('Edit slide term', 'ss_slideshow'),
					'update_item'       => __('Update slide term', 'ss_slideshow'),
					'add_new_item'      => __('Add new slide term', 'ss_slideshow'),
					'new_item_name'     => __('New Slider Taxonomy name', 'ss_slideshow'),
				)
			)
		);
	}

}

// Heres where we start the party
$ss_slideshow = new SS_Slideshow();


/**
 * ss_slideshow is the template tag used in themes and any php file,
 * it accepts a number of arguments within an array:
 *
 * @from_shortcode => (this is generally set to false, unless being
 * @used within shortcodes - utilizing the tnyMCE button)
 * @posts_per_page => how many slides you want to display
 * @slider_tax => the slider taxonomy to utilize
 * @size => the size of the images used (default full)
 **/
function ss_slideshow($atts = array()) {
	$output = '';
	extract(
		shortcode_atts(
			array(
				'from_shortcode' => false,
				'posts_per_page' => '-1',
				'slider_tax'     => '',
				'size'           => 'full',
			),
			$atts
		)
	);

	$bigfiddshslider = new ss_slideshow();

	$query_args = array(
		'posts_per_page' => $posts_per_page,
		'post_type'      => 'slide',
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);
	if ( !empty($slider_tax) ) {
		$query_args['slider_tax'] = $slider_tax;
	}
	$slides = get_posts($query_args);

	ob_start();
	if ( file_exists(get_template_directory() . '/inc/slider-part.php') ) {
		include locate_template('inc/slider-part.php');
	} else {
		include SS_SLIDESHOW_PATH . '/includes/slider-part.php';
	}

	$output .= ob_get_contents();
	ob_end_clean();

	if ( $from_shortcode ) {
		return $output;
	} else {
		echo $output;
	}

}

