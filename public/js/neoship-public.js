/**
 * Public js file
 *
 * @package    Neoship
 */

( function ( $ ) {
	'use strict';

	$( document ).ready(
		function () {
			if (typeof $('.test').select2 === "function") {
				if (!$( '#parcelshop_name' ).length) $( '#parcelshop_id' ).select2();
				if (!$( '#glsparcelshop_name' ).length) $( '#glsparcelshop_id' ).select2();
			}
		}
	);

	$( document ).on(
		'updated_checkout',
		function () {
			if (typeof $('.test').select2 === "function") {
				if (!$( '#parcelshop_name' ).length) $( '#parcelshop_id' ).select2();
				if (!$( '#glsparcelshop_name' ).length) $( '#glsparcelshop_id' ).select2();
			}

            $( '#parcelshop_name' ).on('click', function(event){
                event.preventDefault();
				var onSelectParcelshop = (parcelshop) => {
					if (parcelshop) {
						$(this).val(parcelshop.name);
						$('#parcelshop_id').val(parcelshop.id);
					}
				};
				ParcelshopIframe.Widget.open(onSelectParcelshop, {shipper_id:2});

            });

			$( '#glsparcelshop_name' ).on('click', function(event){
				event.preventDefault();
				var onSelectParcelshop = (parcelshop) => {
					if (parcelshop) {
						$(this).val(parcelshop.name);
						$('#glsparcelshop_id').val(parcelshop.id);
					}
				};
				ParcelshopIframe.Widget.open(onSelectParcelshop, {shipper_id:1});
			});

			$( '#packeta_name' ).on('click', function(event){
				event.preventDefault();
				var onSelectParcelshop = (parcelshop) => {
					if (parcelshop) {
						$(this).val(parcelshop.name);
						$('#packeta_id').val(parcelshop.id);
					}
				};
				ParcelshopIframe.Widget.open(onSelectParcelshop, {shipper_id:3});
			});

			$( '#dpdparcelshop_name' ).on('click', function(event){
				event.preventDefault();
				var onSelectParcelshop = (parcelshop) => {
					if (parcelshop) {
						$(this).val(parcelshop.name);
						$('#dpdparcelshop_id').val(parcelshop.id);
					}
				};
				ParcelshopIframe.Widget.open(onSelectParcelshop, {shipper_id:5});
			});
		}
	);

} )( jQuery );
