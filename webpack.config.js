const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const glob = require( 'glob' );
const CopyPlugin = require( 'copy-webpack-plugin' );

// Auto-discover block entry points.
// Set BLOCK env var to filter: e.g. BLOCK=header-navigation
const blockFilter = process.env.BLOCK || '';
const blockEntries = {};
const blockFiles = glob.sync( './blocks/*/index.{js,jsx,ts,tsx}' );

blockFiles.forEach( ( file ) => {
  // Normalize path separators for Windows compatibility
  const normalizedFile = file.replace( /\\/g, '/' );
  const match = normalizedFile.match( /blocks\/([^/]+)\/index\./ );
  if ( match ) {
    const blockName = match[ 1 ];
    // Skip shared components directory — it is a compile-time alias, not a block
    if ( blockName === 'shared' ) {
      return;
    }
    // Skip example block from build
    if ( blockName === 'example' ) {
      return;
    }
    if ( ! blockFilter || blockName.includes( blockFilter ) ) {
      blockEntries[ `blocks/${ blockName }/index` ] = path.resolve( file );
    }
  }
} );

// Copy block.json, style.css, and render.php into build/blocks/{name}/.
const copyPatterns = [];
const blockDirs = glob.sync( './blocks/*/' );

blockDirs.forEach( ( dir ) => {
  // Normalize path separators for Windows compatibility
  const normalizedDir = dir.replace( /\\/g, '/' );
  const match = normalizedDir.match( /blocks\/([^/]+)\/?$/ );
  const blockName = match?.[ 1 ];
  if ( ! blockName ) {
    return;
  }
  // Skip shared components directory
  if ( blockName === 'shared' ) {
    return;
  }
  // Skip example block from build
  if ( blockName === 'example' ) {
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
    },
    {
      from: path.resolve( dir, 'script.js' ),
      to: path.resolve( __dirname, `build/blocks/${ blockName }/script.js` ),
      noErrorOnMissing: true,
    },
    {
      from: path.resolve( dir, 'assets' ),
      to: path.resolve( __dirname, `build/blocks/${ blockName }/assets` ),
      noErrorOnMissing: true,
    }
  );
} );

// Debug: Log copy patterns
if ( copyPatterns.length > 0 ) {
  console.log( `📋 Copying ${ copyPatterns.length } files for ${ blockDirs.length } blocks...` );
}

module.exports = {
  ...defaultConfig,
  entry: blockEntries,
  output: {
    ...defaultConfig.output,
    filename: '[name].js',
    path: path.resolve( __dirname, 'build' ),
    clean: ! blockFilter,
  },
  resolve: {
    ...defaultConfig.resolve,
    alias: {
      ...( defaultConfig.resolve?.alias ?? {} ),
      '@mbn/editor': path.resolve( __dirname, 'blocks/shared/index.js' ),
    },
  },
  plugins: [
    ...( defaultConfig.plugins || [] ),
    ...( copyPatterns.length ? [ new CopyPlugin( { patterns: copyPatterns } ) ] : [] ),
  ],
};
