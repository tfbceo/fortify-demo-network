/*
 * Location finder: search the embedded registry data by ZIP, city or county,
 * filter by state, and show the offices on a Leaflet map and in the list.
 * The card markup matches Elementor_Integration::office_card() in PHP.
 */
( function () {
	'use strict';

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function fill( template, vars ) {
		return String( template ).replace( /\{(\w+)\}/g, function ( m, k ) {
			return k in vars ? vars[ k ] : m;
		} );
	}

	function norm( s ) {
		return String( s ).toLowerCase().normalize( 'NFD' ).replace( /[̀-ͯ]/g, '' ).replace( /[–—]/g, '-' );
	}

	// Mirrors Elementor_Integration::county_label() in PHP.
	function countyLabel( list, L ) {
		if ( ! list.length ) {
			return '';
		}
		var parts = [], notes = [];
		list.forEach( function ( c ) {
			var m = c.match( /^(.*?)\s*\((.+)\)$/ );
			if ( m ) {
				parts.push( m[ 1 ] );
				notes.push( m[ 2 ] );
			} else {
				parts.push( c );
			}
		} );
		var last = parts.pop();
		var names = parts.length ? parts.join( ', ' ) + ' ' + L.and + ' ' + last : last;
		var label = names + ' ' + ( list.length > 1 ? L.counties : L.county );
		return notes.length ? label + ' (' + notes.join( ', ' ) + ')' : label;
	}

	function card( o, L ) {
		var soon = o.status === 'coming_soon';
		var counties = countyLabel( o.counties, L );
		var meta = [ counties, o.state_name, o.mls ].filter( Boolean ).join( ' · ' );
		return '<article class="fo-card' + ( soon ? ' is-soon' : '' ) + '" data-slug="' + esc( o.slug ) + '">' +
			'<div class="fo-card-head"><h3 class="fo-card-name">' + esc( o.name ) + '</h3>' +
			'<span class="fo-badge ' + ( soon ? 'is-soon' : 'is-live' ) + '">' + esc( soon ? L.soon : L.live ) + '</span></div>' +
			'<p class="fo-card-meta">' + esc( meta ) + '</p>' +
			( soon ? '' : '<a class="fo-card-link" href="' + esc( o.url ) + '">' + esc( L.visit ) + '</a>' ) +
			'</article>';
	}

	function init( root ) {
		var dataEl = root.querySelector( '.fo-finder-data' );
		if ( ! dataEl ) {
			return;
		}
		var data = JSON.parse( dataEl.textContent );
		var L = data.labels;
		var form = root.querySelector( '.fo-finder-form' );
		var input = form.querySelector( '[name="q"]' );
		var stateSel = form.querySelector( '[name="state"]' );
		var list = root.querySelector( '.fo-finder-list' );
		var status = root.querySelector( '.fo-finder-status' );
		var mapEl = root.querySelector( '.fo-finder-map' );
		var map = null;
		var markers = {};

		if ( mapEl && window.L ) {
			map = window.L.map( mapEl, { scrollWheelZoom: false } );
			window.L.tileLayer( 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 18,
				attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
			} ).addTo( map );
			data.offices.forEach( function ( o ) {
				if ( o.lat == null || o.lng == null ) {
					return;
				}
				var m = window.L.circleMarker( [ o.lat, o.lng ], {
					radius: 9,
					weight: 3,
					color: '#fff',
					fillColor: o.status === 'coming_soon' ? '#8B8AA3' : '#4F46E5',
					fillOpacity: 1
				} ).addTo( map );
				m.bindPopup( '<strong>' + esc( o.name ) + '</strong><br>' + esc( o.state_name ) +
					( o.status === 'coming_soon' ? '<br>' + esc( L.soon ) : '<br><a href="' + esc( o.url ) + '">' + esc( L.visit ) + '</a>' ) );
				markers[ o.slug ] = m;
			} );
		}

		function show( offices, message, notCovered ) {
			status.innerHTML = message || '';
			status.hidden = ! message;
			list.innerHTML = offices.map( function ( o ) {
				return card( o, L );
			} ).join( '' );
			if ( notCovered ) {
				list.innerHTML = '<div class="fo-not-covered"><p>' + esc( notCovered.text ) + '</p><a class="fo-card-link" href="' + esc( data.contact ) + '">' + esc( L.notCoveredLink ) + '</a></div>';
			}
			if ( map ) {
				var pts = [];
				Object.keys( markers ).forEach( function ( slug ) {
					var on = offices.some( function ( o ) {
						return o.slug === slug;
					} );
					markers[ slug ].setStyle( { fillOpacity: on ? 1 : 0.25, opacity: on ? 1 : 0.4 } );
					if ( on ) {
						pts.push( markers[ slug ].getLatLng() );
					}
				} );
				if ( pts.length === 1 ) {
					map.setView( pts[ 0 ], 9 );
				} else if ( pts.length ) {
					map.fitBounds( pts, { padding: [ 30, 30 ], maxZoom: 9 } );
				} else {
					map.fitBounds( Object.keys( markers ).map( function ( k ) {
						return markers[ k ].getLatLng();
					} ), { padding: [ 30, 30 ] } );
				}
			}
		}

		function search() {
			var q = input.value.trim();
			var st = stateSel.value;
			var pool = data.offices.filter( function ( o ) {
				return ! st || o.state === st;
			} );
			var zip = ( q.match( /(?:^|\D)(\d{5})(?:-\d{4})?(?!\d)/ ) || [] )[ 1 ];

			if ( zip ) {
				var hit = data.offices.filter( function ( o ) {
					return o.zips.indexOf( zip ) !== -1;
				} )[ 0 ];
				if ( hit && ( ! st || hit.state === st ) ) {
					show( [ hit ], esc( fill( hit.status === 'coming_soon' ? L.zipSoon : L.zipMatch, { zip: zip, office: hit.name } ) ) );
				} else {
					show( [], '', { text: fill( L.notCovered, { zip: zip } ) } );
				}
				return;
			}
			if ( ! q ) {
				show( pool, '' );
				return;
			}
			var needle = norm( q ).replace( /\s+county$/, '' );
			var found = pool.filter( function ( o ) {
				return norm( [ o.name, o.city, o.state, o.state_name ].concat( o.counties ).join( ' | ' ) ).indexOf( needle ) !== -1;
			} );
			show( found, found.length ? '' : esc( fill( L.noResults, { query: q } ) ) );
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			search();
			var url = new URL( window.location.href );
			if ( input.value.trim() ) {
				url.searchParams.set( 'q', input.value.trim() );
			} else {
				url.searchParams.delete( 'q' );
			}
			window.history.replaceState( null, '', url );
		} );
		stateSel.addEventListener( 'change', search );
		search();
	}

	// Single-office map (Office Info widget, field "map").
	function officeMap( el ) {
		if ( ! window.L ) {
			return;
		}
		var pos = [ parseFloat( el.dataset.lat ), parseFloat( el.dataset.lng ) ];
		var map = window.L.map( el, { scrollWheelZoom: false } ).setView( pos, 11 );
		window.L.tileLayer( 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
		} ).addTo( map );
		window.L.circleMarker( pos, { radius: 10, weight: 3, color: '#fff', fillColor: '#4F46E5', fillOpacity: 1 } )
			.addTo( map ).bindPopup( esc( el.dataset.name ) );
	}

	function boot() {
		document.querySelectorAll( '[data-fo-map]' ).forEach( function ( el ) {
			if ( ! el.dataset.foReady ) {
				el.dataset.foReady = '1';
				officeMap( el );
			}
		} );
		document.querySelectorAll( '[data-fo-finder]' ).forEach( function ( el ) {
			if ( ! el.dataset.foReady ) {
				el.dataset.foReady = '1';
				init( el );
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
	window.addEventListener( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && window.elementorFrontend.hooks ) {
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/fortify-office-finder.default', boot );
			window.elementorFrontend.hooks.addAction( 'frontend/element_ready/fortify-office-info.default', boot );
		}
	} );
}() );
