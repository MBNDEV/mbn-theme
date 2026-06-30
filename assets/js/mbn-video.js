/**
 * MBN Theme — lazy video loader.
 *
 * Swaps `data-src` → `src` on <video>/<source>, then loads the video. Autoplay
 * videos start once their sources are swapped. Enhancements over the basic swap:
 *   - IntersectionObserver: sources are swapped only when the video nears the
 *     viewport, so off-screen videos don't download until needed.
 *   - prefers-reduced-motion: autoplay videos are not auto-started.
 *   - Graceful fallback to an eager swap when IntersectionObserver is missing.
 *
 * Plain browser JS, no dependencies.
 */
( function () {
	'use strict';

	var reduceMotion =
		window.matchMedia &&
		window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/**
	 * Swap data-src → src for a video and its <source> children, then load/play.
	 *
	 * @param {HTMLVideoElement} video Target video element.
	 */
	function activateVideo( video ) {
		if ( video.dataset.mbnVideoLoaded ) {
			return;
		}
		video.dataset.mbnVideoLoaded = '1';

		var targets = [ video ].concat(
			Array.prototype.slice.call( video.querySelectorAll( 'source[data-src]' ) )
		);

		var swapped = false;
		targets.forEach( function ( element ) {
			if ( element.dataset && element.dataset.src ) {
				element.src = element.dataset.src;
				swapped = true;
			}
		} );

		if ( ! swapped ) {
			return;
		}

		// Re-read the freshly swapped sources.
		video.load();

		if ( video.hasAttribute( 'autoplay' ) && ! reduceMotion ) {
			var attempt = video.play();
			if ( attempt && typeof attempt.catch === 'function' ) {
				attempt.catch( function () {} ); // Autoplay may be blocked by policy.
			}
		}
	}

	/**
	 * Initialize lazy loading for all videos that opt in via data-src.
	 */
	function init() {
		var videos = Array.prototype.slice.call(
			document.querySelectorAll( 'video[data-src], video:has(source[data-src])' )
		);

		// Fallback for browsers without :has() support — collect via data-src sources.
		if ( ! videos.length ) {
			var sources = document.querySelectorAll( 'source[data-src]' );
			var seen = [];
			Array.prototype.forEach.call( sources, function ( source ) {
				var video = source.closest( 'video' );
				if ( video && seen.indexOf( video ) === -1 ) {
					seen.push( video );
				}
			} );
			videos = seen;
		}

		if ( ! videos.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			videos.forEach( activateVideo );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						activateVideo( entry.target );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '200px 0px' }
		);

		videos.forEach( function ( video ) {
			observer.observe( video );
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
