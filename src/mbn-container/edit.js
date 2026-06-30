import { useEffect } from '@wordpress/element';
import { LayoutShellEdit } from '../shared/controls';
import { WRAPPER } from '../shared/layout';

export default function Edit( props ) {
	useEffect( () => {
		if ( props.attributes.align !== 'full' ) {
			props.setAttributes( { align: 'full' } );
		}
	}, [ props.attributes.align, props.setAttributes ] );

	return (
		<LayoutShellEdit
			{ ...props }
			blockSlug="mbn-container"
			wrapperClassName={ WRAPPER }
			contentClassName="relative z-10 container mx-auto"
		/>
	);
}
