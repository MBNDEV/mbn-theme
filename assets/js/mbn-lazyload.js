/**
 * MBN Theme — deferred styles + scripts loader.
 *
 * Pairs with optimizations.php:
 *   - Re-applies deferred font CSS (style[type="text/lazystyle"]) on window load,
 *     so webfonts load late.
 *   - Runs lazy inline scripts (script[type="lazyload"]) after the page is
 *     interactive, in document order, via Blob URLs.
 *
 * Plain browser JS, no dependencies.
 */
( function () {
	'use strict';

	function enableLazyStyles() {
		var styles = document.querySelectorAll( 'style[type="text/lazystyle"]' );
		Array.prototype.forEach.call( styles, function ( node ) {
			var live = document.createElement( 'style' );
			live.textContent = node.textContent;
			if ( node.id ) {
				live.id = node.id + '-active';
			}
			node.parentNode.insertBefore( live, node );
			node.parentNode.removeChild( node );
		} );
	}

	function runLazyScripts() {
		var scripts = document.querySelectorAll( 'script[type="lazyload"]' );
		Array.prototype.forEach.call( scripts, function ( node ) {
			var blob = new Blob( [ node.textContent ], { type: 'text/javascript' } );
			var run = document.createElement( 'script' );
			run.src = URL.createObjectURL( blob );
			run.async = false; // preserve execution order
			node.parentNode.replaceChild( run, node );
		} );
	}

	function whenIdle( callback ) {
		if ( 'requestIdleCallback' in window ) {
			window.requestIdleCallback( callback, { timeout: 2000 } );
		} else {
			window.setTimeout( callback, 200 );
		}
	}

	// Fonts: late (on load).
	if ( document.readyState === 'complete' ) {
		enableLazyStyles();
	} else {
		window.addEventListener( 'load', enableLazyStyles );
	}

	// Inline scripts: after the page is interactive, when idle.
	if ( document.readyState !== 'loading' ) {
		whenIdle( runLazyScripts );
	} else {
		document.addEventListener( 'DOMContentLoaded', function () {
			whenIdle( runLazyScripts );
		} );
	}
} )();
