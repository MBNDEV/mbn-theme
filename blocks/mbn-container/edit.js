/**
 * MBN Container block editor component.
 *
 * @package CustomTheme
 */

import { useEffect } from '@wordpress/element';
import LayoutShellEdit from '../shared/LayoutShellEdit';
import { CONTAINER_CONTENT_CLASSES, LAYOUT_WRAPPER_CLASSES } from '../shared/use-layout-styles';

/**
 * @param {Object} props Block editor props.
 * @return {JSX.Element} MBN Container block editor.
 */
export default function Edit( { attributes, setAttributes, ...props } ) {
	useEffect( () => {
		if ( attributes.align !== 'full' ) {
			setAttributes( { align: 'full' } );
		}
	}, [ attributes.align, setAttributes ] );

	return (
		<LayoutShellEdit
			{ ...props }
			attributes={ attributes }
			setAttributes={ setAttributes }
			blockSlug="mbn-container"
			wrapperClassName={ LAYOUT_WRAPPER_CLASSES }
			contentClassName={ CONTAINER_CONTENT_CLASSES }
		/>
	);
}
