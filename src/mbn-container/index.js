import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import Edit from './edit';
import { InnerContentSave } from '../shared/save';

registerBlockType( metadata.name, { edit: Edit, save: InnerContentSave } );
