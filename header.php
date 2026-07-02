<?php
/**
 * Theme header template.
 *
 * @package CustomTheme
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="mbn-site flex min-h-screen flex-col">
	<header id="masthead" class="mbn-site-header w-full">
		<?php
		$mbn_header = function_exists( 'mbn_render_part_template' ) ? mbn_render_part_template( 'header' ) : '';
		if ( '' !== $mbn_header ) {
			echo $mbn_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block render output.
		} else {
          ?>
			<div class="mbn-site-header-inner container mx-auto flex items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mbn-site-brand text-lg font-bold no-underline" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
				<?php
				if ( has_nav_menu( 'primary-menu' ) ) {
					wp_nav_menu(
                      array(
						  'theme_location'  => 'primary-menu',
						  'container'       => 'nav',
						  'container_class' => 'mbn-primary-nav',
						  'menu_class'      => 'mbn-primary-menu m-0 flex flex-wrap items-center gap-6 p-0',
						  'depth'           => 2,
						  'fallback_cb'     => false,
					  )
					);
				}
				?>
			</div>
			<?php
		}
		?>
	</header>
