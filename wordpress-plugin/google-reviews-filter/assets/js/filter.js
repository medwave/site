( function () {
	'use strict';

	function applyFilter( wrapper, filter ) {
		var buttons = wrapper.querySelectorAll( '.grf-filter-btn' );
		var reviews = wrapper.querySelectorAll( '.grf-review' );

		buttons.forEach( function ( btn ) {
			btn.classList.toggle( 'is-active', btn.getAttribute( 'data-filter' ) === filter );
		} );

		reviews.forEach( function ( review ) {
			var matches = filter === 'all' || review.getAttribute( 'data-sentiment' ) === filter;
			if ( matches ) {
				review.removeAttribute( 'hidden' );
			} else {
				review.setAttribute( 'hidden', 'hidden' );
			}
		} );
	}

	function init() {
		document.querySelectorAll( '.grf-wrapper' ).forEach( function ( wrapper ) {
			var defaultFilter = wrapper.getAttribute( 'data-default-filter' ) || 'all';

			wrapper.querySelectorAll( '.grf-filter-btn' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					applyFilter( wrapper, btn.getAttribute( 'data-filter' ) );
				} );
			} );

			applyFilter( wrapper, defaultFilter );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
