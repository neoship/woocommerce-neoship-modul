<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.kuskosoft.com
 * @since             1.0.0
 * @package           Neoship
 *
 * @wordpress-plugin
 * Plugin Name:       Neoship
 * Plugin URI:        neoship
 * Description:       Export orders to neoship
 * Version:           1.0.0
 * Author:            Mirec
 * Author URI:        https://www.kuskosoft.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       neoship
 * Domain Path:       /languages
 * 
 * WC requires at least: 3.3
 * WC tested up to: 3.8.1
 *
 * Copyright: © 2009-2015 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Check if WooCommerce is active
 **/
if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'admin_notices', 'errorMissingWC' );
	return;
}

/**
 * check WooCommerce version
 */
if( ! function_exists('get_plugin_data') ){
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}
$plugin_folder = get_plugins( '/' . 'woocommerce' );
$plugin_file = 'woocommerce.php';

if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
	$version = explode('.', $plugin_folder[$plugin_file]['Version']);
	if($version[0] <= 3 && $version[1] <=2) {
		add_action( 'admin_notices', 'errorVersionWC' );
		return;
	}
} else {
	add_action( 'admin_notices', 'errorVersionWC' );
	return;
}

function errorMissingWC() {
	?>
       <div class="notice notice-error">
          <p><?php _e( 'For using neoship you need activate WooCommerce plugin', 'neoship' );?></p>
       </div>
	<?php 
}

function errorVersionWC() {
	?>
       <div class="notice notice-error">
		  <p>
		  	<?php _e( 'For using neoship you need WooCommerce version', 'neoship' );
			echo ' ' . '3.3+'  
			?>
		</p>
       </div>
	<?php 
}

add_filter( 'admin_body_class', function( $classes ) {
	$classes .= ' post-type-shop_order ';
	return $classes;
});

/**
 * include config
 */
require_once plugin_dir_path( __FILE__ ) . 'config.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-neoship-activator.php
 */
function activate_neoship() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-neoship-activator.php';
	Neoship_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-neoship-deactivator.php
 */
function deactivate_neoship() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-neoship-deactivator.php';
	Neoship_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_neoship' );
register_deactivation_hook( __FILE__, 'deactivate_neoship' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-neoship.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_neoship() {
	
	$plugin = new Neoship();
	$plugin->run();

}
run_neoship();