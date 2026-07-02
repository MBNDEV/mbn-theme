<?php
/**
 * Theme footer template.
 *
 * @package CustomTheme
 */

?>
	<footer class="mbn-site-footer mt-auto w-full">
		<?php
		$mbn_footer = function_exists( 'mbn_render_part_template' ) ? mbn_render_part_template( 'footer' ) : '';
		if ( '' !== $mbn_footer ) {
			echo $mbn_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block render output.
		} else {
          ?>
			<div class="mbn-site-footer-inner container mx-auto flex flex-col items-center gap-4 px-4 py-8 sm:px-6 lg:px-8">
				<?php
				if ( has_nav_menu( 'footer-menu' ) ) {
					wp_nav_menu(
                      array(
						  'theme_location'  => 'footer-menu',
						  'container'       => 'nav',
						  'container_class' => 'mbn-footer-nav',
						  'menu_class'      => 'mbn-footer-menu m-0 flex flex-wrap items-center justify-center gap-6 p-0',
						  'depth'           => 1,
						  'fallback_cb'     => false,
					  )
					);
				}
				?>
				<p class="mbn-site-copyright m-0 text-sm opacity-70">
					<?php
					printf(
						/* translators: 1: copyright year, 2: site name. */
                      esc_html__( '© %1$s %2$s. All rights reserved.', 'mbn-theme' ),
                      esc_html( gmdate( 'Y' ) ),
                      esc_html( get_bloginfo( 'name' ) )
					);
					?>
				</p>
			</div>
			<?php
		}
		?>
	</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
