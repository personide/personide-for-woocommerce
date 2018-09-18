<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.quanrio.com
 * @since      1.0.0
 *
 * @package    Loquat
 * @subpackage Loquat/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Loquat
 * @subpackage Loquat/public
 * @author     Quanrio <contact@quanrio.com>
 */

class Loquat_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->logger = wc_get_logger();
		$this->current_user_id = $_COOKIE['LQT_UID'];

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loquat-util.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/loquat-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/loquat-public.js', array( 'jquery' ), $this->version, false );

	}

	public function page_load() {

		if ( is_product() ) {
			global $post;
			$product = wc_get_product( $post->ID );

 			// @todo skip following if product is in cart

			$this->logger->debug( '# Viewing Product: ' . $product->get_title() );

			$name = $product->get_title();

			wc_enqueue_js( "console.log('Viewing Product: $name')" );

			$event_object = Loquat_Util::get_event( 'view', 'user', $this->current_user_id, NULL, 'item', $product->get_id());

			// $this->logger->debug( $event_object );	
			wc_enqueue_js( "dispatch($event_object)" );
		}
	}

	public function add_to_cart($cart_item_key, $product_id, $quantity) {

		$product = wc_get_product( $product_id );
		$name = $product->get_title();
		wc_enqueue_js( "console.log('Product going to cart: $name')" );

		$event_object = Loquat_Util::get_event( 'add-to-cart', 'user', $this->current_user_id, NULL, 'item', $product->get_id() );
		
		// $this->logger->debug( $event_object );
		wc_enqueue_js( "console.log($event_object)" );
		wc_enqueue_js( "dispatch($event_object)" );
	}

	public function checkout($order_get_id) {
		$this->logger->debug( '# Completing Order: ' . $order_get_id );
		$order = new WC_Order($order_get_id);

		$items = $order->get_items();
		$properties = array(
			'items' => array_keys($items)
		);

		$event_object = Loquat_Util::get_event( 'purchase', 'user', $this->current_user_id, json_encode($properties), 'order', $order->get_id() );

		wc_enqueue_js( "dispatch($event_object)" );
	}
}


require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/widgets/class-loquat-widget-recommendations.php';

function widget_register() {
    register_widget( 'Loquat_Widget_Recommendations' );
}

add_action( 'widgets_init', 'widget_register' );