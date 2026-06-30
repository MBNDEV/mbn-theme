<?php
/**
 * Singular Block Template CPT: blank-style shell only (main content column).
 *
 * Included from `singular.php` when viewing a single Block Template post.
 *
 * @package CustomTheme
 */

get_header();
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
      the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
