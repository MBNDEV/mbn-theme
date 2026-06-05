/**
 * Shared layout attribute definitions for MBN blocks.
 *
 * @package CustomTheme
 */

export const layoutAttributes = {
	marginTop: { type: 'string', default: '' },
	marginRight: { type: 'string', default: '' },
	marginBottom: { type: 'string', default: '' },
	marginLeft: { type: 'string', default: '' },
	paddingTop: { type: 'string', default: '' },
	paddingRight: { type: 'string', default: '' },
	paddingBottom: { type: 'string', default: '' },
	paddingLeft: { type: 'string', default: '' },
	backgroundColor: { type: 'string', default: '' },
	textColor: { type: 'string', default: '' },
	accentColor: { type: 'string', default: '' },
	overlayColor: { type: 'string', default: '' },
	overlayOpacity: { type: 'number', default: 0 },
	backgroundImageId: { type: 'number', default: 0 },
	backgroundImageUrl: { type: 'string', default: '' },
	backgroundVideoId: { type: 'number', default: 0 },
	backgroundVideoUrl: { type: 'string', default: '' },
	customCss: { type: 'string', default: '' },
	blockInstanceId: { type: 'string', default: '' },
};
