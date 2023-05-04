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
			
			$( "." +
				"neoship-shipper-change" ).change(function(e) {

				if ( e.target.value === 'sps' ) {
					$( $(this).data("rowid") ).removeClass("neoship-gls");
					$( $(this).data("rowid") ).removeClass("neoship-packeta");
					$( $(this).data("rowid") ).removeClass("neoship-123");
					$( $(this).data("rowid") ).removeClass("neoship-dpd");
					$( $(this).data("rowid") ).addClass("neoship-sps");
				} else if ( e.target.value === 'gls' ) {
					$( $(this).data("rowid") ).removeClass("neoship-sps");
					$( $(this).data("rowid") ).removeClass("neoship-packeta");
					$( $(this).data("rowid") ).removeClass("neoship-123");
					$( $(this).data("rowid") ).removeClass("neoship-dpd");
					$( $(this).data("rowid") ).addClass("neoship-gls");
				} else if ( e.target.value === 'packeta' ) {
					$( $(this).data("rowid") ).removeClass("neoship-gls");
					$( $(this).data("rowid") ).removeClass("neoship-sps");
					$( $(this).data("rowid") ).removeClass("neoship-123");
					$( $(this).data("rowid") ).removeClass("neoship-dpd");
					$( $(this).data("rowid") ).addClass("neoship-packeta");
				} else if ( e.target.value === '123' ) {
					$( $(this).data("rowid") ).removeClass("neoship-gls");
					$( $(this).data("rowid") ).removeClass("neoship-sps");
					$( $(this).data("rowid") ).removeClass("neoship-packeta");
					$( $(this).data("rowid") ).removeClass("neoship-dpd");
					$( $(this).data("rowid") ).addClass("neoship-123");
				} else if ( e.target.value === 'dpd' ) {
					$( $(this).data("rowid") ).removeClass("neoship-gls");
					$( $(this).data("rowid") ).removeClass("neoship-sps");
					$( $(this).data("rowid") ).removeClass("neoship-packeta");
					$( $(this).data("rowid") ).removeClass("neoship-123");
					$( $(this).data("rowid") ).addClass("neoship-dpd");
				}

			});
		}
	);

} )( jQuery );
