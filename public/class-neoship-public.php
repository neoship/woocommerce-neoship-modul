<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link  https://www.kuskosoft.com
 * @since 1.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/public
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $api    Neoship api instance.
	 */
	private $api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->api         = new Neoship_Api();

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
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
		if ( is_checkout() ) {
			wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
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
		if ( is_checkout() ) {
			wp_enqueue_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), $this->version, false );
		}

	}

	/**
	 * Generate carrier fields.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method Method name.
	 * @param int    $index Index.
	 */
	public function carrier_fields( $method, $index ) {
		if ( ! is_checkout() ) {
			return; // Only on checkout page.
		}

		$customer_carrier_method = 'parcelshop';

		if ( $method->method_id === $customer_carrier_method ) {
			$chosen_method_id = WC()->session->chosen_shipping_methods[ $index ];
			if ( substr($chosen_method_id, 0, strlen($customer_carrier_method) ) == $customer_carrier_method ) {
				$parcelshops = $this->api->get_parcel_shops();
				echo '<div class="parcelshop-carrier">';
				woocommerce_form_field(
					'parcelshop_id',
					array(
						'type'     => 'select',
						'class'    => array( 'form-row-wide carrier-name' ),
						'required' => true,
						'options'  => $parcelshops,
					),
					WC()->checkout->get_value( 'parcelshop_id' )
				);
				echo '</div>';
			}
		}

		$customer_carrier_method = 'neoship_glsparcelshop';

		if ( $method->method_id === $customer_carrier_method ) {
			$chosen_method_id = WC()->session->chosen_shipping_methods[ $index ];
			if ( substr($chosen_method_id, 0, strlen($customer_carrier_method) ) == $customer_carrier_method ) {
				$parcelshops = $this->api->get_gls_parcel_shops();
				echo '<div class="parcelshop-carrier">';
				woocommerce_form_field(
					'glsparcelshop_id',
					array(
						'type'     => 'select',
						'class'    => array( 'form-row-wide carrier-name' ),
						'required' => true,
						'options'  => $parcelshops,
					),
					WC()->checkout->get_value( 'glsparcelshop_id' )
				);
				echo '</div>';
			}
		}

	}

	/**
	 * Validate parcelshops.
	 *
	 * @since 1.0.0
	 */
	public function check_checkout() {
		if ( isset( $_POST['parcelshop_id'] ) && false !== wp_verify_nonce( wp_unslash( $sanitized_id ) ) && empty( $_POST['parcelshop_id'] ) ) {
			wc_add_notice( ( __( 'Please fill parcelshop', 'neoship' ) ), 'error' );
		} elseif ( isset( $_POST['parcelshop_id'] ) && false !== wp_verify_nonce( wp_unslash( $sanitized_id ) ) ) {
			$parcelshops  = $this->api->get_parcel_shops();
			$sanitized_id = intval( $_POST['parcelshop_id'] );
			if ( ! array_key_exists( $sanitized_id, $parcelshops ) ) {
				wc_add_notice( ( __( 'Choose correct parcelshop', 'neoship' ) ), 'error' );
			}
		}

		if ( isset( $_POST['glsparcelshop_id'] ) && false !== wp_verify_nonce( wp_unslash( $sanitized_id ) ) && empty( $_POST['glsparcelshop_id'] ) ) {
			wc_add_notice( ( __( 'Please fill parcelshop', 'neoship' ) ), 'error' );
		} elseif ( isset( $_POST['glsparcelshop_id'] ) && false !== wp_verify_nonce( wp_unslash( $sanitized_id ) ) ) {
			$parcelshops  = $this->api->get_gls_parcel_shops();
			$sanitized_id = strval( $_POST['glsparcelshop_id'] );
			if ( ! array_key_exists( $sanitized_id, $parcelshops ) ) {
				wc_add_notice( ( __( 'Choose correct parcelshop', 'neoship' ) ), 'error' );
			}
		}
	}

	/**
	 * Save parcelshop id.
	 *
	 * @since 1.0.0
	 *
	 * @param int $order_id Id of order.
	 */
	public function update_carriers( $order_id ) {
		if ( isset( $_POST['parcelshop_id'] ) ) {
			$sanitized_id = intval( $_POST['parcelshop_id'] );
			update_post_meta( $order_id, '_parcelshop_id', $sanitized_id );
		}
		if ( isset( $_POST['glsparcelshop_id'] ) ) {
			$sanitized_id = strval( $_POST['glsparcelshop_id'] );
			update_post_meta( $order_id, '_glsparcelshop_id', $sanitized_id );
		}
	}

	/**
	 * Edit woocommerce order.
	 *
	 * @since 1.0.0
	 *
	 * @param wp_order $order Id of order.
	 */
	public function change_shipping( $order ) {
		if ( isset( $_POST['parcelshop_id'] ) ) {
			$sanitized_id = intval( $_POST['parcelshop_id'] );
			if ( false !== wp_verify_nonce( wp_unslash( $sanitized_id ) ) ) {
				return $order;
			}
			$parcelshops = $this->api->get_parcel_shops( true );
			$parcel_id   = $sanitized_id;
			if ( array_key_exists( $parcel_id, $parcelshops ) ) {
				$parcelshop = $parcelshops[ $parcel_id ];
				$address    = array(
					'first_name' => $parcelshop['address']['name'],
					'last_name'  => '',
					'company'    => $parcelshop['address']['company'],
					'address_1'  => $parcelshop['address']['street'],
					'address_2'  => '',
					'city'       => $parcelshop['address']['city'],
					'state'      => '',
					'postcode'   => $parcelshop['address']['zip'],
					'country'    => $parcelshop['address']['state']['code'],
				);
				$order->set_address( $address, 'shipping' );
			}
		}

		if ( isset( $_POST['glsparcelshop_id'] ) ) {
			$sanitized_id = strval( $_POST['glsparcelshop_id'] );
			if ( false !== wp_verify_nonce( wp_unslash( $sanitized_id ) ) ) {
				return $order;
			}
			$parcelshops = $this->api->get_gls_parcel_shops( true );
			$parcel_id   = $sanitized_id;
			if ( array_key_exists( $parcel_id, $parcelshops ) ) {
				$parcelshop    		   = $parcelshops[ $parcel_id ];
				$shipping_data 		   = $order->get_data()['shipping'];
				$first_name   		   = '';
				$last_name     		   = '';
				$is_different_shipping = isset( $_POST['ship_to_different_address'] ) && $_POST['ship_to_different_address'];

				if ( $is_different_shipping && isset( $_POST['shipping_first_name'] ) && '' !== $_POST['shipping_first_name'] ) {
					$first_name = sanitize_text_field( $_POST['shipping_first_name'] );
				}
				elseif ( isset( $_POST['billing_first_name'] ) ) {
					$first_name = sanitize_text_field( $_POST['billing_first_name'] );
				}

				if ( $is_different_shipping && isset( $_POST['shipping_last_name'] ) && '' !== $_POST['shipping_last_name'] ) {
					$last_name = sanitize_text_field( $_POST['shipping_last_name'] );
				}
				elseif ( isset( $_POST['billing_last_name'] ) ) {
					$last_name = sanitize_text_field( $_POST['billing_last_name'] );
				}

				$address = array(
					'first_name' => $first_name,
					'last_name'  => $last_name,
					'company'    => $parcelshop['name'],
					'address_1'  => $parcelshop['address'],
					'address_2'  => '',
					'city'       => $parcelshop['cityName'],
					'state'      => '',
					'postcode'   => $parcelshop['zipCode'],
					'country'    => $parcelshop['ctrCode'],
				);

				$order->set_address( $address, 'shipping' );
			}
		}

		return $order;
	}

}
