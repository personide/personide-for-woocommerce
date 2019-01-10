<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.quanrio.com
 * @since      1.0.0
 *
 * @package    Personide
 * @subpackage Personide/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Personide
 * @subpackage Personide/public
 * @author     Quanrio <contact@quanrio.com>
 */

class Personide_Public {

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
		$this->events = [];

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-personide-util.php';

		$this->logger->debug('Loading personide public class');
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/personide-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/personide-public.js', array( 'jquery' ), $this->version, false );

		$access_token = Personide_Util::get_option('access_token');
		wp_enqueue_script( $this->plugin_name, "//connect.personide.com/lib/js?id=".$access_token, array( 'jquery' ), null, false );
		// wp_enqueue_script( $this->plugin_name, "//localhost:9000/lib/js?id=".$access_token, array( 'jquery' ), null, false );
		wp_add_inline_script( $this->plugin_name, Personide_Util::get_var_script() );

	}

	public function page_load() {

		$options = get_option($this->plugin_name);

		$access_token = Personide_Util::get_option('access_token');
		$pagetype = Personide_Util::get_pagetype();

		if( Personide_Util::get_option('remove_wc_related_products') == TRUE ) {
			remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}

		if ( is_product() ) {
			global $post;
			$product = wc_get_product( $post->ID );
 			// @todo skip following if product is in cart
			$name = $product->get_title();
			$event_object = Personide_Util::get_event( 'view', 'user', '', NULL, 'item', $product->get_id());
			array_push($this->events, $event_object);
			$this->logger->debug('Viewing Product: '.$product->get_id());
			wc_enqueue_js("Personide.set('currentProductId', '".$product->get_id()."')");
		}

		// wc_enqueue_js("Personide.setKey('$access_token');");
		// wc_enqueue_js("Personide.set('currentPage', '".$pagetype."')");
		wc_enqueue_js("Personide.set('pluginDirectory', '".plugin_dir_url( __FILE__ )."')");
		wc_enqueue_js("Personide.init()");
	}

	public function all_loaded() {
		foreach( $this->events as $event ) {
			wc_enqueue_js( "Personide.dispatch($event)" );
		}
	}


	public function product_html() {

		if ( isset( $_GET['personide_product_id'] ) ) {
			global $product;
			$id = $_GET['personide_product_id'];
			$product = wc_get_product( $id );
			echo $product->get_id();
			echo wc_get_template_html('/single-product/product-thumbnails.php', array('product' => $product));
			die;
		}

	}


	public function add_to_cart($cart_item_key, $product_id, $quantity) {

		$product = wc_get_product( $product_id );
		$name = $product->get_title();

		$event_object = Personide_Util::get_event( 'add-to-cart', 'user', '', NULL, 'item', $product->get_id() );
		array_push($this->events, $event_object);
	}


	public function checkout($order_get_id) {
		$this->logger->debug( '# Completing Order: ' . $order_get_id );
		$order = new WC_Order($order_get_id);

		$items = $order->get_items();

		function itemsToProductIds($item) {
			return $item->get_product_id();
		}

		$items = array_values(array_map("itemsToProductIds", $items));
		$properties = array(
			'items' => $items
		);

		$event_object = Personide_Util::get_event( 'purchase', 'user', '', json_encode($properties), 'cart', $order->get_id() );
		array_push($this->events, $event_object);
	}


	public function add_hotslot() {
		echo $this->get_hotslot_html();
	}


	public function get_hotslot_html() {
		return '
		<div class="personide_container" data-priority=1 data-type="hotslot">
	  	'.Personide_Util::get_option('hotslot_template').'
		</div>
	';
	}


	public function hotslot_shortcode() {
		return $this->get_hotslot_html();
	}


	public function get_pagetype() {
		global $wp_query;
		$loop = 'other';

		if ( $wp_query->is_page ) {
			$loop = is_front_page() ? 'front' : 'page';
		}

		if ( is_product() ) {
			$loop = 'product';
		} elseif ( is_shop() ) {
			$loop = 'listing';
		} elseif ( is_product_category() ) {
			$loop = 'category';
		} elseif ( is_product_tag() ) {
			$loop = 'tag';
		} elseif ( is_product_taxonomy() ) {
			$loop = 'taxonomy';
		} elseif ( is_cart() ) {
			$loop = 'cart';
		} elseif ( is_checkout() ) {
			$loop = 'checkout';
		} elseif ( $wp_query->is_search ) {
			$loop = 'search';
		} elseif ( $wp_query->is_404 ) {
			$loop = 'notfound';
		}

		return $loop;
	}
}


require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/widgets/class-personide-widget-recommendations.php';


function widget_register() {
	register_widget( 'Personide_Widget_Recommendations' );
}

add_action( 'widgets_init', 'widget_register' );