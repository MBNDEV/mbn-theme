import { Fragment } from '@wordpress/element';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ServerPreview } from '../shared/server-preview';

export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Logo', 'mbn-theme' ) } initialOpen>
					<TextControl
						label={ __( 'Max width', 'mbn-theme' ) }
						value={ attributes.maxWidth || '' }
						onChange={ ( value ) => setAttributes( { maxWidth: value } ) }
						help={ __( 'Examples: 160px, 12rem.', 'mbn-theme' ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerPreview block="mbn-theme/mbn-logo" attributes={ attributes } />
			</div>
		</Fragment>
	);
}
