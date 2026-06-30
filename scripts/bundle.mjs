#!/usr/bin/env node
/**
 * Build a distributable theme zip under bundle/.
 *
 * Denylist approach: every theme file ships EXCEPT development, tooling and
 * generated artifacts. Excluded: .claude, node_modules, env files, git
 * metadata + .gitignore, and the dev/build tooling at the project root
 * (Composer/npm manifests, Tailwind/PostCSS config, resources, scripts,
 * plans, vendor). Runtime PHP, blocks, templates, the compiled Tailwind
 * stylesheet, README and screenshot are kept.
 */
import { execSync } from 'node:child_process';
import { existsSync, readFileSync, mkdirSync, rmSync, cpSync, readdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const here = path.dirname( fileURLToPath( import.meta.url ) );
const root = path.resolve( here, '..' );
const slug = 'mbn-theme';
const outDir = path.join( root, 'bundle' );
const stageDir = path.join( outDir, slug );

/**
 * Names (files or directories, matched anywhere in the tree) that must never
 * ship: editor/agent/VCS metadata, dependencies, generated output, and build
 * and lint tooling.
 */
const EXCLUDE_NAMES = new Set( [
	// editor / agent / VCS
	'.claude',
	'.git',
	'.gitignore',
	'.vscode',
	'.DS_Store',
	// dependencies & generated output
	'node_modules',
	'vendor',
	'bundle',
	// build / lint tooling source & config (the compiled build/ ships, src/ does not)
	'src',
	'resources',
	'scripts',
	'plans',
	'tailwind.config.js',
	'webpack.config.js',
	'postcss.config.js',
	'phpcs.xml',
	'composer.json',
	'composer.lock',
	'package.json',
	'package-lock.json',
	'yarn.lock',
] );

/** Extra patterns applied to the theme-root-relative path. */
const EXCLUDE_PATTERNS = [
	/(^|\/)\.env($|\.)/, // .env, .env.example, .env.local …
	/\.map$/,
];

function isExcluded( rel ) {
	if ( EXCLUDE_NAMES.has( path.basename( rel ) ) ) {
		return true;
	}
	return EXCLUDE_PATTERNS.some( ( re ) => re.test( rel ) );
}

console.log( 'Building blocks (JS) + Tailwind CSS…' );
execSync( 'npm run build', { cwd: root, stdio: 'inherit' } );

console.log( 'Staging theme files…' );
rmSync( outDir, { recursive: true, force: true } );
mkdirSync( stageDir, { recursive: true } );

for ( const entry of readdirSync( root ) ) {
	if ( isExcluded( entry ) ) {
		continue;
	}
	cpSync( path.join( root, entry ), path.join( stageDir, entry ), {
		recursive: true,
		filter: ( source ) =>
			! isExcluded( path.relative( root, source ).replace( /\\/g, '/' ) ),
	} );
}

// Ship a production-only vendor/ ONLY when the theme has real runtime Composer
// deps. dev-only deps (phpcs etc.) never ship, and the local dev vendor/ is left
// untouched — a clean --no-dev tree is built inside the staged copy instead.
const composerPath = path.join( root, 'composer.json' );
const runtimeDeps = existsSync( composerPath )
	? Object.keys( JSON.parse( readFileSync( composerPath, 'utf8' ) ).require ?? {} )
	: [];

if ( runtimeDeps.length ) {
	console.log( 'Installing runtime Composer dependencies (--no-dev)…' );
	cpSync( composerPath, path.join( stageDir, 'composer.json' ) );
	const lockPath = path.join( root, 'composer.lock' );
	if ( existsSync( lockPath ) ) {
		cpSync( lockPath, path.join( stageDir, 'composer.lock' ) );
	}
	execSync(
		'composer install --no-dev --optimize-autoloader --no-interaction --no-progress',
		{ cwd: stageDir, stdio: 'inherit' }
	);
	// Drop the manifests — the shipped theme only needs the resolved vendor/.
	rmSync( path.join( stageDir, 'composer.json' ), { force: true } );
	rmSync( path.join( stageDir, 'composer.lock' ), { force: true } );
}

console.log( 'Zipping…' );
const zipName = `${ slug }.zip`;
execSync( `cd "${ outDir }" && rm -f "${ zipName }" && zip -r -q -X "${ zipName }" "${ slug }"`, {
	stdio: 'inherit',
} );

console.log( `\nBundle ready: bundle/${ zipName }` );
