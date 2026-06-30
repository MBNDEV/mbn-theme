/**
 * MBN Theme — Header/Footer template meta box: logo media picker.
 *
 * Plain browser JS using the jQuery + wp.media globals.
 */
( function ( $ ) {
	'use strict';

	var data = window.mbnTemplateParts || { title: 'Select logo', button: 'Use as logo' };
	var frame = null;

	$( function () {
		var $wrap = $( '.mbn-template-parts' );
		if ( ! $wrap.length ) {
			return;
		}

		var $id = $wrap.find( '.mbn-logo-id' );
		var $preview = $wrap.find( '.mbn-logo-preview' );
		var $remove = $wrap.find( '.mbn-remove-logo' );

		$wrap.on( 'click', '.mbn-select-logo', function ( e ) {
			e.preventDefault();
			if ( ! window.wp || ! window.wp.media ) {
				return;
			}

			if ( frame ) {
				frame.open();
				return;
			}

			frame = window.wp.media( {
				title: data.title,
				button: { text: data.button },
				library: { type: 'image' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				$id.val( att.id );
				var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
				$preview.html( $( '<img>', { src: url, css: { maxWidth: '100%', height: 'auto' } } ) );
				$remove.show();
			} );

			frame.open();
		} );

		$wrap.on( 'click', '.mbn-remove-logo', function ( e ) {
			e.preventDefault();
			$id.val( '' );
			$preview.empty();
			$remove.hide();
		} );
	} );
} )( window.jQuery );
