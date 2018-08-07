<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.quanrio.com
 * @since      1.0.0
 *
 * @package    Loquat
 * @subpackage Loquat/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Loquat
 * @subpackage Loquat/admin
 * @author     Quanrio <contact@quanrio.com>
 */
class Loquat_Admin {

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
		$this->context = array( 'source' => 'loquat' );

		if( !empty($_SESSION['loquat_admin_script']) ) {
			wc_enqueue_js($_SESSION['loquat_admin_script']);
		}

		unset($_SESSION['loquat_admin_script']);

		// require_once plugin_dir_path( __FILE__ ) . '../includes/class-loquat-lib.php';
		// $this->lib = new Loquat_Lib();
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
		 * defined in Loquat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loquat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/loquat-admin.css', array(), $this->version, 'all' );

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
		 * defined in Loquat_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Loquat_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/loquat-admin.js', array( 'jquery' ), $this->version, false );

	}


	public function product_new($id) {
		$this->logger->debug( 'New Product: ' . $id, $this->context );

	}

	public function product_update($id) {
		$this->logger->debug( 'Update Product: ' . $id, $this->context );
	}

	public function product_trash($id) {
		if(get_post_type($id) !== 'product') {
			return;
		}
		$this->logger->debug( 'Trash Product: ' . $id, $this->context );
	}	

	public function product_diff($new_status, $old_status, $post) {
		if(get_post_type($post->ID) !== 'product' || empty($post->ID)) return;

		$product = wc_get_product($post->ID);
		$this->logger->debug( "Product Price: " . $product->get_sale_price() );
		$code = '';

		// $this->logger->debug( 'Old: ' . $old_status . ', New: ' . $new_status );

		$label = null;

		$payload = array(
				'id' => $product->get_id(),
				'sku' => $product->get_sku(),
				'title' => $product->get_title(),
				'in_stock' => $product->is_in_stock(),
				'sale_price' => $product->get_sale_price(),
				'regular_price' => $product->get_regular_price(),
				'categroies' => array_map(function($id) {
					return get_term_by( 'id', $id, 'product_cat' )->name;
				}, $product->get_category_ids())
			);

		$payload = json_encode($payload);

		if ( ( $old_status == 'draft' || $old_status == 'trash' ) && $new_status == 'publish' ) {
			$label = 'New Product';

			// $req = $this->lib->newProduct($payload);
			$code = "
				lib.newProduct($payload, function(res) {
					console.log('Product sent to remote', res)
					})
			";
		}

		if ( $old_status == 'publish' && $new_status == 'publish' ) {
			$label = 'Update Product';
			$code = "
				lib.updateProduct($payload, function(res) {
					console.log('Product update sent to remote', res)
					})
			";
		}

		if ( $old_status == 'publish' && ( $new_status == 'draft' || $new_status == 'trash' ) ) {
			$label = 'Delete Product';
		}

		if( $code !== '' ) $this->enqueue_script($code);

		if($label) $this->logger->debug( $label . ' - ' . $post->ID . ' : ' . $product->name );
	}

	public function enqueue_script($code) {
		$_SESSION['loquat_admin_script'] = $_SESSION['loquat_admin_script']."\n\n $code";
	}

	public function add_menu() {
		add_menu_page( 'Loquat - Store Personlization', 'Loquat', 'administrator', 'loquat', array($this, 'register_menu'), '');
		// add_options_page( 'Loquat Settings', 'Loquat', 'manage_options', 'loquat', '' );
	} 


	public function register_menu() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/loquat-admin-display.php';
	}

}
