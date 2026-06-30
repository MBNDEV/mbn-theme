<?php
/**
 * Main template file.
 *
 * @package CustomTheme
 */

get_header();
?>
<main id="main" class="site-main">
  <?php
  if ( have_posts() ) :
    while ( have_posts() ) :
      the_post();
      get_template_part( 'template-parts/content', get_post_type() );
    endwhile;
  else :
    get_template_part( 'template-parts/content', 'none' );
  endif;
  ?>
</main>
<?php
get_footer();
