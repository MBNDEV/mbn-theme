/**
 * Remote Templates — editor sidebar to sign in to a remote site (JWT, held in
 * memory only) and import its block templates. Nothing is persisted.
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginSidebar,
	PluginSidebarMoreMenuItem,
	PluginPostStatusInfo,
} from '@wordpress/edit-post';
import { BlockPreview } from '@wordpress/block-editor';
import {
	Button,
	Modal,
	Notice,
	PanelBody,
	Spinner,
	TextControl,
} from '@wordpress/components';
import { Fragment, useState } from '@wordpress/element';
import { parse } from '@wordpress/blocks';
import { dispatch } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

const config = window.customThemeTemplateReuse || {};

if ( config.nonce && apiFetch.createNonceMiddleware ) {
	apiFetch.use( apiFetch.createNonceMiddleware( config.nonce ) );
}

const post = ( path, data ) => apiFetch( { path, method: 'POST', data } );

function formatExpiry( exp ) {
	if ( ! exp ) {
		return '';
	}
	try {
		return new Date( exp * 1000 ).toLocaleString();
	} catch ( e ) {
		return '';
	}
}

function TemplatePreviewModal( { template, onClose } ) {
	const blocks = parse( template.content || '' );

	return (
		<Modal
			title=" "
			onRequestClose={ onClose }
			className="mbn-template-reuse-preview-modal is-fullscreen"
			shouldCloseOnClickOutside={ false }
			shouldCloseOnEsc
			isDismissible={ false }
			size="fill"
		>
			{ blocks.length ? (
				<div className="mbn-template-reuse-preview flex-1 overflow-y-auto bg-white p-4">
					<BlockPreview blocks={ blocks } viewportWidth={ window.innerWidth || 1200 } />
				</div>
			) : (
				<Notice status="warning" isDismissible={ false }>
					{ __( 'This template has no block content to preview.', 'mbn-theme' ) }
				</Notice>
			) }
			<div className="mbn-template-reuse-preview-actions flex justify-end gap-2 border-t border-gray-200 bg-white p-4">
				<Button variant="secondary" onClick={ onClose }>
					{ __( 'Close', 'mbn-theme' ) }
				</Button>
			</div>
		</Modal>
	);
}

function SignInForm( { onSignIn, isBusy } ) {
	const [ homeUrl, setHomeUrl ] = useState( '' );
	const [ username, setUsername ] = useState( '' );
	const [ password, setPassword ] = useState( '' );

	return (
		<div className="mbn-template-reuse-signin flex flex-col gap-3 p-2">
			<p className="text-sm opacity-70 m-0">
				{ __(
					'Sign in to a remote site to browse and import its block templates. Credentials are exchanged for a token and never stored.',
					'mbn-theme'
				) }
			</p>
			<TextControl
				label={ __( 'Remote site URL', 'mbn-theme' ) }
				value={ homeUrl }
				type="url"
				placeholder="https://example.com"
				onChange={ setHomeUrl }
			/>
			<TextControl
				label={ __( 'Username', 'mbn-theme' ) }
				value={ username }
				onChange={ setUsername }
			/>
			<TextControl
				label={ __( 'Password', 'mbn-theme' ) }
				value={ password }
				type="password"
				onChange={ setPassword }
			/>
			<Button
				variant="primary"
				isBusy={ isBusy }
				disabled={ isBusy || ! homeUrl || ! username || ! password }
				onClick={ () => onSignIn( homeUrl.trim(), username.trim(), password ) }
			>
				{ isBusy ? __( 'Signing in…', 'mbn-theme' ) : __( 'Sign in', 'mbn-theme' ) }
			</Button>
		</div>
	);
}

function RemoteTemplatesPanel() {
	const [ session, setSession ] = useState( null );
	const [ templates, setTemplates ] = useState( [] );
	const [ isBusy, setIsBusy ] = useState( false );
	const [ errorMessage, setErrorMessage ] = useState( '' );
	const [ previewTemplate, setPreviewTemplate ] = useState( null );

	function loadTemplates( activeSession ) {
		setIsBusy( true );
		post( '/mbn-theme/v1/template-reuse/templates', {
			home_url: activeSession.home_url,
			token: activeSession.token,
		} )
			.then( ( response ) =>
				setTemplates( Array.isArray( response.templates ) ? response.templates : [] )
			)
			.catch( ( error ) =>
				setErrorMessage(
					error.message || __( 'Unable to load remote templates.', 'mbn-theme' )
				)
			)
			.finally( () => setIsBusy( false ) );
	}

	function signIn( homeUrl, username, password ) {
		setIsBusy( true );
		setErrorMessage( '' );
		post( '/mbn-theme/v1/template-reuse/signin', {
			home_url: homeUrl,
			username,
			password,
		} )
			.then( ( response ) => {
				const activeSession = {
					token: response.token,
					home_url: response.home_url || homeUrl,
					user: response.user || username,
					exp: response.exp || 0,
				};
				setSession( activeSession );
				loadTemplates( activeSession );
			} )
			.catch( ( error ) => {
				setErrorMessage( error.message || __( 'Sign-in failed.', 'mbn-theme' ) );
				setIsBusy( false );
			} );
	}

	function signOut() {
		setSession( null );
		setTemplates( [] );
		setErrorMessage( '' );
	}

	function appendTemplate( template ) {
		const blocks = parse( template.content || '' );
		if ( ! blocks.length ) {
			setErrorMessage(
				__( 'Selected template has no block content to append.', 'mbn-theme' )
			);
			return;
		}
		dispatch( 'core/block-editor' ).insertBlocks( blocks );
		setErrorMessage( '' );
	}

	const errorNotice = errorMessage ? (
		<Notice status="error" isDismissible onRemove={ () => setErrorMessage( '' ) }>
			{ errorMessage }
		</Notice>
	) : null;

	if ( ! session ) {
		return (
			<Fragment>
				{ errorNotice }
				<SignInForm onSignIn={ signIn } isBusy={ isBusy } />
			</Fragment>
		);
	}

	const expiry = formatExpiry( session.exp );

	return (
		<Fragment>
			<div className="mbn-template-reuse-session flex items-center justify-between gap-2 border-b border-gray-200 p-2">
				<span className="text-sm">
					<strong>{ session.user }</strong>
					{ expiry && (
						<span className="block text-xs opacity-60">
							{ __( 'Expires', 'mbn-theme' ) + ' ' + expiry }
						</span>
					) }
				</span>
				<Button variant="tertiary" isSmall onClick={ signOut }>
					{ __( 'Sign out', 'mbn-theme' ) }
				</Button>
			</div>
			{ errorNotice }
			{ isBusy ? (
				<div className="mbn-template-reuse-loading flex items-center gap-2 p-4">
					<Spinner />
					<p>{ __( 'Loading templates…', 'mbn-theme' ) }</p>
				</div>
			) : ! templates.length ? (
				<Notice status="warning" isDismissible={ false }>
					{ __( 'No block templates were returned for this site.', 'mbn-theme' ) }
				</Notice>
			) : (
				<div className="mbn-template-reuse-templates flex flex-col gap-2">
					{ templates.map( ( template ) => (
						<PanelBody
							key={ template.id || template.slug }
							title={ template.title || template.slug }
							initialOpen={ false }
						>
							<div className="mbn-template-reuse-actions flex flex-wrap gap-2">
								<Button variant="secondary" onClick={ () => setPreviewTemplate( template ) }>
									{ __( 'Preview', 'mbn-theme' ) }
								</Button>
								<Button variant="primary" onClick={ () => appendTemplate( template ) }>
									{ __( 'Append Template', 'mbn-theme' ) }
								</Button>
							</div>
						</PanelBody>
					) ) }
				</div>
			) }
			{ previewTemplate && (
				<TemplatePreviewModal
					template={ previewTemplate }
					onClose={ () => setPreviewTemplate( null ) }
				/>
			) }
		</Fragment>
	);
}

function RemoteTemplatesPlugin() {
	const openSidebar = () =>
		dispatch( 'core/edit-post' ).openGeneralSidebar(
			'custom-theme-template-reuse/custom-theme-template-reuse-sidebar'
		);

	return (
		<Fragment>
			{ PluginPostStatusInfo && (
				<PluginPostStatusInfo>
					<Button variant="secondary" onClick={ openSidebar }>
						{ __( 'Remote Templates', 'mbn-theme' ) }
					</Button>
				</PluginPostStatusInfo>
			) }
			<PluginSidebarMoreMenuItem target="custom-theme-template-reuse-sidebar">
				{ __( 'Remote Templates', 'mbn-theme' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="custom-theme-template-reuse-sidebar"
				title={ __( 'Remote Templates', 'mbn-theme' ) }
			>
				<RemoteTemplatesPanel />
			</PluginSidebar>
		</Fragment>
	);
}

registerPlugin( 'custom-theme-template-reuse', { render: RemoteTemplatesPlugin } );
