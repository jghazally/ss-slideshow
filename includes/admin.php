<?php
/**
 *
 * SS_Admin Class handles all the wp-admin functionality
 * From setting up additional columns to saving post meta
 * Package: ss-slideshow
 **/

class SS_Admin {

	function __construct($post_type, $taxonomy) {
		$this->post_type = $post_type;
		$this->taxonomy  = $taxonomy;
		add_action('save_post', array($this, 'save_postdata'));

		add_action('wp_ajax_ss_tinymce_window', array($this, 'get_tinymce_window'));

		add_action('wp_ajax_ss_slideshow_order', array($this, 'save_slide_order'));
		add_action('init', array($this, 'init'));

		add_action('manage_pages_custom_column', array($this, 'additional_column_data'), 10, 2);
		add_filter('manage_edit-slide_columns', array($this, 'additional_column_names'));

		add_filter('restrict_manage_posts', array($this, 'restrict_slides_by_taxonomy'));
		add_filter('parse_query', array($this, 'convert_tax_id_to_slug'));

	}

	public function init() {
		if ( !empty($_GET['post_type']) && $this->post_type === $_GET['post_type'] ) {
			$this->enqueue_admin_scripts();
		}

		$this->add_mcebuttons();
	}

	public function add_mcebuttons() {
		if ( get_user_option('rich_editing') === 'true' ) {
			add_filter('mce_external_plugins', array($this, 'add_tinymce_plugin'), 5);
			add_filter('mce_buttons', array($this, 'register_button'), 5);
		}
	}

	public function get_tinymce_window() {
		require_once SS_SLIDESHOW_PATH . 'js/tinymce3/window.php';
		exit;
	}

	public function add_tinymce_plugin($plugin_array) {
		$plugin_array['SSSLIDESHOW'] = SS_SLIDESHOW_URL . '/js/tinymce3/editor_plugin.js';
		return $plugin_array;
	}

	public function register_button($buttons) {
		array_push($buttons, 'separator', 'SSSLIDESHOW');
		return $buttons;
	}

	private function enqueue_admin_scripts() {
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script(
			'ss-slider-admin',
			SS_SLIDESHOW_URL . '/js/slider-admin.js',
			array('jquery-ui-sortable'),
			true
		);
	}

	public function meta_boxes() {
		add_meta_box('ss_slider_metabox', 'Slide Options', array('SS_Admin', 'metabox'), 'slide', 'normal', 'default');
	}

	public function metabox($post) {
		$url       = get_post_meta($post->ID, '_ss_slider_url', true);
		$read_more = get_post_meta($post->ID, '_ss_slider_read_more_text', true);

		wp_nonce_field(plugin_basename(__FILE__), 'ss_slider_nonce');
		?>
		<p>
			<label for="ss_slider_url">
				<?php _e('Slide links to', 'ss_slideshow'); ?>:
			</label>
			<input type="text" style="width:99%;" id="ss_slider_url" name="ss_slider_url" class="code" value="<?php echo $url; ?>" />
			<br>
			<?php _e(' ( Enter the link you want the slide title and image to direct to ) ', 'ss_slideshow'); ?>
		</p>
		<p>
			<label for="ss_slider_read_more_text">
				<?php _e('Slides read more text', 'ss_slideshow'); ?>:
			</label>
			<input type="text" style="width:99%;" name="ss_slider_read_more_text" id="ss_slideshow_read_more_text" class="code" value="<?php echo $read_more; ?>" />
			<br>
			<?php _e(' ( The read more text you would like on the slide - leave empty for none ) ', 'ss_slideshow'); ?>
		</p>
		<?php
	}

	public function save_postdata($post_id) {
		// Need to make sure we do not overwrite postmeta on bulk edit
		// screens as well as quick edit screens.
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset($_POST['ss_slider_nonce']) && !wp_verify_nonce($_POST['ss_slider_nonce'], plugin_basename(__FILE__)) ) {
			return;
		}

		if ( isset($_POST['ss_slider_url']) ) {
			update_post_meta($post_id, '_ss_slider_url', $_POST['ss_slider_url']);
		}

		if ( isset($_POST['ss_slider_read_more_text']) ) {
			update_post_meta($post_id, '_ss_slider_read_more_text', $_POST['ss_slider_read_more_text']);
		}
	}

	public function save_slide_order() {
		global $wpdb;
		if ( isset($_POST['post']) ) {
			foreach ( (array) $_POST['post'] as $position =>  $post_id ) {
				$wpdb->update(
					$wpdb->posts,
					array('menu_order' => (int) $position),
					array('ID' => (int) $post_id)
				);
			}
		}
	}

	public function additional_column_names($columns) {
		$columns          = array();
		$columns['cb']    = '<input type = "checkbox">';
		$columns['order'] = 'Order';
		$columns['image'] = 'Image';
		$columns['title'] = 'Title';
		$columns['date']  = 'Date';

		return $columns;
	}

	public function additional_column_data($column, $post_id) {
		global $post;

		if ( $post->post_type !== $this->post_type ) {
			return;
		}

		switch ( $column ) {
			case 'image' :
				echo $this->get_image($post_id, 'thumbnail');
				break;

			case 'order' :
				if ( empty($_GET['slide_tax']) ) {
					echo '[X]';
				} else {
					echo "<img class='ss_drag' src='" . SS_SLIDESHOW_URL . "/images/roll-over-drag.png' title='drag to order' />";
				}
			break;
		}
	}

	public function get_image($post_id, $size = 'thumbnail') {
		if ( has_post_thumbnail($post_id) ) {
			return get_the_post_thumbnail($post_id, $size);
		}
	}

	public function restrict_slides_by_taxonomy() {
		global $typenow;

		if ( $typenow == $this->post_type ) {
			$selected = isset($_GET[$this->taxonomy]) ? $_GET[$this->taxonomy] : '';
			$taxonomy_info = get_taxonomy($this->taxonomy);
			wp_dropdown_categories(
				array(
					'show_option_all' => __("Show All {$taxonomy_info->label}"),
					'taxonomy' => $this->taxonomy,
					'name' => $this->taxonomy,
					'orderby' => 'name',
					'selected' => $selected,
					'show_count' => false,
					'hide_empty' => false,
				)
			);
		}
	}

	public function convert_tax_id_to_slug($query) {
		global $pagenow;
		$q_vars = &$query->query_vars;
		if ( $pagenow === 'edit.php'
			&& !empty($q_vars['post_type'])
			&& $q_vars['post_type'] === $this->post_type
			&& !empty($q_vars[$this->taxonomy])
			&& is_numeric($q_vars[$this->taxonomy])
		) {
			$term = get_term_by('id', $q_vars[$this->taxonomy], $this->taxonomy);
			$q_vars[$this->taxonomy] = $term->slug;
		}
	}
}
