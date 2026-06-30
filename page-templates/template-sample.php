<?php
/**
 * Template Name: Sample Template
 * Template Post Type: page, post
 *
 * Main content column only.
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
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'layout-blank' ); ?>>
			<?php the_content(); ?>
		</article>
		<?php
	endwhile;
	?>
</main>
<?php
get_footer();
