<?php
/**
 * File define parcelshop shipping method.
 *
 * @link  https://www.kuskosoft.com
 * @since 1.0.0
 *
 * @package    Neoship
 * @subpackage Neoship/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-neoship-wc-shipipng-class.php';

/**
 * This is used to define parcelshop shipping method.
 *
 * @since      1.0.0
 * @package    Neoship
 * @subpackage Neoship/includes
 * @author     Mirec <hutar@kuskosoft.com>
 */
class Neoship_WC_Parcelshop_Shipping_Method extends Neoship_WC_Shipping {

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Instance id.
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );
		$this->id           = 'parcelshop';
		$this->method_title = __( 'Parcelshop', 'neoship' );
		$this->title        = __( 'Parcelshop', 'neoship' );
		$this->init();
	}
}
