/**
 * Scroll-reveal animations — Elementor-style entrance set.
 *
 * Watches every [data-animate] element and adds `.is-visible` once it enters
 * the viewport. CSS keyframes (assets/css/scroll-animations.css) handle the
 * actual fade / zoom / bounce / slide / rotate / lightspeed / roll motion.
 *
 * Per-element configuration via data attributes:
 *   data-animate           — animation name (e.g. fadeInUp, zoomIn, bounceInLeft, …)
 *   data-animate-duration  — "slow" | "fast"  (default = normal, 1.25s)
 *   data-animate-delay     — milliseconds, e.g. "200" → 200ms delay before playing
 *
 * Above-the-fold elements are revealed instantly (no animation) on first paint.
 * Only off-screen elements actually animate as the user scrolls them into view.
 *
 * Stagger: a parent with [data-animate-stagger] gives each direct
 * [data-animate] child an incremental 100ms transition-delay automatically.
 *
 * @package CustomTheme
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var $animated            = $( '[data-animate]' );
		// Force animations - ignore OS prefers-reduced-motion setting
		var prefersReducedMotion = false;

		if ( $animated.length === 0 ) {
			return;
		}

		// Apply per-element animation-delay from data-animate-delay (in ms).
		$animated.each( function () {
			var delayMs = parseInt( this.getAttribute( 'data-animate-delay' ), 10 );
			if ( delayMs > 0 ) {
				this.style.setProperty( '--animation-delay', delayMs + 'ms' );
			}
		} );

		function revealInstant( $el ) {
			$el.addClass( 'no-anim is-visible' );
		}

		// OS-level reduce-motion: show everything immediately, no transitions.
		if ( prefersReducedMotion ) {
			$animated.each( function () {
				revealInstant( $( this ) );
			} );
			return;
		}

		// Apply stagger delays on direct [data-animate] children of any
		// [data-animate-stagger] parent — additive on top of data-animate-delay.
		$( '[data-animate-stagger]' ).each( function () {
			$( this ).children( '[data-animate]' ).each( function ( i ) {
				var existing = parseInt( this.getAttribute( 'data-animate-delay' ), 10 ) || 0;
				this.style.setProperty( '--animation-delay', ( existing + i * 100 ) + 'ms' );
			} );
		} );

		function isInViewportNow( el ) {
			var rect           = el.getBoundingClientRect();
			var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
			return rect.top < viewportHeight && rect.bottom > 0;
		}

		// Browsers without IntersectionObserver: degrade to instant reveal.
		if ( typeof window.IntersectionObserver === 'undefined' ) {
			$animated.each( function () {
				revealInstant( $( this ) );
			} );
			return;
		}

		var observer = new window.IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						$( entry.target ).addClass( 'is-visible' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{
				threshold: 0.08,
				rootMargin: '0px 0px -40px 0px',
			}
		);

		// Split into above-the-fold (instant) vs off-screen (observe).
		$animated.each( function () {
			if ( isInViewportNow( this ) ) {
				revealInstant( $( this ) );
				return;
			}
			observer.observe( this );
		} );
	} );
}( jQuery ) );
