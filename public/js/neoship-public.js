/**
 * Public js gile
 *
 * @package    Neoship
 */

( function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			$( '#parcelshop_id' ).select2();
		}
	);

	$( document ).on(
		'updated_checkout',
		function () {
			$( '#parcelshop_id' ).select2();
		}
	);

} )( jQuery );
