/**
 * Import / Export — editor sidebar (next to Remote Templates) to export the
 * CURRENT post as JSON or import a JSON export INTO the current post. Import
 * always targets the post being edited; the editor reloads afterwards so the
 * imported content is shown.
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { Button, FormFileUpload, Notice, PanelBody, Spinner } from '@wordpress/components';
import { Fragment, useEffect, useState } from '@wordpress/element';
import { useSelect, dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { __, sprintf } from '@wordpress/i18n';

const RELOAD_FLAG = 'mbnContentIoImported';

function download( filename, text ) {
	const blob = new window.Blob( [ text ], { type: 'application/json' } );
	const url = window.URL.createObjectURL( blob );
	const a = document.createElement( 'a' );
	a.href = url;
	a.download = filename;
	document.body.appendChild( a );
	a.click();
	document.body.removeChild( a );
	window.URL.revokeObjectURL( url );
}

function ContentIoSidebar() {
	const { postId, postType, postSlug } = useSelect( ( select ) => {
		const editor = select( 'core/editor' );
		return {
			postId: editor.getCurrentPostId(),
			postType: editor.getCurrentPostType(),
			postSlug: editor.getEditedPostAttribute( 'slug' ),
		};
	}, [] );

	const [ busy, setBusy ] = useState( false );
	const [ error, setError ] = useState( '' );

	useEffect( () => {
		if ( window.sessionStorage.getItem( RELOAD_FLAG ) ) {
			window.sessionStorage.removeItem( RELOAD_FLAG );
			dispatch( 'core/notices' ).createSuccessNotice(
				__( 'Import finished: this post was updated from the JSON file.', 'mbn-theme' ),
				{ type: 'snackbar' }
			);
		}
	}, [] );

	const onExport = async () => {
		setBusy( true );
		setError( '' );
		try {
			const data = await apiFetch( { path: `/mbn/v1/posts/${ postId }` } );
			download(
				`mbn-export-${ postSlug || 'post-' + postId }.json`,
				JSON.stringify( data, null, 2 )
			);
		} catch ( e ) {
			setError( e.message || __( 'Export failed.', 'mbn-theme' ) );
		}
		setBusy( false );
	};

	const onImport = async ( event ) => {
		const file = event.target.files && event.target.files[ 0 ];
		if ( ! file ) {
			return;
		}
		setError( '' );

		let payload;
		try {
			payload = JSON.parse( await file.text() );
		} catch ( e ) {
			setError( __( 'The selected file is not valid JSON.', 'mbn-theme' ) );
			return;
		}
		if ( Array.isArray( payload ) ) {
			payload = payload[ 0 ];
		}
		if ( ! payload || typeof payload !== 'object' ) {
			setError( __( 'The selected file is not a post export.', 'mbn-theme' ) );
			return;
		}

		/* eslint-disable no-alert */
		if (
			! window.confirm(
				sprintf(
					/* translators: %d: post ID. */
					__( 'Import will overwrite THIS post’s (#%d) title, content and thumbnail. Continue?', 'mbn-theme' ),
					postId
				)
			)
		) {
			return;
		}
		/* eslint-enable no-alert */

		// Import INTO the current post: force the target id + keep this post type.
		payload.post_id = postId;
		payload.post_type = postType;

		setBusy( true );
		try {
			await apiFetch( { path: '/mbn/v1/posts', method: 'POST', data: payload } );
			window.sessionStorage.setItem( RELOAD_FLAG, '1' );
			window.location.reload();
		} catch ( e ) {
			setError( e.message || __( 'Import failed.', 'mbn-theme' ) );
			setBusy( false );
		}
	};

	return (
		<Fragment>
			<PanelBody title={ __( 'Export', 'mbn-theme' ) } initialOpen>
				<p className="description">
					{ __( 'Download this post (content + media) as a JSON file.', 'mbn-theme' ) }
				</p>
				<Button variant="secondary" onClick={ onExport } disabled={ busy }>
					{ __( 'Export this post', 'mbn-theme' ) }
				</Button>
			</PanelBody>
			<PanelBody title={ __( 'Import', 'mbn-theme' ) } initialOpen>
				<p className="description">
					{ __( 'Apply a JSON export to THIS post — its title, content and media are replaced.', 'mbn-theme' ) }
				</p>
				{ error && (
					<Notice status="error" isDismissible={ false }>
						{ error }
					</Notice>
				) }
				<FormFileUpload
					accept="application/json,.json"
					variant="primary"
					disabled={ busy }
					onChange={ onImport }
				>
					{ __( 'Import into this post', 'mbn-theme' ) }
				</FormFileUpload>
				{ busy && <Spinner /> }
			</PanelBody>
		</Fragment>
	);
}

registerPlugin( 'mbn-content-io', {
	icon: 'database-import',
	render: () => (
		<Fragment>
			<PluginSidebarMoreMenuItem target="mbn-content-io" icon="database-import">
				{ __( 'Import / Export (JSON)', 'mbn-theme' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="mbn-content-io"
				title={ __( 'Import / Export (JSON)', 'mbn-theme' ) }
				icon="database-import"
			>
				<ContentIoSidebar />
			</PluginSidebar>
		</Fragment>
	),
} );
