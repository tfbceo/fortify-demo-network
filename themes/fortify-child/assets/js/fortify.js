/* Fortify site behaviors: show/hide panels (the plans feature table). */
( function () {
	'use strict';

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-fx-toggle]' );
		if ( ! btn ) {
			return;
		}
		var panel = document.getElementById( btn.getAttribute( 'aria-controls' ) );
		if ( ! panel ) {
			return;
		}
		var open  = btn.getAttribute( 'aria-expanded' ) === 'true';
		var label = btn.querySelector( '[data-fx-toggle-label]' );
		btn.setAttribute( 'aria-expanded', String( ! open ) );
		panel.classList.toggle( 'is-open', ! open );
		if ( label ) {
			label.textContent = open ? btn.dataset.labelClosed : btn.dataset.labelOpen;
		}
		if ( ! open ) {
			setTimeout( function () {
				panel.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
			}, 120 );
		}
	} );
}() );
