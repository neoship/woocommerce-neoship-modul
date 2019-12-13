<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.kuskosoft.com
 * @since      1.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Neoship
 * @subpackage Neoship/public
 * @author     Mirec <hutar@kuskosoft.com>
 */
class Neoship_Public {

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

	private $api;

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
		$this->api = new NeoshipApi();

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
		 * defined in Neoship_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Neoship_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/neoship-public.css', array(), $this->version, 'all' );
		if(is_checkout()){
			wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
		}

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
		 * defined in Neoship_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Neoship_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/neoship-public.js', array( 'jquery' ), $this->version, false );
		if(is_checkout()){
			wp_enqueue_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		}

	}

	function carrierFields( $method, $index ) {
		if( !is_checkout()) return; // Only on checkout page
	
		$customer_carrier_method = 'parcelshop';
		if( $method->method_id != $customer_carrier_method ) return; // Only display for "local_pickup"
		
		$chosen_method_id = WC()->session->chosen_shipping_methods[ $index ];
	
		if(strpos($chosen_method_id, $customer_carrier_method) !== false ) {
			$parcelshops = $this->api->getParcelShops();
			echo '<div class="parcelshop-carrier">';
			woocommerce_form_field( 'parcelshop_id' , array(
				'type'          => 'select',
				'class'         => array('form-row-wide carrier-name'),
				'required'      => true,
				//'default'		=> $parcelshop_id,
				'options'     => $parcelshops,
			), WC()->checkout->get_value( 'parcelshop_id' ));
			echo '</div>';
		}
	} 

	function checkCheckout() {
		if( isset( $_POST['parcelshop_id'] ) && empty( $_POST['parcelshop_id'] ) ) {
			wc_add_notice( ( __('Please fill parcelshop', 'neoship') ), "error" );
		}
		else if($_POST['parcelshop_id']) {
			$parcelshops = $this->api->getParcelShops();
			if(!array_key_exists($_POST['parcelshop_id'], $parcelshops)) {
				wc_add_notice( ( __('Choose correct parcelshop', 'neoship') ), "error" );
			}
		}
	}

	function updateCarriers( $order_id ) {
		if( isset( $_POST['parcelshop_id'] ))
			update_post_meta( $order_id, '_parcelshop_id', sanitize_text_field( $_POST['parcelshop_id'] ) );
	}

	function changeShipping ($order) {
		if(isset($_POST['parcelshop_id'])){
			$parcelshops = $this->api->getParcelShops(true);
			$parcelId = intval($_POST['parcelshop_id']);
			if(array_key_exists($parcelId, $parcelshops)) {
				$parcelshop = $parcelshops[$parcelId];
				$address = array(
					'first_name' => $parcelshop['address']['name'],
					'last_name'  => '',
					'company' => $parcelshop['address']['company'],
					'address_1'  => $parcelshop['address']['street'],
					'address_2'  => '', 
					'city'       => $parcelshop['address']['city'],
					'state'      => '',
					'postcode'   => $parcelshop['address']['zip'],
					'country'    => $parcelshop['address']['state']['code']
				);
				$order->set_address( $address, 'shipping' );
			}
		}
		return $order;
	}

}