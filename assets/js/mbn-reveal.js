/**
 * MBN Theme — scroll-reveal animations.
 *
 * Any element with `data-mbn-reveal="<type>"` (fade / slide-up / slide-left /
 * slide-right / zoom) reveals its direct children in order as it scrolls into
 * view. The hidden/visible states are CSS (resources/css/app.css); this script
 * just adds the `mbn-revealed` class and a per-child transition-delay so the
 * children animate sequentially. Respects prefers-reduced-motion.
 *
 * Plain browser JS, no dependencies.
 */
( function () {
	'use strict';

	var reduceMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function toArray( nodeList ) {
		return Array.prototype.slice.call( nodeList );
	}

	function reveal( el ) {
		if ( el.dataset.mbnRevealed ) {
			return;
		}
		el.dataset.mbnRevealed = '1';

		if ( ! reduceMotion ) {
			toArray( el.children ).forEach( function ( child, i ) {
				child.style.transitionDelay = i * 0.1 + 's';
			} );
		}
		el.classList.add( 'mbn-revealed' );
	}

	function init() {
		var els = toArray(
			document.querySelectorAll( '[data-mbn-reveal]:not([data-mbn-reveal="none"])' )
		);
		if ( ! els.length ) {
			return;
		}

		// Enable the hidden start state only now that the script is running, so the
		// content is never hidden where this script doesn't run (the block editor,
		// or JS disabled) — progressive enhancement.
		document.documentElement.classList.add( 'mbn-reveal-ready' );

		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			els.forEach( reveal );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						reveal( entry.target );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '0px 0px -10% 0px', threshold: 0.12 }
		);

		els.forEach( function ( el ) {
			observer.observe( el );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
