/**
 * Admin js file
 *
 * @package    Neoship
 */

( function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			$( 'a.neoship-tracking' ).attr( 'target', '_blank' );
			
			$( ".neoship-shipper-change" ).change(function(e) {

				if ( e.target.value === 'sps' ) {
					$( $(this).data("rowid") ).removeClass("neoship-gls");
					$( $(this).data("rowid") ).addClass("neoship-sps");
				} else {
					$( $(this).data("rowid") ).removeClass("neoship-sps");
					$( $(this).data("rowid") ).addClass("neoship-gls");
				}

			});
		}
	);

} )( jQuery );
