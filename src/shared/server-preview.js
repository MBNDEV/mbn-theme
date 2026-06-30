/**
 * Editor preview for dynamic blocks — renders the block's PHP output so the
 * editor matches the front end exactly.
 */
import ServerSideRender from '@wordpress/server-side-render';

export function ServerPreview( { block, attributes } ) {
	return <ServerSideRender block={ block } attributes={ attributes } />;
}
