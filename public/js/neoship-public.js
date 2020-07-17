/**
 * Public js gile
 *
 * @package    Neoship
 */

( function ( $ ) {
	'use strict';
	
	$( document ).ready(
		function () {
			if (typeof $('.test').select2 === "function") {
				$( '#parcelshop_id' ).select2();
				$( '#glsparcelshop_id' ).select2();
			}
		}
	);

	$( document ).on(
		'updated_checkout',
		function () {
			if (typeof $('.test').select2 === "function") {
				$( '#parcelshop_id' ).select2();
				$( '#glsparcelshop_id' ).select2();
			}
		}
	);

} )( jQuery );
