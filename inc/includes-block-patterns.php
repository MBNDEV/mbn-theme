<?php
/**
 * Register reusable Block Patterns for common page layouts.
 *
 * Patterns are stored in code (not database) and ship via Git to all environments.
 * Use these to quickly build pages with consistent structure.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom block pattern category.
 */
function custom_theme_register_pattern_category() {
	register_block_pattern_category(
      'custom_theme',
      array(
		  'label' => __( 'DA Motorsports', 'mbn-theme' ),
	  )
	);
}
add_action( 'init', 'custom_theme_register_pattern_category' );

/**
 * Register block patterns.
 */
function custom_theme_register_block_patterns() {

	// Hero + Content Pattern.
	register_block_pattern(
      'custom_theme/hero-with-content',
      array(
		  'title'       => __( 'Hero Section with Content', 'mbn-theme' ),
		  'description' => __( 'Full-width hero section with background image and text content below', 'mbn-theme' ),
		  'categories'  => array( 'custom_theme' ),
		  'content'     => '<!-- wp:mbn-theme/hero-section {"align":"full"} /-->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading -->
	<h2>Welcome to Our Platform</h2>
	<!-- /wp:heading -->

	<!-- wp:paragraph -->
	<p>This is a reusable pattern that ships via Git. Edit this pattern in inc/includes-block-patterns.php</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->',
	  )
	);

	// Two Column Layout Pattern.
	register_block_pattern(
      'custom_theme/two-column-content',
      array(
		  'title'       => __( 'Two Column Content', 'mbn-theme' ),
		  'description' => __( 'Two column layout with heading and text', 'mbn-theme' ),
		  'categories'  => array( 'custom_theme' ),
		  'content'     => '<!-- wp:columns -->
<div class="wp-block-columns">
	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":3} -->
		<h3>Column 1 Title</h3>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p>Add your content here. This pattern is defined in code and will be available on staging/production automatically.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column -->
	<div class="wp-block-column">
		<!-- wp:heading {"level":3} -->
		<h3>Column 2 Title</h3>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p>Patterns are reusable across all your projects. Copy this file to new themes and customize.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->
</div>
<!-- /wp:columns -->',
	  )
	);

	// Full Page Home Template Pattern
	register_block_pattern(
      'custom_theme/home-page-template',
      array(
		  'title'       => __( 'Complete Home Page', 'mbn-theme' ),
		  'description' => __( 'Full home page layout with hero, content sections, and CTA', 'mbn-theme' ),
		  'categories'  => array( 'custom_theme' ),
		  'blockTypes'  => array( 'core/post-content' ),
		  'content'     => '<!-- wp:mbn-theme/hero-section {"align":"full","heading":"BUILT TO PROTECT THOSE AT RISK","description":"We help at-risk people and institutions access protection, training, and readiness support."} /-->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:heading {"textAlign":"center","level":2} -->
	<h2 class="has-text-align-center">Who We Serve</h2>
	<!-- /wp:heading -->

	<!-- wp:columns -->
	<div class="wp-block-columns">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3} -->
			<h3>Service 1</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>Description of your first service or offering.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3} -->
			<h3>Service 2</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>Description of your second service or offering.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":3} -->
			<h3>Service 3</h3>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p>Description of your third service or offering.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:mbn-theme/cta-section {"align":"full"} /-->',
	  )
	);
}
add_action( 'init', 'custom_theme_register_block_patterns' );
