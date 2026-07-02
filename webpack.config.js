/**
 * Extends the @wordpress/scripts default config.
 *
 * - Keeps the default per-block handling (auto-detects `src/<block>/block.json`,
 *   builds their editorScript/viewScript, copies block.json + render.php to build/).
 * - Adds the standalone editor plugin entry (Remote Templates) that is not a block.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry() {
		const base =
			typeof defaultConfig.entry === 'function'
				? defaultConfig.entry()
				: defaultConfig.entry;

		return {
			...base,
			'template-reuse': './src/template-reuse/index.js',
			'content-io': './src/content-io/index.js',
		};
	},
};
