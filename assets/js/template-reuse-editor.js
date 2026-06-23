( function ( wp ) {
	if (
		! wp ||
		! wp.apiFetch ||
		! wp.blockEditor ||
		! wp.blocks ||
		! wp.components ||
		! wp.data ||
		! wp.editPost ||
		! wp.element ||
		! wp.i18n ||
		! wp.plugins
	) {
		return;
	}

	var el = wp.element.createElement;
	var useEffect = wp.element.useEffect;
	var useState = wp.element.useState;
	var __ = wp.i18n.__;
	var apiFetch = wp.apiFetch;
	var BlockPreview = wp.blockEditor.BlockPreview;
	var Button = wp.components.Button;
	var Modal = wp.components.Modal;
	var Notice = wp.components.Notice;
	var PanelBody = wp.components.PanelBody;
	var Spinner = wp.components.Spinner;
	var TabPanel = wp.components.TabPanel;
	var PluginPostStatusInfo = wp.editPost.PluginPostStatusInfo;
	var PluginSidebar = wp.editPost.PluginSidebar;
	var PluginSidebarMoreMenuItem = wp.editPost.PluginSidebarMoreMenuItem;
	var registerPlugin = wp.plugins.registerPlugin;
	var parse = wp.blocks.parse;
	var dispatch = wp.data.dispatch;
	var config = window.customThemeTemplateReuse || {};

	if ( config.nonce && apiFetch.createNonceMiddleware ) {
		apiFetch.use( apiFetch.createNonceMiddleware( config.nonce ) );
	}

	function request( path ) {
		return apiFetch( { path: path } );
	}

	function TemplatePreviewModal( props ) {
		var template = props.template;
		var onClose = props.onClose;
		var blocks = parse( template.content || '' );

		return el(
			Modal,
			{
				title: ' ',
				onRequestClose: onClose,
				className: 'custom-theme-template-reuse__preview-modal is-fullscreen',
				shouldCloseOnClickOutside: false,
				shouldCloseOnEsc: true,
				isDismissible: false,
				size: 'fill',
			},
			blocks.length
				? el(
						'div',
						{ className: 'custom-theme-template-reuse__preview' },
						el( BlockPreview, {
							blocks: blocks,
							viewportWidth: window.innerWidth || 1200,
						} )
					)
				: el(
						Notice,
						{ status: 'warning', isDismissible: false },
						__( 'This template has no block content to preview.', 'mbn-theme' )
					),
			el(
				'div',
				{ className: 'custom-theme-template-reuse__preview-actions' },
				el(
					Button,
					{
						variant: 'secondary',
						onClick: onClose,
					},
					__( 'Close', 'mbn-theme' )
				)
			)
		);
	}

	function RemoteTemplatesPanel() {
		var _useState = useState( [] );
		var sites = _useState[ 0 ];
		var setSites = _useState[ 1 ];
		var _useState2 = useState( null );
		var selectedSite = _useState2[ 0 ];
		var setSelectedSite = _useState2[ 1 ];
		var _useState3 = useState( {} );
		var templatesBySite = _useState3[ 0 ];
		var setTemplatesBySite = _useState3[ 1 ];
		var _useState4 = useState( true );
		var isLoadingSites = _useState4[ 0 ];
		var setIsLoadingSites = _useState4[ 1 ];
		var _useState5 = useState( false );
		var isLoadingTemplates = _useState5[ 0 ];
		var setIsLoadingTemplates = _useState5[ 1 ];
		var _useState6 = useState( '' );
		var errorMessage = _useState6[ 0 ];
		var setErrorMessage = _useState6[ 1 ];
		var _useState7 = useState( null );
		var previewTemplate = _useState7[ 0 ];
		var setPreviewTemplate = _useState7[ 1 ];

		useEffect( function () {
			var isMounted = true;

			setIsLoadingSites( true );
			request( '/mbn-theme/v1/template-reuse/sites' )
				.then( function ( response ) {
					if ( ! isMounted ) {
						return;
					}

					var nextSites = Array.isArray( response.sites ) ? response.sites : [];
					setSites( nextSites );
					setSelectedSite( nextSites.length ? String( nextSites[ 0 ].index ) : null );
					setErrorMessage( '' );
				} )
				.catch( function ( error ) {
					if ( isMounted ) {
						setErrorMessage( error.message || __( 'Unable to load remote template sites.', 'mbn-theme' ) );
					}
				} )
				.finally( function () {
					if ( isMounted ) {
						setIsLoadingSites( false );
					}
				} );

			return function () {
				isMounted = false;
			};
		}, [] );

		useEffect(
			function () {
				if ( null === selectedSite || templatesBySite[ selectedSite ] ) {
					return;
				}

				var isMounted = true;
				setIsLoadingTemplates( true );
				request( '/mbn-theme/v1/template-reuse/sites/' + selectedSite + '/templates' )
					.then( function ( response ) {
						if ( ! isMounted ) {
							return;
						}

						setTemplatesBySite( function ( currentTemplates ) {
							return Object.assign( {}, currentTemplates, {
								[ selectedSite ]: Array.isArray( response.templates ) ? response.templates : [],
							} );
						} );
						setErrorMessage( '' );
					} )
					.catch( function ( error ) {
						if ( isMounted ) {
							setErrorMessage( error.message || __( 'Unable to load remote templates.', 'mbn-theme' ) );
						}
					} )
					.finally( function () {
						if ( isMounted ) {
							setIsLoadingTemplates( false );
						}
					} );

				return function () {
					isMounted = false;
				};
			},
			[ selectedSite, templatesBySite ]
		);

		function appendTemplate( template ) {
			var blocks = parse( template.content || '' );

			if ( ! blocks.length ) {
				setErrorMessage( __( 'Selected template has no block content to append.', 'mbn-theme' ) );
				return;
			}

			dispatch( 'core/block-editor' ).insertBlocks( blocks );
			setErrorMessage( '' );
		}

		if ( isLoadingSites ) {
			return el(
				'div',
				{ className: 'custom-theme-template-reuse__loading' },
				el( Spinner ),
				el( 'p', null, __( 'Loading remote template sites...', 'mbn-theme' ) )
			);
		}

		if ( ! sites.length ) {
			return el(
				Notice,
				{ status: 'info', isDismissible: false },
				__( 'No remote template sites are configured in Appearance > Theme Options.', 'mbn-theme' )
			);
		}

		return el(
			wp.element.Fragment,
			null,
			el(
				'div',
				{ className: 'custom-theme-template-reuse' },
				errorMessage
					? el(
							Notice,
							{
								status: 'error',
								isDismissible: true,
								onRemove: function () {
									setErrorMessage( '' );
								},
							},
							errorMessage
						)
					: null,
				el( TabPanel, {
					className: 'custom-theme-template-reuse__tabs',
					activeClass: 'is-active',
					tabs: sites.map( function ( site ) {
						return {
							name: String( site.index ),
							title: site.site_name,
							className: 'custom-theme-template-reuse__tab',
						};
					} ),
					onSelect: function ( tabName ) {
						setSelectedSite( tabName );
					},
				}, function ( tab ) {
					var templates = templatesBySite[ tab.name ] || [];

					if ( isLoadingTemplates && selectedSite === tab.name ) {
						return el(
							'div',
							{ className: 'custom-theme-template-reuse__loading' },
							el( Spinner ),
							el( 'p', null, __( 'Loading templates...', 'mbn-theme' ) )
						);
					}

					if ( ! templates.length ) {
						return el(
							Notice,
							{ status: 'warning', isDismissible: false },
							__( 'No block templates were returned for this site.', 'mbn-theme' )
						);
					}

					return el(
						'div',
						{ className: 'custom-theme-template-reuse__templates' },
						templates.map( function ( template ) {
							return el(
								PanelBody,
								{
									key: template.id || template.slug,
									title: template.title || template.slug,
									initialOpen: false,
								},
								el(
									'div',
									{
										className: 'custom-theme-template-reuse__actions',
										style: {
											display: 'flex',
											flexWrap: 'wrap',
											gap: '8px',
										},
									},
									el(
										Button,
										{
											variant: 'secondary',
											onClick: function () {
												setPreviewTemplate( template );
											},
										},
										__( 'Preview', 'mbn-theme' )
									),
									el(
										Button,
										{
											variant: 'primary',
											onClick: function () {
												appendTemplate( template );
											},
										},
										__( 'Append Template', 'mbn-theme' )
									)
								)
							);
						} )
					);
				} )
			),
			previewTemplate
				? el( TemplatePreviewModal, {
						template: previewTemplate,
						onClose: function () {
							setPreviewTemplate( null );
						},
					} )
				: null
		);
	}

	function RemoteTemplatesPlugin() {
		var openSidebar = function () {
			dispatch( 'core/edit-post' ).openGeneralSidebar( 'custom-theme-template-reuse/custom-theme-template-reuse-sidebar' );
		};

		return el(
			wp.element.Fragment,
			null,
			PluginPostStatusInfo
				? el(
						PluginPostStatusInfo,
						null,
						el(
							Button,
							{
								variant: 'secondary',
								onClick: openSidebar,
							},
							__( 'Remote Templates', 'mbn-theme' )
						)
					)
				: null,
			el(
				PluginSidebarMoreMenuItem,
				{ target: 'custom-theme-template-reuse-sidebar' },
				__( 'Remote Templates', 'mbn-theme' )
			),
			el(
				PluginSidebar,
				{
					name: 'custom-theme-template-reuse-sidebar',
					title: __( 'Remote Templates', 'mbn-theme' ),
				},
				el( RemoteTemplatesPanel )
			)
		);
	}

	registerPlugin( 'custom-theme-template-reuse', {
		render: RemoteTemplatesPlugin,
	} );
}( window.wp ) );
