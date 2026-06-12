import { registerBlockType } from '@wordpress/blocks';
import Edit from './edit';
import save from './save';
import metadata from './block.json';
import './style.css';

registerBlockType(metadata.name, {
  edit: Edit,
  save,
});
