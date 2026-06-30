/**
 * Shared "LCP" toggle. When on, the block's background (or largest) image is
 * loaded with high priority (`fetchpriority="high"` + eager) so the Largest
 * Contentful Paint element paints sooner. Use it on the one above-the-fold block
 * that holds the page's biggest image — never more than one per page.
 */
import { ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export function LcpControl( { value, onChange } ) {
	return (
		<ToggleControl
			label={ __( 'Largest Contentful Paint (above the fold)', 'mbn-theme' ) }
			help={ __( 'Loads this block\'s main image with high priority. Set it on the one block holding the page\'s biggest above-the-fold image.', 'mbn-theme' ) }
			checked={ !! value }
			onChange={ onChange }
		/>
	);
}
