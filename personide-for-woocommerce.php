<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.quanrio.com
 * @since             1.1.0
 * @package           Personide
 *
 * @wordpress-plugin
 * Plugin Name:       Personide for WooCommerce
 * Plugin URI:        http://www.personide.com
 * Description:       Integrate Personide and personalize your customers' experience
 * Version:           1.1.4
 * Author:            Personide
 * Author URI:        http://www.personide.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       personide
 * Domain Path:       /languages
 * Requires at least: 4.9.0
 * Tested up to:      4.9.6
 * WC requires at least:  3.4.0
 * WC tested up to:  3.4.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'PERSONIDE_VERSION', '1.1.4' );

require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
  'http://18.136.134.232/woocommerce/?action=get_metadata&slug=personide-for-woocommerce',
  __FILE__,
  'personide-for-woocommerce' 
);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-personide-activator.php
 */
function activate_personide() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-personide-activator.php';
	Personide_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-personide-deactivator.php
 */
function deactivate_personide() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-personide-deactivator.php';
	Personide_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_personide' );
register_deactivation_hook( __FILE__, 'deactivate_personide' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-personide.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_personide() {
	$plugin = new Personide();
	$plugin->run();
}

if(in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )) {
  add_action('woocommerce_init', function() {
    $logger = wc_get_logger();
    $logger->debug("---- Launching Personide for WooCommerce ----", array( 'source' => 'personide' ));
    run_personide();
  });
}