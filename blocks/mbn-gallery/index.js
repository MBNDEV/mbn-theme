/**
 * MBN Gallery block registration.
 *
 * @package CustomTheme
 */

import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import metadata from './block.json';
import '../shared/editor-layout.css';

registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
