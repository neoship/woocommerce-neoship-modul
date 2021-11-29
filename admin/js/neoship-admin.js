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
					$( $(this).data("rowid") ).removeClass("neoship-packeta");
					$( $(this).data("rowid") ).addClass("neoship-sps");
				} else if ( e.target.value === 'gls' ) {
					$( $(this).data("rowid") ).removeClass("neoship-sps");
					$( $(this).data("rowid") ).removeClass("neoship-packeta");
					$( $(this).data("rowid") ).addClass("neoship-gls");
				} else {
					$( $(this).data("rowid") ).removeClass("neoship-gls");
					$( $(this).data("rowid") ).removeClass("neoship-sps");
					$( $(this).data("rowid") ).addClass("neoship-packeta");
				}

			});
		}
	);

} )( jQuery );
