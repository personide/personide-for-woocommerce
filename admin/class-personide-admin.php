<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.quanrio.com
 * @since      1.0.0
 *
 * @package    Personide
 * @subpackage Personide/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Personide
 * @subpackage Personide/admin
 * @author     Quanrio <contact@quanrio.com>
 */
class Personide_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->logger = wc_get_logger();
		$this->context = array( 'source' => 'personide' );

		$this->new_products = array();

		$this->logger->debug("Created admin class instance", $this->context);

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-personide-util.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Personide_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Personide_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/personide-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Personide_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Personide_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . '../public/js/personide-public.js', array( 'jquery' ), $this->version, false );
		$access_token = Personide_Util::get_option('access_token');
		wp_enqueue_script( $this->plugin_name, "//connect.personide.com/lib/js?id=".$access_token, array( 'jquery' ), null, false );
		// wp_enqueue_script( $this->plugin_name, "//localhost:9000/lib/js?id=".$access_token, array( 'jquery' ), null, false );
		wp_add_inline_script( $this->plugin_name, Personide_Util::get_var_script() );

	}

	// @todo remove if unneccessary 
	public function product_add($id) {
		$this->logger->debug( 'New Product: ' . $id );
	}

	public function product_update($meta_id, $post_id, $meta_key, $meta_value) {

		if ( $meta_key == '_edit_lock' ) {

			if ( get_post_type( $post_id ) == 'product' ) {

				// $this->logger->debug( "# " . $meta_key );
				
				$label = '';
				$product = wc_get_product( $post_id );

				$properties = array(
					'id' => $product->get_id().'',
					'sku' => $product->get_sku(),
					'title' => $product->get_title(),
					'description' => $product->get_short_description() . '\n' . $product->get_description(),
					'in_stock' => $product->is_in_stock(),
					'on_sale' => $product->is_on_sale(),
					'sale_price' => $product->get_sale_price(),
					'regular_price' => $product->get_regular_price(),
					'categroies' => array_map(function($id) {
						return get_term_by( 'id', $id, 'product_cat' )->slug;
					}, $product->get_category_ids()),
					'url' => $product->get_permalink(),
					'image_url' => get_the_post_thumbnail_url($product->get_id())
				);

				if ( in_array( $post_id, $this->new_products ) ) {
				}
				else {
				}

				$event_object = Personide_Util::get_event( '$set', 'item', $product->get_id(), json_encode($properties) );

				wc_enqueue_js( "Personide.dispatch($event_object)" );

				$this->logger->debug( "# Add / Update Product: " . $product->get_id() );
			}
		}
	}


	// @todo: doesn't work because it fires before page reload 
	public function product_trash($id) {
		if ( get_post_type( $id ) == 'product' ) {
			$this->logger->debug( 'Trash Product: ' . $id );
			$event_object = Personide_Util::get_event( '$delete', 'item', $id );
			wc_enqueue_js( "Personide.dispatch($event_object)" );
			wc_enqueue_js( "console.log(RemovingProduct, $id)" );
		}
	}


	public function product_diff($new_status, $old_status, $post) {

		if(get_post_type($post->ID) !== 'product' || empty($post->ID)) return;

		$label = null;
		$id = $post->ID;

		if ( ( $old_status == 'draft' || $old_status == 'trash' ) && $new_status == 'publish' ) {
			
			$label = 'New Product';
			array_push( $this->new_products, $post->ID );
		}

		if( $new_status == 'trash' ) {
			$label = 'Trash Product';
			$event_object = Personide_Util::get_event( '$delete', 'item', $id );
			wc_enqueue_js( "Personide.dispatch($event_object)" );
			wc_enqueue_js( "console.log(RemovingProduct, $id)" );
		}

		if($label) $this->logger->debug( $label . ' - ' . $post->ID . ' : ' . $product->name );
	}

///////////////////////////

	public function add_menu() {
		add_menu_page( 'Personide - General Settings', 'Personide', 'administrator', $this->plugin_name , array($this, 'register_menu'), plugin_dir_url( __FILE__ ) . '../assets/icon.png');
	}


	public function register_menu() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/personide-admin-display.php';
	}

//////////////////////////

	public function validate_all($input) {

		$keys = ['access_token', 'remove_wc_related_products', 'hotslot_template'];
		foreach ($keys as $key) {
			$input[$key] = (isset($input[$key]) && !empty($input[$key])) ? $input[$key] : '';
		}

		$input['remove_wc_related_products'] = ($input['remove_wc_related_products']) ? TRUE : FALSE;

		return $input;
	}


	public function options_update() {
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate_all'));
	}
}
