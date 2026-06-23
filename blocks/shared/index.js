/**
 * @mbn/editor — Shared block editor barrel module.
 *
 * Single import path for all WordPress block API primitives used across mbn-theme blocks.
 * Resolved at compile time via webpack alias: '@mbn/editor' → this file.
 *
 * All @wordpress/* packages are externalized by @wordpress/dependency-extraction-webpack-plugin
 * (bundled inside @wordpress/scripts). They load as WP globals at runtime and are listed in
 * each block's index.asset.php so WordPress enqueues them automatically — no bundle duplication.
 *
 * WHY explicit named re-exports instead of `export * from`:
 *   `export * from '@wordpress/block-editor'` and `export * from '@wordpress/blocks'` both export
 *   a symbol named `store`, causing a compile-time name collision. Explicit exports prevent this.
 */

// ─── @wordpress/blocks ───────────────────────────────────────────────────────
export {
	registerBlockType,
	unregisterBlockType,
	createBlock,
	cloneBlock,
	getBlockType,
	getBlockTypes,
	hasBlockSupport,
	isReusableBlock,
	serialize,
	parse,
} from '@wordpress/blocks';

// ─── @wordpress/block-editor ─────────────────────────────────────────────────
export {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
	InspectorAdvancedControls,
	BlockControls,
	BlockAlignmentToolbar,
	RichText,
	MediaUpload,
	MediaUploadCheck,
	MediaPlaceholder,
	ColorPalette,
	ColorPaletteControl,
	PanelColorSettings,
	withColors,
	getColorClassName,
	getColorObjectByAttributeValues,
	URLInput,
	URLInputButton,
	LinkControl,
	InnerBlocks,
	store as blockEditorStore,
} from '@wordpress/block-editor';

// ─── @wordpress/components ───────────────────────────────────────────────────
export {
	PanelBody,
	PanelRow,
	TextControl,
	TextareaControl,
	SelectControl,
	RadioControl,
	CheckboxControl,
	ToggleControl,
	RangeControl,
	Button,
	ButtonGroup,
	Placeholder,
	Spinner,
	Notice,
	Modal,
	Tooltip,
	Popover,
	Dropdown,
	DropdownMenu,
	MenuItem,
	MenuGroup,
	Icon,
	Dashicon,
	BaseControl,
	FormTokenField,
	ComboboxControl,
	DateTimePicker,
	Flex,
	FlexBlock,
	FlexItem,
	Card,
	CardBody,
	CardHeader,
	CardFooter,
	Divider,
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';

// ─── @wordpress/element ──────────────────────────────────────────────────────
export {
	Fragment,
	createElement,
	createPortal,
	useState,
	useEffect,
	useRef,
	useCallback,
	useMemo,
	useContext,
	useReducer,
	createContext,
	forwardRef,
	createInterpolateElement,
	cloneElement,
	isValidElement,
	Children,
	Component,
	RawHTML,
	render,
} from '@wordpress/element';

// ─── @wordpress/i18n ─────────────────────────────────────────────────────────
export {
	__,
	_x,
	_n,
	_nx,
	sprintf,
	setLocaleData,
	getLocaleData,
} from '@wordpress/i18n';

// ─── @wordpress/data ─────────────────────────────────────────────────────────
export {
	useSelect,
	useDispatch,
	select,
	dispatch,
	subscribe,
	withSelect,
	withDispatch,
} from '@wordpress/data';

// ─── @wordpress/compose ──────────────────────────────────────────────────────
export {
	compose,
	withState,
	withInstanceId,
	useDebounce,
	useThrottle,
	usePrevious,
	withSafeTimeout,
} from '@wordpress/compose';

// ─── @wordpress/hooks ────────────────────────────────────────────────────────
export {
	addFilter,
	removeFilter,
	addAction,
	removeAction,
	applyFilters,
	doAction,
} from '@wordpress/hooks';

// ─── @wordpress/icons ────────────────────────────────────────────────────────
// Explicit named exports for icons used across mbn-theme blocks.
// Add more here as needed; avoid `export * from '@wordpress/icons'` (large bundle, no tree-shaking).
export {
	check,
	close,
	plus,
	edit,
	trash,
	upload,
	image,
	link,
	linkOff,
	arrowUp,
	arrowDown,
	arrowLeft,
	arrowRight,
	chevronUp,
	chevronDown,
	chevronLeft,
	chevronRight,
	settings,
	more,
	seen,
	unseen,
	starEmpty,
	starFilled,
	warning,
	info,
	help,
	search,
	grid,
	menu,
} from '@wordpress/icons';
