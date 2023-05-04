<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  https://www.kuskosoft.com
 * @since 1.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/includes
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Neoship
 * @subpackage Neoship/includes
 * @author     Mirec <hutar@kuskosoft.com>
 */
class Neoship {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Neoship_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'NEOSHIP_VERSION' ) ) {
			$this->version = NEOSHIP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'neoship';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Neoship_Loader. Orchestrates the hooks of the plugin.
	 * - Neoship_I18n. Defines internationalization functionality.
	 * - Neoship_Admin. Defines all hooks for the admin area.
	 * - Neoship_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-neoship-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-neoship-public.php';

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-neoship3-api.php';

        include_once plugin_dir_path( dirname( __FILE__ ) ) . '/../woocommerce/includes/admin/list-tables/class-wc-admin-list-table-orders.php';

		$this->loader = new Neoship_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Neoship_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {

		$plugin_i18n = new Neoship_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Neoship_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_neoship_post_statuses' );
		$this->loader->add_action( 'wc_order_statuses', $plugin_admin, 'add_neoship_order_statuses' );
		$this->loader->add_action( 'woocommerce_admin_order_actions', $plugin_admin, 'add_neoship_order_list_actions', 2, 2 );
		$this->loader->add_action( 'bulk_actions-edit-shop_order', $plugin_admin, 'neoship_order_list_bulk_actions', 2, 1 );
		$this->loader->add_action( 'handle_bulk_actions-edit-shop_order', $plugin_admin, 'handle_bulk_action_export_packages_to_neoship', 10, 3 );
		$this->loader->add_action( 'handle_bulk_actions-edit-shop_order', $plugin_admin, 'handle_bulk_action_print_stickers', 10, 3 );
		$this->loader->add_action( 'handle_bulk_actions-edit-shop_order', $plugin_admin, 'handle_bulk_action_print_acceptance_protocol', 10, 3 );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'neoship_bulk_action_admin_notice' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'settings_page' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'settings_init' );
		$this->loader->add_action( 'woocommerce_shipping_init', $plugin_admin, 'request_shipping_init' );
		$this->loader->add_filter( 'woocommerce_shipping_methods', $plugin_admin, 'request_shipping_method' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {

		$plugin_public = new Neoship_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'woocommerce_after_shipping_rate', $plugin_public, 'carrier_fields', 20, 2 );
		$this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'check_checkout' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'update_carriers', 30, 1 );
		$this->loader->add_filter( 'woocommerce_checkout_create_order', $plugin_public, 'change_shipping', 10, 1 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return Neoship_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
