/**
 * Shared repeater for blocks with an `items` array attribute (accordion, tabs,
 * features, services). Supports text, textarea and media (image/icon) fields so
 * every item is editable in the editor like a visual builder.
 */
import { Fragment } from '@wordpress/element';
import { PanelBody, TextControl, TextareaControl, BaseControl, Button } from '@wordpress/components';
import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { MediaPicker } from './media-controls';

/**
 * Render a single field control for an item.
 */
function ItemField( { field, value, onChange, sizeValue, onSizeChange } ) {
	if ( field.type === 'media' ) {
		return (
			<MediaPicker
				label={ field.label }
				value={ value }
				onChange={ onChange }
				allowedTypes={ field.allowedTypes || [ 'image' ] }
				sizeValue={ field.withSize ? sizeValue : undefined }
				onSizeChange={ field.withSize ? onSizeChange : undefined }
			/>
		);
	}

	if ( field.type === 'richtext' ) {
		return (
			<BaseControl __nextHasNoMarginBottom label={ field.label }>
				<RichText
					className="mbn-richtext-field"
					tagName="div"
					multiline="p"
					value={ value || '' }
					onChange={ onChange }
					placeholder={ field.label }
				/>
			</BaseControl>
		);
	}

	const Control = field.type === 'textarea' ? TextareaControl : TextControl;
	return (
		<Control
			label={ field.label }
			value={ value || '' }
			rows={ field.type === 'textarea' ? 4 : undefined }
			onChange={ onChange }
		/>
	);
}

export function ItemsRepeater( { attributes, setAttributes, fields } ) {
	const items = attributes.items || [];
	const setItems = ( next ) => setAttributes( { items: next } );

	const updateField = ( idx, key, value ) =>
		setItems(
			items.map( ( item, i ) =>
				i === idx ? { ...item, [ key ]: value } : item
			)
		);

	const addItem = () => {
		const blank = {};
		fields.forEach( ( field ) => ( blank[ field.key ] = field.type === 'media' ? 0 : '' ) );
		setItems( [ ...items, blank ] );
	};

	const removeItem = ( idx ) => setItems( items.filter( ( _i, i ) => i !== idx ) );

	const moveItem = ( idx, dir ) => {
		const target = idx + dir;
		if ( target < 0 || target >= items.length ) {
			return;
		}
		const next = items.slice();
		[ next[ idx ], next[ target ] ] = [ next[ target ], next[ idx ] ];
		setItems( next );
	};

	return (
		<Fragment>
			{ items.map( ( item, idx ) => (
				<PanelBody
					key={ idx }
					title={ __( 'Item', 'mbn-theme' ) + ' ' + ( idx + 1 ) }
					initialOpen={ false }
				>
					{ fields.map( ( field ) => (
						<ItemField
							key={ field.key }
							field={ field }
							value={ item[ field.key ] }
							onChange={ ( value ) => updateField( idx, field.key, value ) }
							sizeValue={ item[ `${ field.key }Size` ] }
							onSizeChange={ ( size ) => updateField( idx, `${ field.key }Size`, size ) }
						/>
					) ) }
					<div className="mbn-item-actions" style={ { display: 'flex', gap: '4px' } }>
						<Button variant="tertiary" onClick={ () => moveItem( idx, -1 ) }>
							↑
						</Button>
						<Button variant="tertiary" onClick={ () => moveItem( idx, 1 ) }>
							↓
						</Button>
						<Button
							variant="link"
							isDestructive
							onClick={ () => removeItem( idx ) }
						>
							{ __( 'Remove', 'mbn-theme' ) }
						</Button>
					</div>
				</PanelBody>
			) ) }
			<Button variant="primary" onClick={ addItem }>
				{ __( 'Add item', 'mbn-theme' ) }
			</Button>
		</Fragment>
	);
}
