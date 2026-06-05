/**
 * MBN Container block registration.
 *
 * @package CustomTheme
 */

import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';
import { createElement } from '@wordpress/element';
import Edit from './edit';
import metadata from './block.json';
import '../shared/editor-layout.css';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => createElement( InnerBlocks.Content ),
} );
