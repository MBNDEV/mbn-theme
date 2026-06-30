/**
 * Shared "Scroll animation" control. Sets the block's `animation` attribute,
 * which render.php maps to data-mbn-reveal on the items container so the content
 * reveals in order as it scrolls into view. Default none.
 */
import { SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export function AnimationControl( { value, onChange } ) {
	return (
		<SelectControl
			label={ __( 'Scroll animation', 'mbn-theme' ) }
			help={ __( 'Reveals the content in order as the section scrolls into view.', 'mbn-theme' ) }
			value={ value || 'none' }
			options={ [
				{ label: __( 'None', 'mbn-theme' ), value: 'none' },
				{ label: __( 'Fade', 'mbn-theme' ), value: 'fade' },
				{ label: __( 'Slide up', 'mbn-theme' ), value: 'slide-up' },
				{ label: __( 'Slide down', 'mbn-theme' ), value: 'slide-down' },
				{ label: __( 'Slide left', 'mbn-theme' ), value: 'slide-left' },
				{ label: __( 'Slide right', 'mbn-theme' ), value: 'slide-right' },
				{ label: __( 'Zoom', 'mbn-theme' ), value: 'zoom' },
			] }
			onChange={ onChange }
		/>
	);
}
