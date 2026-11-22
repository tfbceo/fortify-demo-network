/* Office Profile screen: add/remove team and review rows, pick team photos. */
( function ( $ ) {
	'use strict';

	var counter = Date.now();

	$( document ).on( 'click', '[data-fo-add]', function () {
		var type = $( this ).data( 'fo-add' );
		var html = $( '#tmpl-fo-' + type + '-row' ).html().replace( /__i__/g, 'n' + ( counter++ ) );
		$( '[data-fo-rows="' + type + '"]' ).append( html );
	} );

	$( document ).on( 'click', '[data-fo-remove]', function () {
		$( this ).closest( '.fo-row' ).remove();
	} );

	$( document ).on( 'click', '[data-fo-photo-pick]', function () {
		var $wrap = $( this ).closest( '.fo-photo' );
		var frame = wp.media( { title: 'Team photo', library: { type: 'image' }, multiple: false } );
		frame.on( 'select', function () {
			var a = frame.state().get( 'selection' ).first().toJSON();
			var src = ( a.sizes && a.sizes.medium ) ? a.sizes.medium.url : a.url;
			$wrap.find( '[data-fo-photo-id]' ).val( src );
			$wrap.find( '[data-fo-photo-preview]' ).html( $( '<img>', { src: src, alt: '' } ) );
		} );
		frame.open();
	} );

	$( document ).on( 'click', '[data-fo-photo-clear]', function () {
		var $wrap = $( this ).closest( '.fo-photo' );
		$wrap.find( '[data-fo-photo-id]' ).val( '' );
		$wrap.find( '[data-fo-photo-preview]' ).empty();
	} );
}( jQuery ) );
