<?php
/**
 * Template Name: Wide Template
 * Template Post Type: page, post
 *
 * Main column, full content width.
 *
 * @package CustomTheme
 */

get_header();
?>
<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
      ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'layout-wide' ); ?>>
			<div>
				<?php the_content(); ?>
			</div>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
