/**
 * MBN Theme — Appearance > MBN Theme settings page helpers.
 *
 * - wp-color-picker for the color-scheme repeater (add/remove rows, relabel vars)
 * - live primary/secondary font preview from the font selects
 * - live font-size previews from the size fields
 * - CodeMirror editors for the Custom HTML fields
 *
 * Plain browser JS using the jQuery + wp.codeEditor globals.
 */
( function ( $ ) {
	'use strict';

	var data = window.mbnThemeSettings || {
		families: {},
		fallbackPrimary: '',
		fallbackSecond: '',
		colorVarPrefix: '--mbn-color-scheme-',
		defaultColor: '#2563EB',
		removeLabel: 'Remove',
	};

	/**
	 * Build a CSS font-family value for a selected family slug.
	 *
	 * @param {string} slug     Family slug.
	 * @param {string} fallback Named fallback @font-face family.
	 * @return {string} font-family value.
	 */
	function familyValue( slug, fallback ) {
		var stack = data.families[ slug ] || '';
		var parts = [];
		if ( stack ) {
			parts.push( stack );
		}
		if ( fallback ) {
			parts.push( "'" + fallback + "'" );
		}
		parts.push( 'sans-serif' );
		return parts.join( ', ' );
	}

	/**
	 * Refresh the --mbn-color-scheme-N labels after add/remove/reorder.
	 */
	function relabelColors() {
		$( '#mbn-color-schemes .mbn-color-row' ).each( function ( i ) {
			$( this ).find( '.mbn-color-var' ).text( data.colorVarPrefix + ( i + 1 ) );
		} );
	}

	/**
	 * Initialize a wp-color-picker on a field if not already done.
	 *
	 * @param {jQuery} $field Color text input.
	 */
	function initColorPicker( $field ) {
		if ( $field.data( 'mbnPicker' ) ) {
			return;
		}
		$field.data( 'mbnPicker', true );
		if ( $.fn.wpColorPicker ) {
			$field.wpColorPicker();
		}
	}

	/**
	 * Update the primary/secondary font preview text.
	 */
	function updateFontPreview() {
		var primary = $( '#mbn-font-primary' ).val() || '';
		var secondary = $( '#mbn-font-secondary' ).val() || '';
		var primaryStack = familyValue( primary, data.fallbackPrimary );
		var secondaryStack = familyValue( secondary, data.fallbackSecond );
		$( '.mbn-font-preview-primary' ).css( 'font-family', primaryStack );
		$( '.mbn-font-preview-secondary' ).css( 'font-family', secondaryStack );
		// The font-match overlay's webfont line.
		$( '.mbn-font-match__web[data-preview="primary"]' ).css( 'font-family', primaryStack );
		$( '.mbn-font-match__web[data-preview="secondary"]' ).css( 'font-family', secondaryStack );
	}

	/**
	 * Apply a size field value to its preview element. Empty tablet/mobile fields
	 * fall back to their placeholder (the auto-reduced value).
	 *
	 * @param {HTMLElement} field Size text input.
	 */
	function updateSizePreview( field ) {
		var $field = $( field );
		var target = $field.data( 'preview' );
		if ( target ) {
			var value = $field.val() || $field.attr( 'placeholder' ) || '';
			$( '#' + target ).css( 'font-size', value );
		}
	}

	/**
	 * Apply the chosen font weight to all of a level's previews (desktop/tablet/mobile).
	 *
	 * @param {Element} field The weight <select>.
	 */
	function updateWeightPreview( field ) {
		var $field = $( field );
		var key = ( $field.attr( 'id' ) || '' ).replace( 'mbn-weight-', '' );
		var weight = $field.val();
		[ '', 'tablet-', 'mobile-' ].forEach( function ( prefix ) {
			$( '#mbn-size-preview-' + prefix + key ).css( 'font-weight', weight );
		} );
	}

	/**
	 * Build a fallback @font-face rule from the current metric inputs.
	 *
	 * @param {string} which    'primary' or 'secondary'.
	 * @param {string} faceName Named fallback family.
	 * @return {string} The @font-face CSS.
	 */
	function buildFallbackFace( which, faceName ) {
		if ( ! faceName ) {
			return '';
		}
		var get = function ( field ) {
			return ( $( '#mbn-fallback-' + which + '-' + field ).val() || '' ).trim();
		};
		var rule = "@font-face{font-family:'" + faceName + "';font-style:normal;font-weight:400;";
		var src = get( 'src' );
		var descriptors = {
			'src': src,
			'size-adjust': get( 'size_adjust' ),
			'ascent-override': get( 'ascent_override' ),
			'descent-override': get( 'descent_override' ),
			'line-gap-override': get( 'line_gap_override' ),
		};
		Object.keys( descriptors ).forEach( function ( prop ) {
			if ( descriptors[ prop ] ) {
				rule += prop + ':' + descriptors[ prop ] + ';';
			}
		} );
		return rule + '}';
	}

	/**
	 * Live-rebuild both fallback faces so the font-match preview updates as the
	 * size-adjust / ascent / descent / line-gap values are edited.
	 */
	function updateFallbackFaces() {
		var css =
			buildFallbackFace( 'primary', data.fallbackPrimary ) +
			buildFallbackFace( 'secondary', data.fallbackSecond );
		var style = document.getElementById( 'mbn-fallback-live' );
		if ( ! style ) {
			style = document.createElement( 'style' );
			style.id = 'mbn-fallback-live';
			document.head.appendChild( style );
		}
		style.textContent = css;
	}

	/**
	 * Initialize CodeMirror on the Custom HTML textareas.
	 */
	function initCodeEditors() {
		if ( ! window.wp || ! window.wp.codeEditor ) {
			return;
		}
		$( 'textarea.mbn-code-editor' ).each( function () {
			if ( this.dataset.mbnInit ) {
				return;
			}
			this.dataset.mbnInit = '1';
			var textarea = this;
			var editor = window.wp.codeEditor.initialize( textarea );
			if ( editor && editor.codemirror ) {
				editor.codemirror.on( 'change', function ( cm ) {
					textarea.value = cm.getValue();
				} );
			}
		} );
	}

	$( function () {
		// Color schemes: pickers + add/remove.
		$( '#mbn-color-schemes .mbn-color-field' ).each( function () {
			initColorPicker( $( this ) );
		} );

		$( '.mbn-add-color' ).on( 'click', function () {
			var $row = $(
				'<div class="mbn-color-row" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">' +
					'<code class="mbn-color-var" style="min-width:190px;display:inline-block;"></code>' +
					'<input type="text" class="mbn-color-field" name="mbn_settings[color_schemes][]" value="' + data.defaultColor + '" data-default-color="' + data.defaultColor + '" />' +
					'<button type="button" class="button mbn-remove-color">' + data.removeLabel + '</button>' +
				'</div>'
			);
			$( '#mbn-color-schemes' ).append( $row );
			initColorPicker( $row.find( '.mbn-color-field' ) );
			relabelColors();
		} );

		$( '#mbn-color-schemes' ).on( 'click', '.mbn-remove-color', function () {
			$( this ).closest( '.mbn-color-row' ).find( '.mbn-color-field' ).wpColorPicker( 'close' );
			$( this ).closest( '.mbn-color-row' ).remove();
			relabelColors();
		} );
		relabelColors();

		// Font preview.
		$( '.mbn-font-select' ).on( 'change', updateFontPreview );
		updateFontPreview();

		// Size previews (desktop / tablet / mobile).
		$( '.mbn-size-field, .mbn-size-field-tablet, .mbn-size-field-mobile' ).on( 'input change', function () {
			updateSizePreview( this );
		} ).each( function () {
			updateSizePreview( this );
		} );

		// Weight previews.
		$( '.mbn-weight-field' ).on( 'change', function () {
			updateWeightPreview( this );
		} );

		// Fallback font metrics: live-rebuild the @font-face so the overlay preview
		// updates as size-adjust / ascent / descent / line-gap are edited.
		$( document ).on( 'input change', '[id^="mbn-fallback-"]', updateFallbackFaces );
		updateFallbackFaces();

		// Code editors.
		initCodeEditors();
	} );
} )( window.jQuery );
