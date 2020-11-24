<?php
/**
 * File define gls courier shipping method.
 *
 * @link  https://www.kuskosoft.com
 * @since 2.3.0
 *
 * @package    Neoship
 * @subpackage Neoship/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-neoship-wc-shipipng-class.php';

/**
 * This is used to define sps courier shipping method.
 *
 * @since      2.3.0
 * @package    Neoship
 * @subpackage Neoship/includes
 * @author     Mirec <hutar@kuskosoft.com>
 */
class Neoship_WC_SpsCourier_Shipping_Method extends Neoship_WC_Shipping {

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Instance id.
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );
		$this->id           = 'neoship_spscourier';
		$this->method_title = __( 'SPS Courier', 'neoship' );
		$this->title        = __( 'SPS Courier', 'neoship' );
		$this->init();
	}

}
