import {
	InspectorControls,
	InspectorAdvancedControls,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	BaseControl,
	Button,
	Icon,
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
	__experimentalBoxControl as BoxControl,
	__experimentalUnitControl as UnitControl,
} from '@wordpress/components';
import { Fragment, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	DndContext,
	KeyboardSensor,
	PointerSensor,
	closestCenter,
	useSensor,
	useSensors,
} from '@dnd-kit/core';
import {
	SortableContext,
	arrayMove,
	sortableKeyboardCoordinates,
	useSortable,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

const ALIGN_OPTIONS = [
	{ label: __( 'Left', 'mbn-theme' ),   value: 'left' },
	{ label: __( 'Center', 'mbn-theme' ), value: 'center' },
	{ label: __( 'Right', 'mbn-theme' ),  value: 'right' },
];

const TITLE_TAG_OPTIONS = [
	{ label: 'h2', value: 'h2' },
	{ label: 'h3', value: 'h3' },
	{ label: 'h4', value: 'h4' },
	{ label: 'h5', value: 'h5' },
	{ label: 'h6', value: 'h6' },
	{ label: 'div', value: 'div' },
];

/* ──────────────────────────────────────────────
   Sortable FAQ item inside the inspector panel
────────────────────────────────────────────── */
function SortableFaqItem( { item, index, updateItem, removeItem, duplicateItem } ) {
	const {
		attributes: dndAttrs,
		listeners,
		setNodeRef,
		transform,
		transition,
		isDragging,
	} = useSortable( { id: item.id } );

	const wrapStyle = {
		transform: CSS.Transform.toString( transform ),
		transition,
		opacity: isDragging ? 0.4 : 1,
		marginBottom: '16px',
		padding: '12px',
		border: '1px solid #ddd',
		borderRadius: '4px',
		background: '#fff',
	};

	return (
		<div ref={ setNodeRef } style={ wrapStyle }>
			<div style={ { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' } }>
				<div style={ { display: 'flex', alignItems: 'center', gap: '8px' } }>
					<div { ...dndAttrs } { ...listeners } style={ { cursor: 'grab', padding: '4px' } }>
						<Icon icon="menu" />
					</div>
					<strong>{ __( 'FAQ', 'mbn-theme' ) } { index + 1 }</strong>
				</div>
				<div style={ { display: 'flex', gap: '6px' } }>
					<Button
						icon="admin-page"
						label={ __( 'Duplicate', 'mbn-theme' ) }
						onClick={ () => duplicateItem( index ) }
						isSmall
					/>
					<Button
						icon="trash"
						label={ __( 'Remove', 'mbn-theme' ) }
						onClick={ () => removeItem( index ) }
						isSmall
						isDestructive
					/>
				</div>
			</div>

			<TextControl
				label={ __( 'Question', 'mbn-theme' ) }
				value={ item.question || '' }
				onChange={ ( value ) => updateItem( index, { question: value } ) }
			/>

			<TextareaControl
				label={ __( 'Answer', 'mbn-theme' ) }
				help={ __( 'Plain text. Rich HTML is supported on the frontend via render.php.', 'mbn-theme' ) }
				value={ item.answer || '' }
				onChange={ ( value ) => updateItem( index, { answer: value } ) }
				rows={ 4 }
			/>
		</div>
	);
}

/* ──────────────────────────────────────────────
   Main Edit component
────────────────────────────────────────────── */
export default function Edit( { attributes, setAttributes, clientId } ) {
	useEffect( () => {
		if ( ! attributes.blockId ) {
			setAttributes( { blockId: `mbn-faqs-${ clientId.slice( 0, 8 ) }` } );
		}
	}, [ attributes.blockId, clientId, setAttributes ] );

	/* ── DnD sensors ── */
	const sensors = useSensors(
		useSensor( PointerSensor ),
		useSensor( KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates } )
	);

	/* ── FAQ item helpers ── */
	const updateItem = ( index, updates ) => {
		const next = [ ...attributes.items ];
		next[ index ] = { ...next[ index ], ...updates };
		setAttributes( { items: next } );
	};

	const addItem = () => {
		setAttributes( {
			items: [
				...attributes.items,
				{ id: crypto.randomUUID(), question: '', answer: '' },
			],
		} );
	};

	const removeItem = ( index ) => {
		setAttributes( { items: attributes.items.filter( ( _, i ) => i !== index ) } );
	};

	const duplicateItem = ( index ) => {
		const clone = { ...attributes.items[ index ], id: crypto.randomUUID() };
		const next = [
			...attributes.items.slice( 0, index + 1 ),
			clone,
			...attributes.items.slice( index + 1 ),
		];
		setAttributes( { items: next } );
	};

	const handleDragEnd = ( { active, over } ) => {
		if ( over && active.id !== over.id ) {
			const oldIdx = attributes.items.findIndex( ( it ) => it.id === active.id );
			const newIdx = attributes.items.findIndex( ( it ) => it.id === over.id );
			setAttributes( { items: arrayMove( attributes.items, oldIdx, newIdx ) } );
		}
	};

	/* ── inline editor styles ── */
	const wrapperStyle = {
		textAlign: attributes.alignment || undefined,
		width: attributes.containerWidth || undefined,
		paddingTop: attributes.paddingTop || undefined,
		paddingRight: attributes.paddingRight || undefined,
		paddingBottom: attributes.paddingBottom || undefined,
		paddingLeft: attributes.paddingLeft || undefined,
		marginTop: attributes.marginTop || undefined,
		marginRight: attributes.marginRight || undefined,
		marginBottom: attributes.marginBottom || undefined,
		marginLeft: attributes.marginLeft || undefined,
		backgroundColor: attributes.bgType === 'color' ? attributes.bgColor || undefined : undefined,
		background: attributes.bgType === 'gradient' ? attributes.bgGradient || undefined : undefined,
		backgroundImage: attributes.bgType === 'image' && attributes.bgImageUrl ? `url(${ attributes.bgImageUrl })` : undefined,
		backgroundSize: attributes.bgType === 'image' ? attributes.bgImageSize || undefined : undefined,
		backgroundPosition: attributes.bgType === 'image' ? attributes.bgImagePosition || undefined : undefined,
		borderStyle: attributes.borderStyle !== 'none' ? attributes.borderStyle || undefined : undefined,
		borderWidth: attributes.borderWidth || undefined,
		borderColor: attributes.borderColor || undefined,
		borderRadius: attributes.borderRadius || undefined,
	};

	const questionStyle = {
		maxWidth: attributes.questionMaxWidth || undefined,
		marginTop: attributes.questionMarginTop || undefined,
		marginRight: attributes.questionMarginRight || undefined,
		marginBottom: attributes.questionMarginBottom || undefined,
		marginLeft: attributes.questionMarginLeft || undefined,
		paddingTop: attributes.questionPaddingTop || undefined,
		paddingRight: attributes.questionPaddingRight || undefined,
		paddingBottom: attributes.questionPaddingBottom || undefined,
		paddingLeft: attributes.questionPaddingLeft || undefined,
		fontFamily: attributes.questionFontFamily || undefined,
		fontSize: attributes.questionFontSize || undefined,
		fontWeight: attributes.questionFontWeight || undefined,
		lineHeight: attributes.questionLineHeight || undefined,
		letterSpacing: attributes.questionLetterSpacing || undefined,
		color: attributes.questionColor || undefined,
		textAlign: attributes.questionAlign || undefined,
	};

	const answerStyle = {
		maxWidth: attributes.answerMaxWidth || undefined,
		marginTop: attributes.answerMarginTop || undefined,
		marginRight: attributes.answerMarginRight || undefined,
		marginBottom: attributes.answerMarginBottom || undefined,
		marginLeft: attributes.answerMarginLeft || undefined,
		paddingTop: attributes.answerPaddingTop || undefined,
		paddingRight: attributes.answerPaddingRight || undefined,
		paddingBottom: attributes.answerPaddingBottom || undefined,
		paddingLeft: attributes.answerPaddingLeft || undefined,
		fontFamily: attributes.answerFontFamily || undefined,
		fontSize: attributes.answerFontSize || undefined,
		fontWeight: attributes.answerFontWeight || undefined,
		lineHeight: attributes.answerLineHeight || undefined,
		letterSpacing: attributes.answerLetterSpacing || undefined,
		color: attributes.answerColor || undefined,
		textAlign: attributes.answerAlign || undefined,
	};

	const blockClass = attributes.blockId || '';
	const editorCustomCss =
		attributes.customCss && blockClass
			? attributes.customCss.replaceAll( '{{WRAPPER}}', `.${ blockClass }` )
			: '';

	const blockProps = useBlockProps( {
		className: [
			'mbn-faqs',
			attributes.customClass || '',
			blockClass,
		]
			.filter( Boolean )
			.join( ' ' ),
		style: wrapperStyle,
		id: attributes.customId || undefined,
	} );

	return (
		<Fragment>
			{ /* ── Inspector ── */ }
			<InspectorControls>

				{ /* FAQ Settings */ }
				<PanelBody title={ __( 'FAQ Settings', 'mbn-theme' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Alignment', 'mbn-theme' ) }
						value={ attributes.alignment }
						options={ ALIGN_OPTIONS }
						onChange={ ( alignment ) => setAttributes( { alignment } ) }
					/>
					<SelectControl
						label={ __( 'Background Type', 'mbn-theme' ) }
						value={ attributes.bgType }
						options={ [
							{ label: __( 'Color', 'mbn-theme' ),    value: 'color' },
							{ label: __( 'Gradient', 'mbn-theme' ), value: 'gradient' },
							{ label: __( 'Image', 'mbn-theme' ),    value: 'image' },
						] }
						onChange={ ( bgType ) => setAttributes( { bgType } ) }
					/>
					{ attributes.bgType === 'color' && (
						<TextControl
							label={ __( 'Background Color', 'mbn-theme' ) }
							type="color"
							value={ attributes.bgColor }
							onChange={ ( bgColor ) => setAttributes( { bgColor } ) }
						/>
					) }
					{ attributes.bgType === 'gradient' && (
						<TextControl
							label={ __( 'Background Gradient', 'mbn-theme' ) }
							value={ attributes.bgGradient }
							onChange={ ( bgGradient ) => setAttributes( { bgGradient } ) }
						/>
					) }
					{ attributes.bgType === 'image' && (
						<Fragment>
							<MediaUploadCheck>
								<MediaUpload
									onSelect={ ( media ) => setAttributes( { bgImageUrl: media?.url || '', bgImageId: media?.id || 0 } ) }
									allowedTypes={ [ 'image' ] }
									value={ attributes.bgImageId }
									render={ ( { open } ) => (
										<Button onClick={ open } variant="secondary">
											{ attributes.bgImageUrl
												? __( 'Replace Background', 'mbn-theme' )
												: __( 'Select Background', 'mbn-theme' ) }
										</Button>
									) }
								/>
							</MediaUploadCheck>
							<SelectControl
								label={ __( 'Background Size', 'mbn-theme' ) }
								value={ attributes.bgImageSize }
								options={ [ { label: 'cover', value: 'cover' }, { label: 'contain', value: 'contain' }, { label: 'auto', value: 'auto' } ] }
								onChange={ ( bgImageSize ) => setAttributes( { bgImageSize } ) }
							/>
							<TextControl
								label={ __( 'Background Position', 'mbn-theme' ) }
								value={ attributes.bgImagePosition }
								onChange={ ( bgImagePosition ) => setAttributes( { bgImagePosition } ) }
								placeholder="center center"
							/>
						</Fragment>
					) }
					<SelectControl
						label={ __( 'Border Style', 'mbn-theme' ) }
						value={ attributes.borderStyle }
						options={ [
							{ label: __( 'None', 'mbn-theme' ),   value: 'none' },
							{ label: __( 'Solid', 'mbn-theme' ),  value: 'solid' },
							{ label: __( 'Dashed', 'mbn-theme' ), value: 'dashed' },
							{ label: __( 'Dotted', 'mbn-theme' ), value: 'dotted' },
						] }
						onChange={ ( borderStyle ) => setAttributes( { borderStyle } ) }
					/>
					<UnitControl
						label={ __( 'Border Width', 'mbn-theme' ) }
						value={ attributes.borderWidth }
						onChange={ ( borderWidth ) => setAttributes( { borderWidth } ) }
					/>
					<TextControl
						label={ __( 'Border Color', 'mbn-theme' ) }
						type="color"
						value={ attributes.borderColor }
						onChange={ ( borderColor ) => setAttributes( { borderColor } ) }
					/>
					<UnitControl
						label={ __( 'Border Radius', 'mbn-theme' ) }
						value={ attributes.borderRadius }
						onChange={ ( borderRadius ) => setAttributes( { borderRadius } ) }
					/>
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl
							values={ { top: attributes.paddingTop, right: attributes.paddingRight, bottom: attributes.paddingBottom, left: attributes.paddingLeft } }
							onChange={ ( next ) => setAttributes( { paddingTop: next?.top || '', paddingRight: next?.right || '', paddingBottom: next?.bottom || '', paddingLeft: next?.left || '' } ) }
						/>
					</BaseControl>
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl
							values={ { top: attributes.marginTop, right: attributes.marginRight, bottom: attributes.marginBottom, left: attributes.marginLeft } }
							onChange={ ( next ) => setAttributes( { marginTop: next?.top || '', marginRight: next?.right || '', marginBottom: next?.bottom || '', marginLeft: next?.left || '' } ) }
						/>
					</BaseControl>
					<UnitControl
						label={ __( 'Width', 'mbn-theme' ) }
						value={ attributes.containerWidth }
						onChange={ ( containerWidth ) => setAttributes( { containerWidth } ) }
					/>
				</PanelBody>

				{ /* FAQ Items – repeater */ }
				<PanelBody title={ __( 'FAQ Items', 'mbn-theme' ) } initialOpen={ false }>
					<p style={ { fontSize: '12px', color: '#666', marginBottom: '12px' } }>
						{ __( 'Drag and drop to reorder items.', 'mbn-theme' ) }
					</p>
					<DndContext sensors={ sensors } collisionDetection={ closestCenter } onDragEnd={ handleDragEnd }>
						<SortableContext
							items={ attributes.items.map( ( it ) => it.id ) }
							strategy={ verticalListSortingStrategy }
						>
							{ attributes.items.map( ( item, index ) => (
								<SortableFaqItem
									key={ item.id }
									item={ item }
									index={ index }
									updateItem={ updateItem }
									removeItem={ removeItem }
									duplicateItem={ duplicateItem }
								/>
							) ) }
						</SortableContext>
					</DndContext>
					<Button
						variant="primary"
						onClick={ addItem }
						style={ { marginTop: '12px' } }
					>
						{ __( '+ Add FAQ Item', 'mbn-theme' ) }
					</Button>
				</PanelBody>

				{ /* Question Settings */ }
				<PanelBody title={ __( 'Question Settings', 'mbn-theme' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'Heading Tag', 'mbn-theme' ) }
						value={ attributes.questionTag }
						options={ TITLE_TAG_OPTIONS }
						onChange={ ( questionTag ) => setAttributes( { questionTag } ) }
					/>
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl
							values={ { top: attributes.questionMarginTop, right: attributes.questionMarginRight, bottom: attributes.questionMarginBottom, left: attributes.questionMarginLeft } }
							onChange={ ( next ) => setAttributes( { questionMarginTop: next?.top || '', questionMarginRight: next?.right || '', questionMarginBottom: next?.bottom || '', questionMarginLeft: next?.left || '' } ) }
						/>
					</BaseControl>
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl
							values={ { top: attributes.questionPaddingTop, right: attributes.questionPaddingRight, bottom: attributes.questionPaddingBottom, left: attributes.questionPaddingLeft } }
							onChange={ ( next ) => setAttributes( { questionPaddingTop: next?.top || '', questionPaddingRight: next?.right || '', questionPaddingBottom: next?.bottom || '', questionPaddingLeft: next?.left || '' } ) }
						/>
					</BaseControl>
					<UnitControl
						label={ __( 'Max Width', 'mbn-theme' ) }
						value={ attributes.questionMaxWidth }
						onChange={ ( questionMaxWidth ) => setAttributes( { questionMaxWidth } ) }
					/>
					<TextControl
						label={ __( 'Font Family', 'mbn-theme' ) }
						value={ attributes.questionFontFamily }
						onChange={ ( questionFontFamily ) => setAttributes( { questionFontFamily } ) }
					/>
					<UnitControl
						label={ __( 'Font Size', 'mbn-theme' ) }
						value={ attributes.questionFontSize }
						onChange={ ( questionFontSize ) => setAttributes( { questionFontSize } ) }
					/>
					<TextControl
						label={ __( 'Font Weight', 'mbn-theme' ) }
						value={ attributes.questionFontWeight }
						onChange={ ( questionFontWeight ) => setAttributes( { questionFontWeight } ) }
					/>
					<UnitControl
						label={ __( 'Line Height', 'mbn-theme' ) }
						value={ attributes.questionLineHeight }
						onChange={ ( questionLineHeight ) => setAttributes( { questionLineHeight } ) }
					/>
					<UnitControl
						label={ __( 'Letter Spacing', 'mbn-theme' ) }
						value={ attributes.questionLetterSpacing }
						onChange={ ( questionLetterSpacing ) => setAttributes( { questionLetterSpacing } ) }
					/>
					<TextControl
						label={ __( 'Color', 'mbn-theme' ) }
						type="color"
						value={ attributes.questionColor }
						onChange={ ( questionColor ) => setAttributes( { questionColor } ) }
					/>
					<SelectControl
						label={ __( 'Text Align', 'mbn-theme' ) }
						value={ attributes.questionAlign }
						options={ [ { label: __( 'Default', 'mbn-theme' ), value: '' }, ...ALIGN_OPTIONS ] }
						onChange={ ( questionAlign ) => setAttributes( { questionAlign } ) }
					/>
				</PanelBody>

				{ /* Answer Settings */ }
				<PanelBody title={ __( 'Answer Settings', 'mbn-theme' ) } initialOpen={ false }>
					<BaseControl label={ __( 'Margin', 'mbn-theme' ) }>
						<BoxControl
							values={ { top: attributes.answerMarginTop, right: attributes.answerMarginRight, bottom: attributes.answerMarginBottom, left: attributes.answerMarginLeft } }
							onChange={ ( next ) => setAttributes( { answerMarginTop: next?.top || '', answerMarginRight: next?.right || '', answerMarginBottom: next?.bottom || '', answerMarginLeft: next?.left || '' } ) }
						/>
					</BaseControl>
					<BaseControl label={ __( 'Padding', 'mbn-theme' ) }>
						<BoxControl
							values={ { top: attributes.answerPaddingTop, right: attributes.answerPaddingRight, bottom: attributes.answerPaddingBottom, left: attributes.answerPaddingLeft } }
							onChange={ ( next ) => setAttributes( { answerPaddingTop: next?.top || '', answerPaddingRight: next?.right || '', answerPaddingBottom: next?.bottom || '', answerPaddingLeft: next?.left || '' } ) }
						/>
					</BaseControl>
					<UnitControl
						label={ __( 'Max Width', 'mbn-theme' ) }
						value={ attributes.answerMaxWidth }
						onChange={ ( answerMaxWidth ) => setAttributes( { answerMaxWidth } ) }
					/>
					<TextControl
						label={ __( 'Font Family', 'mbn-theme' ) }
						value={ attributes.answerFontFamily }
						onChange={ ( answerFontFamily ) => setAttributes( { answerFontFamily } ) }
					/>
					<UnitControl
						label={ __( 'Font Size', 'mbn-theme' ) }
						value={ attributes.answerFontSize }
						onChange={ ( answerFontSize ) => setAttributes( { answerFontSize } ) }
					/>
					<TextControl
						label={ __( 'Font Weight', 'mbn-theme' ) }
						value={ attributes.answerFontWeight }
						onChange={ ( answerFontWeight ) => setAttributes( { answerFontWeight } ) }
					/>
					<UnitControl
						label={ __( 'Line Height', 'mbn-theme' ) }
						value={ attributes.answerLineHeight }
						onChange={ ( answerLineHeight ) => setAttributes( { answerLineHeight } ) }
					/>
					<UnitControl
						label={ __( 'Letter Spacing', 'mbn-theme' ) }
						value={ attributes.answerLetterSpacing }
						onChange={ ( answerLetterSpacing ) => setAttributes( { answerLetterSpacing } ) }
					/>
					<TextControl
						label={ __( 'Color', 'mbn-theme' ) }
						type="color"
						value={ attributes.answerColor }
						onChange={ ( answerColor ) => setAttributes( { answerColor } ) }
					/>
					<SelectControl
						label={ __( 'Text Align', 'mbn-theme' ) }
						value={ attributes.answerAlign }
						options={ [ { label: __( 'Default', 'mbn-theme' ), value: '' }, ...ALIGN_OPTIONS ] }
						onChange={ ( answerAlign ) => setAttributes( { answerAlign } ) }
					/>
				</PanelBody>

			</InspectorControls>

			{ /* Advanced panel – ID / Class / Custom CSS */ }
			<InspectorAdvancedControls>
				<TextControl
					label={ __( 'HTML Anchor (ID)', 'mbn-theme' ) }
					value={ attributes.customId }
					onChange={ ( customId ) => setAttributes( { customId } ) }
					help={ __( 'Adds an id="" attribute to the wrapper.', 'mbn-theme' ) }
				/>
				<TextControl
					label={ __( 'Additional CSS Class(es)', 'mbn-theme' ) }
					value={ attributes.customClass }
					onChange={ ( customClass ) => setAttributes( { customClass } ) }
					help={ __( 'Separate multiple classes with spaces.', 'mbn-theme' ) }
				/>
				<TextareaControl
					label={ __( 'Custom CSS', 'mbn-theme' ) }
					value={ attributes.customCss }
					onChange={ ( customCss ) => setAttributes( { customCss } ) }
					rows={ 6 }
					help={ __( 'Use {{WRAPPER}} as a selector for this block.', 'mbn-theme' ) }
				/>
			</InspectorAdvancedControls>

			{ editorCustomCss && <style>{ editorCustomCss }</style> }

			{ /* ── Block preview ── */ }
			<div { ...blockProps }>
				{ attributes.items.length === 0 && (
					<p style={ { padding: '20px', color: '#888', textAlign: 'center' } }>
						{ __( '← Add FAQ items in the sidebar panel.', 'mbn-theme' ) }
					</p>
				) }
				{ attributes.items.map( ( item ) => (
					<div key={ item.id } className="mbn-faqs__item">
						{ item.question && (
							/* eslint-disable-next-line react/no-danger */
							<div
								className="mbn-faqs__question"
								style={ questionStyle }
								dangerouslySetInnerHTML={ { __html: item.question } }
							/>
						) }
						{ item.answer && (
							/* eslint-disable-next-line react/no-danger */
							<div
								className="mbn-faqs__answer"
								style={ answerStyle }
								dangerouslySetInnerHTML={ { __html: item.answer } }
							/>
						) }
					</div>
				) ) }
			</div>
		</Fragment>
	);
}
