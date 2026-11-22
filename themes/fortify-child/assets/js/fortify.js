/* Fortify site behaviors: show/hide panels (the plans feature table) and
 * in-page navigation highlighting (Plans anchor bar, FAQs sidebar). */
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

/*
 * Scrollspy: in a .fx-spy list of #anchor links, mark the link whose target
 * section is in view with .is-active.
 */
( function () {
	'use strict';

	function init() {
		document.querySelectorAll( '.fx-spy' ).forEach( function ( nav ) {
			var links = Array.prototype.filter.call( nav.querySelectorAll( 'a[href^="#"]' ), function ( a ) {
				return a.getAttribute( 'href' ).length > 1 && document.getElementById( a.getAttribute( 'href' ).slice( 1 ) );
			} );
			if ( ! links.length || ! ( 'IntersectionObserver' in window ) ) {
				return;
			}
			var byId = {};
			links.forEach( function ( a ) {
				byId[ a.getAttribute( 'href' ).slice( 1 ) ] = a;
			} );
			var observer = new IntersectionObserver( function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						links.forEach( function ( a ) {
							a.classList.remove( 'is-active' );
						} );
						byId[ entry.target.id ].classList.add( 'is-active' );
					}
				} );
			}, { rootMargin: '-30% 0px -60% 0px' } );
			Object.keys( byId ).forEach( function ( id ) {
				observer.observe( document.getElementById( id ) );
			} );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
