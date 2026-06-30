import { Fragment } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ServerPreview } from '../shared/server-preview';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Menu', 'mbn-theme' ) } initialOpen>
					<RangeControl
						label={ __( 'Menu slot', 'mbn-theme' ) }
						value={ attributes.slot }
						onChange={ ( value ) =>
							setAttributes( { slot: value == null ? 1 : value } )
						}
						min={ 1 }
						max={ 10 }
						help={ __(
							'Maps to the Nth menu checked in this template’s Header / Footer Settings.',
							'mbn-theme'
						) }
					/>
					<SelectControl
						label={ __( 'Orientation', 'mbn-theme' ) }
						value={ attributes.orientation || 'horizontal' }
						options={ [
							{ label: __( 'Horizontal', 'mbn-theme' ), value: 'horizontal' },
							{ label: __( 'Vertical', 'mbn-theme' ), value: 'vertical' },
						] }
						onChange={ ( value ) => setAttributes( { orientation: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerPreview block="mbn-theme/mbn-menu" attributes={ attributes } />
			</div>
		</Fragment>
	);
}
