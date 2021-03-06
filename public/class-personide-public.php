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

class Personide_Public
{

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
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->logger = wc_get_logger();
		$this->context = array('source' => 'personide');
		$this->script = "";
		$this->params_html = "";
		$this->events = [];

		if (WC()->session) {
			if (WC()->session->__isset($plugin_name . '_events')) {
				$this->events = WC()->session->get($plugin_name . '_events');
			} else {
				WC()->session->set($plugin_name . '_events', array());
			}
		}

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-personide-util.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		$access_token = Personide_Util::get_option('access_token');
		$wpes = wp_enqueue_script($this->plugin_name, "//connect.personide.com/lib/js/" . $access_token, array('jquery'), null, false);
		// wp_enqueue_script( $this->plugin_name, "//localhost:9000/lib/js/".$access_token, array( 'jquery' ), null, false );
		$this->enqueue_params("page", Personide_Util::get_pagedata('type'));
	}

	public function enqueue_js($code)
	{
		$this->script .= "\n" . $code;
	}

	public function enqueue_params($key, $value)
	{
		$this->params_html .= "\n" . "<div class=\"personide_$key\" style=\"display: none\">$value</div>";
	}

	public function add_async_attribute($tag, $handle)
	{
		if ($this->plugin_name !== $handle)
			return $tag;
		return str_replace(' src', ' async src', $tag);
	}

	public function page_load()
	{

		$options = get_option($this->plugin_name);

		$access_token = Personide_Util::get_option('access_token');
		$pagedata = Personide_Util::get_pagedata();

		if (Personide_Util::get_option('remove_wc_related_products') == TRUE) {
			remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
		}

		if (is_front_page() || is_checkout()) {
			$this->push_user_set_event();
		}

		if (is_product()) {
			global $post;
			$product = wc_get_product($post->ID);
			// @todo skip following if product is in cart
			$name = $product->get_title();
			$this->enqueue_params("product_id", $product->get_id());
		}

		if (is_product_category()) {
			$properties = $pagedata['properties'];
			$value = $properties['name'];
			if (!empty($properties['ancestors'])) {
				array_unshift($properties['ancestors'], $value);
				$value = $properties['ancestors'];
			}

			$this->enqueue_params("category_name", ((is_array($value)) ? json_encode($value) : $value));
		}

		array_push($this->events, $pagedata['event']);

		$items = WC()->cart->get_cart();

		function itemsToProductIds($item)
		{
			return strval($item['data']->get_id());
		}

		$items = array_values(array_map("itemsToProductIds", $items));

		$this->enqueue_params('cart', json_encode($items));
		$this->enqueue_params('plugin_directory', plugin_dir_url(__FILE__));
	}


	public function all_loaded()
	{
		WC()->session->__unset($this->plugin_name . '_events');

		$array = "[";
		$array .= implode(', ', $this->events);
		$array .= "]";

		$this->enqueue_js("window.personide_events = " . $array . ";");
		// wp_add_inline_script($this->plugin_name, $this->script);
		echo '<script type="text/javascript">' . $this->script . '</script>';
		echo $this->params_html;
	}

	public function push_user_set_event()
	{
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$customer = new WC_Customer($current_user->ID);
			$properties = array(
				'nativeId' => $customer->get_id(),
				'email' => $customer->get_email(),
				'firstName' => $customer->get_first_name(),
				'lastName' => $customer->get_last_name(),
				'addressLine1' => $customer->get_billing_address_1(),
				'addressLine2' => $customer->get_billing_address_2(),
				'city' => $customer->get_billing_city(),
				'state' => $customer->get_billing_state(),
				'country' => $customer->get_billing_country()
			);
			$event_object = Personide_Util::get_event('$set', 'user', '', json_encode($properties));
			array_push($this->events, $event_object);
		}
	}


	public function add_to_cart($cart_item_key, $product_id, $quantity)
	{

		$product = wc_get_product($product_id);
		$name = $product->get_title();

		$event_object = Personide_Util::get_event('add-to-cart', 'user', '', NULL, 'item', $product->get_id());

		$events = WC()->session->get($this->plugin_name . '_events');
		array_push($events, $event_object);
		WC()->session->set($this->plugin_name . '_events', $events);
		array_push($this->events, $event_object);
	}


	public function checkout()
	{
		global $wp;

		if (!(function_exists('is_checkout') && is_checkout())) {
			return;
		}

		if (!(function_exists('is_order_received_page') && is_order_received_page())) {
			return;
		}

		if (!isset($wp->query_vars['order-received'])) {
			$this->logger->error("Checkout: query_vars['order-received'] is not set on checkout page... aborting.", $this->context);
			return;
		}

		$order_id = absint($wp->query_vars['order-received']);

		if ($order_id == 0 || is_bool($order_id)) {
			$this->logger->error("Checkout: order-received int value is invalid: $order_id ... aborting.", $this->context);
			return;
		}

		$this->logger->debug("Checkout: query_vars['order-received'] = $order_id", $this->context);

		$order = wc_get_order($order_id);

		if (is_bool($order)) {
			$this->logger->error("Checkout: order was not found by id = $order_id ... aborting.", $this->context);
			return;
		}

		$items = $order->get_items(); // WC_Order_Item[]

		function itemsToProducts($item)
		{
			$product = $item->get_product();
			$order_item_product = new WC_Order_Item_Product($item->get_id());
			return array(
				'id' => strval($order_item_product->get_product_id()),
				'variation_id' => strval($order_item_product->get_variation_id()),
				'price' => $product->get_price(),
				'quantity' => $order_item_product->get_quantity(),
				'subtotal' => $order_item_product->get_subtotal(),
				'total' => $order_item_product->get_total(),
				'categories' => array_map(function ($id) {
					return get_term_by('id', $id, 'product_cat')->slug;
				}, $product->get_category_ids())
			);
		}

		$items = array_values(array_map("itemsToProducts", $items));
		$properties = array(
			'items' => $items,
			'total_amount' => $order->get_total()
		);
		$event_object = Personide_Util::get_event('purchase', 'user', '', json_encode($properties), 'cart', $order->get_id());
		array_push($this->events, $event_object);
	}
}

require_once plugin_dir_path(dirname(__FILE__)) . 'includes/widgets/class-personide-widget-recommendations.php';
