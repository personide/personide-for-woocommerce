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
		wp_add_inline_script( $this->plugin_name, Personide_Util::get_var_script() );

	}

	// @todo remove
	public function get_payload($product) {
		$payload = array(
			'id' => $product->get_id(),
			'sku' => $product->get_sku(),
			'title' => $product->get_title(),
			'in_stock' => $product->is_in_stock(),
			'sale_price' => $product->get_sale_price(),
			'regular_price' => $product->get_regular_price(),
			'categroies' => array_map(function($id) {
				return get_term_by( 'id', $id, 'product_cat' )->slug;
			}, $product->get_category_ids())
		);

		$payload = json_encode($payload);

		return $payload;
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
					'title' => $product->get_title(),
					'sku' => $product->get_sku(),
					'description' => $product->get_description(),
					'url' => get_permalink($product->get_id()),
					'in_stock' => $product->get_stock_status() === 'instock',
					'regular_price' => $product->get_regular_price(),
					'sale_price' => $product->get_sale_price(),
					'categories' => array_map(function($id) {
						return get_term_by( 'id', $id, 'product_cat' )->slug;
					}, $product->get_category_ids()),

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


	public function product_trash($id) {
		$this->logger->debug( 'Trash Product: ' . $id );
		$product = wc_get_product($id);
		$event_object = Personide_Util::get_event( '$delete', 'item', $product->get_id() );
		wc_enqueue_js( "Personide.dispatch($event_object)" );
	}


	public function product_diff($new_status, $old_status, $post) {

		if(get_post_type($post->ID) !== 'product' || empty($post->ID)) return;

		$code = '';
		$label = null;

		if ( ( $old_status == 'draft' || $old_status == 'trash' ) && $new_status == 'publish' ) {
			
			$label = 'New Product';
			array_push( $this->new_products, $post->ID );
		}

		if($label) $this->logger->debug( $label . ' - ' . $post->ID . ' : ' . $product->name );
	}

///////////////////////////

	public function add_menu() {
		add_menu_page( 'Personide - General Settings', 'Personide', 'administrator', $this->plugin_name , array($this, 'register_menu'), plugin_dir_url( __FILE__ ) . '../assets/icon.png');
		// add_options_page( 'Personide Settings', 'Personide', 'manage_options', 'personide', '' );
	}


	public function register_menu() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/personide-admin-display.php';
	}

///////////////////////////

	// @todo remove
	public function enqueue_script($code) {
		if(empty($_SESSION['personide_admin_script']))
			$_SESSION['personide_admin_script'] = '';

		$_SESSION['personide_admin_script'] = $_SESSION['personide_admin_script']."\n\n $code";
	}

//////////////////////////

	public function validate_all($input) {

		$keys = ['access_token', 'remove_wc_related_products', 'hotslot_template'];
		foreach ($keys as $key) {
			$input[$key] = (isset($input[$key]) && !empty($input[$key])) ? $input[$key] : '';
		}

		$input['remove_wc_related_products'] = ($input['remove_wc_related_products']) ? TRUE : FALSE;

		// $this->logger->debug( 'Form input::cleaner : ' . print_r($input, TRUE) );

		return $input;
	}


	public function options_update() {
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate_all'));
	}
}
