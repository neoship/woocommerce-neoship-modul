<?php
/**
 * Settings for flat rate shipping.
 */

defined( 'ABSPATH' ) || exit;

$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'woocommerce' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'woocommerce' );

$settings = array(
	'title'      => array(
		'title'       => __( 'Title', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'VAS NAZOV PREPRAVCU', 'neoship' ),
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

$shipping_classes = WC()->shipping()->get_shipping_classes();

if ( ! empty( $shipping_classes ) ) {
	$settings['class_costs'] = array(
		'title'       => __( 'Shipping class costs', 'woocommerce' ),
		'type'        => 'title',
		'default'     => '',
		/* translators: %s: URL for link. */
		'description' => sprintf( __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) ),
	);
	foreach ( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}
		$settings[ 'class_cost_' . $shipping_class->term_id ] = array(
			/* translators: %s: shipping class name */
			'title'             => sprintf( __( '"%s" shipping class cost', 'woocommerce' ), esc_html( $shipping_class->name ) ),
			'type'              => 'text',
			'placeholder'       => __( 'N/A', 'woocommerce' ),
			'description'       => $cost_desc,
			'default'           => $this->get_option( 'class_cost_' . $shipping_class->slug ), // Before 2.5.0, we used slug here which caused issues with long setting names.
			'desc_tip'          => true,
			'sanitize_callback' => array( $this, 'sanitize_cost' ),
		);
	}

	$settings['no_class_cost'] = array(
		'title'             => __( 'No shipping class cost', 'woocommerce' ),
		'type'              => 'text',
		'placeholder'       => __( 'N/A', 'woocommerce' ),
		'description'       => $cost_desc,
		'default'           => '',
		'desc_tip'          => true,
		'sanitize_callback' => array( $this, 'sanitize_cost' ),
	);

	$settings['type'] = array(
		'title'   => __( 'Calculation type', 'woocommerce' ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'default' => 'class',
		'options' => array(
			'class' => __( 'Per class: Charge shipping for each shipping class individually', 'woocommerce' ),
			'order' => __( 'Per order: Charge shipping for the most expensive shipping class', 'woocommerce' ),
		),
	);
}

return $settings;