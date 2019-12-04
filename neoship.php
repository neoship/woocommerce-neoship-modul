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
 * WC requires at least: 2.2
 * WC tested up to: 3.8.0
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

function errorMissingWC() {
	?>
       <div class="notice notice-error">
          <p><?php _e( 'For using neoship you need activate WooCommerce plugin', 'neoship' );?></p>
       </div>
	<?php 
}

add_filter( 'admin_body_class', function( $classes ) {
	$classes .= ' post-type-shop_order ';
	return $classes;
});

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'NEOSHIP_VERSION', '1.0.0' );
define( 'NEOSHIP_API_URL', 'http://api.neoship.loc' );
define( 'NEOSHIP_TRACKING_URL', 'http://neoship.loc' );

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