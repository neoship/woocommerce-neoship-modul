<?php
/**
 * File define gls courier shipping method.
 *
 * @link  https://www.kuskosoft.com
 * @since 2.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This is used to define gls courier shipping method.
 *
 * @since      2.0.0
 * @package    Neoship
 * @subpackage Neoship/includes
 * @author     Mirec <hutar@kuskosoft.com>
 */
class Neoship_WC_GlsCourier_Shipping_Method extends WC_Shipping_Method {

	/**
	 * Min amount to be free shipping.
	 *
	 * @var integer
	 */
	public $min_amount = 0;

	/**
	 * Requires option.
	 *
	 * @var string
	 */
	public $requires = '';

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Instance id.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id           = 'neoship_glscourier';
		$this->instance_id  = absint( $instance_id );
		$this->method_title = __( 'Gls Courier', 'neoship' );
		$this->title        = __( 'Gls Courier', 'neoship' );
		$this->supports     = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();
	}

	/**
	 * Initialize custom shiping method.
	 */
	public function init() {

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title      		= $this->get_option( 'title' );
		$this->tax_status 		= $this->get_option( 'tax_status' );
		$this->cost       		= $this->get_option( 'cost' );
		$this->min_amount       = $this->get_option( 'min_amount', 0 );
		$this->requires         = $this->get_option( 'requires' );
		$this->ignore_discounts = $this->get_option( 'ignore_discounts' );

		// Actions.
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	/**
	 * Calculate local pickup shipping.
	 *
	 * @param array $package Package information.
	 */
	public function calculate_shipping( $package = array() ) {
		$this->add_rate(
			array(
				'label'   => $this->title,
				'package' => $package,
				'cost'    => $this->is_free_shipping_available( $package ) ? 0 : $this->cost,
			)
		);
	}

	/**
	 * Init form fields.
	 */
	public function init_form_fields() {
		$this->instance_form_fields = array(
			'title'      => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Gls Courier', 'neoship' ),
				'desc_tip'    => true,
			),
			'tax_status' => array(
				'title'   => __( 'Tax status', 'woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Taxable', 'woocommerce' ),
					'none'    => _x( 'None', 'Tax status', 'woocommerce' ),
				),
			),
			'cost'       => array(
				'title'       => __( 'Cost', 'woocommerce' ),
				'type'        => 'price',
				'placeholder' => '0',
				'description' => __( 'Optional cost for local pickup.', 'woocommerce' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'requires'         => array(
				'title'   => __( 'Free shipping requires...', 'woocommerce' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => '',
				'options' => array(
					''           => __( 'N/A', 'woocommerce' ),
					'coupon'     => __( 'A valid free shipping coupon', 'woocommerce' ),
					'min_amount' => __( 'A minimum order amount', 'woocommerce' ),
					'either'     => __( 'A minimum order amount OR a coupon', 'woocommerce' ),
					'both'       => __( 'A minimum order amount AND a coupon', 'woocommerce' ),
				),
			),
			'min_amount'       => array(
				'title'       => __( 'Minimum order amount', 'woocommerce' ),
				'type'        => 'price',
				'placeholder' => wc_format_localized_price( 0 ),
				'description' => __( 'Users will need to spend this amount to get free shipping (if enabled above).', 'woocommerce' ),
				'default'     => '0',
				'desc_tip'    => true,
			),
			'ignore_discounts' => array(
				'title'       => __( 'Coupons discounts', 'woocommerce' ),
				'label'       => __( 'Apply minimum order rule before coupon discount', 'woocommerce' ),
				'type'        => 'checkbox',
				'description' => __( 'If checked, free shipping would be available based on pre-discount order amount.', 'woocommerce' ),
				'default'     => 'no',
				'desc_tip'    => true,
			),
		);
	}
	
	/**
	 * Check if shipping can be free.
	 *
	 * @return boolean
	 */
	public function is_free_shipping_available( $package ) {
		$has_coupon         = false;
		$has_met_min_amount = false;

		if ( in_array( $this->requires, array( 'coupon', 'either', 'both' ), true ) ) {
			$coupons = WC()->cart->get_coupons();

			if ( $coupons ) {
				foreach ( $coupons as $code => $coupon ) {
					if ( $coupon->is_valid() && $coupon->get_free_shipping() ) {
						$has_coupon = true;
						break;
					}
				}
			}
		}

		if ( in_array( $this->requires, array( 'min_amount', 'either', 'both' ), true ) ) {
			$total = WC()->cart->get_displayed_subtotal();

			if ( WC()->cart->display_prices_including_tax() ) {
				$total = $total - WC()->cart->get_discount_tax();
			}

			if ( 'no' === $this->ignore_discounts ) {
				$total = $total - WC()->cart->get_discount_total();
			}

			$total = round( $total, wc_get_price_decimals(), PHP_ROUND_HALF_UP );

			if ( $total >= $this->min_amount ) {
				$has_met_min_amount = true;
			}
		}

		switch ( $this->requires ) {
			case 'min_amount':
				$is_available = $has_met_min_amount;
				break;
			case 'coupon':
				$is_available = $has_coupon;
				break;
			case 'both':
				$is_available = $has_met_min_amount && $has_coupon;
				break;
			case 'either':
				$is_available = $has_met_min_amount || $has_coupon;
				break;
			default:
				$is_available = false;
				break;
		}

		return $is_available;
	}
}
