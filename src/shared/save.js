/**
 * Save callbacks shared across blocks.
 */
import { InnerBlocks } from '@wordpress/block-editor';

// Layout blocks are server-rendered (render.php) but keep their inner blocks.
export const InnerContentSave = () => <InnerBlocks.Content />;

// Fully dynamic blocks render entirely in PHP.
export const NullSave = () => null;
