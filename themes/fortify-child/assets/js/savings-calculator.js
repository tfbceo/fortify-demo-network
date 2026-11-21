/* Fortify savings calculator. Works on the front end and in the Elementor editor preview. */
( function () {
	'use strict';

	function money( n ) {
		return '$' + Math.round( n ).toLocaleString( 'en-US' );
	}

	function init( root ) {
		if ( ! root || root.dataset.fxSvReady ) {
			return;
		}
		root.dataset.fxSvReady = '1';

		var range = root.querySelector( 'input[type="range"]' );
		var price = root.querySelector( '[data-fx-sv-price]' );
		var comm  = root.querySelector( '[data-fx-sv-comm]' );
		var keep  = root.querySelector( '[data-fx-sv-keep]' );
		var rate  = parseFloat( root.dataset.rate ) || 0;
		var fee   = parseFloat( root.dataset.fee ) || 0;
		if ( ! range ) {
			return;
		}

		function update() {
			var p = +range.value;
			var c = Math.round( p * rate / 100 );
			var k = c - fee;
			price.textContent = money( p );
			comm.textContent  = '−' + money( c );
			keep.textContent  = ( k < 0 ? '−' : '+' ) + money( Math.abs( k ) );
		}

		range.addEventListener( 'input', update );
		update();
	}

	function initAll( scope ) {
		( scope || document ).querySelectorAll( '[data-fx-sv]' ).forEach( init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { initAll(); } );
	} else {
		initAll();
	}

	// Elementor editor: re-run when the widget is (re)rendered.
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/fortify-savings-calculator.default', function ( $scope ) {
				initAll( $scope[ 0 ] );
			} );
		}
	} );
}() );
