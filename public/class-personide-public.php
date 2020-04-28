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

		$this->logger->debug("Created public class instance", $this->context);

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
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/personide-public.js', array( 'jquery' ), $this->version, false );

		$access_token = Personide_Util::get_option('access_token');
		$wpes = wp_enqueue_script($this->plugin_name, "//connect.personide.com/lib/js/" . $access_token, array('jquery'), null, false);
		// wp_enqueue_script( $this->plugin_name, "//localhost:9000/lib/js/".$access_token, array( 'jquery' ), null, false );
		// $this->enqueue_js( Personide_Util::get_var_script() );
		$this->enqueue_params("page", Personide_Util::get_pagedata('type'));
		$this->logger->debug("Enqueued public scripts", $this->context);
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

		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$this->enqueue_params('user_email', $current_user->user_email);
			$this->enqueue_params('user_firstname', $current_user->user_firstname);
			$this->enqueue_params('user_lastname', $current_user->user_lastname);
			$this->logger->debug($current_user->ID, $this->context);
			$customer = new WC_Customer($current_user->ID);
			$this->logger->debug(print_r($customer), $this->context);
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

		$this->logger->debug("Completed execution: template_redirect handler", $this->context);
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

		$this->logger->debug("Completed execution: wp_footer handler", $this->context);
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

		$this->logger->debug("Completed execution: woocommerce_add_to_cart handler", $this->context);
	}


	public function checkout()
	{
		global $wp;
		if (!isset($wp->query_vars['order-received'])) {
			return;
		}

		$order_id = absint($wp->query_vars['order-received']);
		$order = wc_get_order($order_id);
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

		// $this->logger->debug("Adding purchase event to session");

		// $events = WC()->session->get($this->plugin_name . '_events');
		array_push($this->events, $event_object);
		// WC()->session->set($this->plugin_name . '_events', $events);



		$this->logger->debug("Completed execution: woocommerce_checkout_update_order_meta handler", $this->context);
	}


	public function add_hotslot($type)
	{
		echo $this->get_hotslot_html($type);
	}


	public function get_hotslot_html($strategy = NULL)
	{
		$default = '
		<h1 class="personide_hotslot-title center"></h1>
		<div class="listing">
		<div class="template item personide-product">
		<a class="personide-product__link" href="">
		<img class="personide-product__picture" src=""/>
		<div class="personide-product__details">
		<p class="personide-product__name"></p>
		<p class="personide-product__price"></p>
		</div>
		</a>
		</div>
		</div>
		';

		$default_theme = Personide_Util::get_option('default_theme');
		$template = Personide_Util::get_option('hotslot_template');

		if (strlen($template) == 0) {
			$template = $default;
		}
		$default_class = ($default_theme) ? 'personide-basic' : '';

		return '
		<div class="personide_container ' . $default_class . '" data-priority=1 data-container="hotslot" data-type="' . $strategy . '">
		' . $template . '
		</div>
		';
	}


	public function hotslot_shortcode($atts)
	{
		$type = (is_array($atts)) ? $atts['type'] : NULL;
		return $this->get_hotslot_html($type);
	}
}


require_once plugin_dir_path(dirname(__FILE__)) . 'includes/widgets/class-personide-widget-recommendations.php';


function widget_register()
{
	register_widget('Personide_Widget_Recommendations');
}

add_action('widgets_init', 'widget_register');
