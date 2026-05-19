const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const glob = require( 'glob' );
const CopyPlugin = require( 'copy-webpack-plugin' );

// Auto-discover block entry points from child theme
const blockFilter = process.env.BLOCK || '';
const blockEntries = {};
const blockFiles = glob.sync( './blocks/*/index.{js,jsx,ts,tsx}' );

blockFiles.forEach( ( file ) => {
	const normalizedFile = file.replace( /\\/g, '/' );
	const match = normalizedFile.match( /blocks\/([^/]+)\/index\./ );
	if ( match ) {
		const blockName = match[ 1 ];
		if ( ! blockFilter || blockName.includes( blockFilter ) ) {
			blockEntries[ `blocks/${ blockName }/index` ] = path.resolve( file );
		}
	}
} );

// Copy block.json, style.css, and render.php into build/blocks/{name}/
const copyPatterns = [];
const blockDirs = glob.sync( './blocks/*/' );

blockDirs.forEach( ( dir ) => {
	const normalizedDir = dir.replace( /\\/g, '/' );
	const match = normalizedDir.match( /blocks\/([^/]+)\/?$/ );
	const blockName = match?.[ 1 ];
	if ( ! blockName || blockName === 'shared' ) {
		return;
	}
	if ( blockFilter && ! blockName.includes( blockFilter ) ) {
		return;
	}

	copyPatterns.push(
		{
			from: path.resolve( dir, 'block.json' ),
			to: path.resolve( __dirname, `build/blocks/${ blockName }/block.json` ),
		},
		{
			from: path.resolve( dir, 'style.css' ),
			to: path.resolve( __dirname, `build/blocks/${ blockName }/style.css` ),
			noErrorOnMissing: true,
		},
		{
			from: path.resolve( dir, 'render.php' ),
			to: path.resolve( __dirname, `build/blocks/${ blockName }/render.php` ),
			noErrorOnMissing: true,
		}
	);
} );

module.exports = {
	...defaultConfig,
	entry: {
		...blockEntries,
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'build' ),
	},
	plugins: [
		...defaultConfig.plugins,
		new CopyPlugin( {
			patterns: copyPatterns,
		} ),
	],
};
