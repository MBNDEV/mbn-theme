<?php
/**
 * Native WordPress Theme Options (replaces Carbon Fields PresetOptionsContainer).
 * Appearance > Theme Options
 *
 * @package BlackLineGuardianFund
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme options page under Appearance menu.
 */
function blgf_register_theme_options_page() {
	add_theme_page(
      __( 'Theme Options', 'mbn-theme' ),
      __( 'Theme Options', 'mbn-theme' ),
      'manage_options',
      'mbn-theme-options',
      'blgf_render_theme_options_page'
	);
}
add_action( 'admin_menu', 'blgf_register_theme_options_page' );

/**
 * Register theme option settings.
 */
function blgf_register_theme_settings() {
	// Typography section.
	register_setting( 'blgf_theme_options', 'blgf_font_primary', array( 'default' => 'inter' ) );
	register_setting( 'blgf_theme_options', 'blgf_font_secondary', array( 'default' => 'system_sans' ) );

	// Appearance section.
	register_setting( 'blgf_theme_options', 'blgf_primary_accent_color', array( 'default' => '#2563EB' ) );
	register_setting( 'blgf_theme_options', 'blgf_secondary_accent_color', array( 'default' => '#64748B' ) );

	// Performance section.
	register_setting( 'blgf_theme_options', 'blgf_opt_front_remove_block_global', array( 'default' => true ) );
	register_setting( 'blgf_theme_options', 'blgf_opt_front_remove_classic_theme_styles', array( 'default' => true ) );

	// Custom HTML sections.
	register_setting( 'blgf_theme_options', 'blgf_global_html_head', array( 'default' => '' ) );
	register_setting( 'blgf_theme_options', 'blgf_global_html_before_body', array( 'default' => '' ) );
	register_setting( 'blgf_theme_options', 'blgf_global_html_after_body', array( 'default' => '' ) );
	register_setting( 'blgf_theme_options', 'blgf_global_html_footer', array( 'default' => '' ) );
}
add_action( 'admin_init', 'blgf_register_theme_settings' );

/**
 * Render theme options page.
 */
function blgf_render_theme_options_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
      return;
  }

	// Get font presets.
	$font_choices = array();
  foreach ( blgf_get_font_presets() as $slug => $preset ) {
      $font_choices[ $slug ] = $preset['label'];
  }

  ?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'blgf_theme_options' );
			do_settings_sections( 'blgf_theme_options' );
			?>
			
			<table class="form-table">
				<!-- Typography Section -->
				<tr>
					<th colspan="2"><h2><?php esc_html_e( 'Typography', 'mbn-theme' ); ?></h2></th>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_font_primary"><?php esc_html_e( 'Primary font (headings)', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<select name="blgf_font_primary" id="blgf_font_primary">
							<?php foreach ( $font_choices as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( get_option( 'blgf_font_primary', 'inter' ), $slug ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Applied to heading tags (h1–h6).', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_font_secondary"><?php esc_html_e( 'Secondary font (body)', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<select name="blgf_font_secondary" id="blgf_font_secondary">
							<?php foreach ( $font_choices as $slug => $label ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( get_option( 'blgf_font_secondary', 'system_sans' ), $slug ); ?>>
									<?php echo esc_html( $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Applied to body text.', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				
				<!-- Appearance Section -->
				<tr>
					<th colspan="2"><h2><?php esc_html_e( 'Appearance', 'mbn-theme' ); ?></h2></th>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_primary_accent_color"><?php esc_html_e( 'Primary accent', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<input type="text" name="blgf_primary_accent_color" id="blgf_primary_accent_color" value="<?php echo esc_attr( get_option( 'blgf_primary_accent_color', '#2563EB' ) ); ?>" class="blgf-color-picker" />
						<p class="description"><?php esc_html_e( 'Maps to CSS variable --cbb-accent-primary on :root.', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_secondary_accent_color"><?php esc_html_e( 'Secondary accent', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<input type="text" name="blgf_secondary_accent_color" id="blgf_secondary_accent_color" value="<?php echo esc_attr( get_option( 'blgf_secondary_accent_color', '#64748B' ) ); ?>" class="blgf-color-picker" />
						<p class="description"><?php esc_html_e( 'Maps to CSS variable --cbb-accent-secondary on :root.', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				
				<!-- Performance Section -->
				<tr>
					<th colspan="2"><h2><?php esc_html_e( 'Performance', 'mbn-theme' ); ?></h2></th>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_opt_front_remove_block_global"><?php esc_html_e( 'Remove core block assets', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="blgf_opt_front_remove_block_global" id="blgf_opt_front_remove_block_global" value="1" <?php checked( get_option( 'blgf_opt_front_remove_block_global', true ), true ); ?> />
						<label for="blgf_opt_front_remove_block_global"><?php esc_html_e( 'Remove core block scripts, global styles, and stored styles on the front end', 'mbn-theme' ); ?></label>
						<p class="description"><?php esc_html_e( 'When enabled, skips loading the block library, theme.json global styles, and stored block styles on public pages. Disable if the front end needs those assets.', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_opt_front_remove_classic_theme_styles"><?php esc_html_e( 'Remove classic theme styles', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<input type="checkbox" name="blgf_opt_front_remove_classic_theme_styles" id="blgf_opt_front_remove_classic_theme_styles" value="1" <?php checked( get_option( 'blgf_opt_front_remove_classic_theme_styles', true ), true ); ?> />
						<label for="blgf_opt_front_remove_classic_theme_styles"><?php esc_html_e( 'Remove classic theme styles on the front end', 'mbn-theme' ); ?></label>
						<p class="description"><?php esc_html_e( 'When enabled, skips WordPress classic theme stylesheet output on public pages. Disable if you rely on that CSS.', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				
				<!-- Custom HTML Section -->
				<tr>
					<th colspan="2"><h2><?php esc_html_e( 'Custom HTML (Global)', 'mbn-theme' ); ?></h2></th>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_global_html_head"><?php esc_html_e( 'Head', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<textarea name="blgf_global_html_head" id="blgf_global_html_head" rows="10" class="large-text code"><?php echo esc_textarea( get_option( 'blgf_global_html_head', '' ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Printed inside the document head (e.g. meta tags, styles).', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_global_html_before_body"><?php esc_html_e( 'Before Body', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<textarea name="blgf_global_html_before_body" id="blgf_global_html_before_body" rows="10" class="large-text code"><?php echo esc_textarea( get_option( 'blgf_global_html_before_body', '' ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Printed right after the opening body tag.', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_global_html_after_body"><?php esc_html_e( 'After Body', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<textarea name="blgf_global_html_after_body" id="blgf_global_html_after_body" rows="10" class="large-text code"><?php echo esc_textarea( get_option( 'blgf_global_html_after_body', '' ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Printed after the main page wrapper, before footer scripts.', 'mbn-theme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="blgf_global_html_footer"><?php esc_html_e( 'Footer', 'mbn-theme' ); ?></label>
					</th>
					<td>
						<textarea name="blgf_global_html_footer" id="blgf_global_html_footer" rows="10" class="large-text code"><?php echo esc_textarea( get_option( 'blgf_global_html_footer', '' ) ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Printed at the start of wp_footer (before most scripts).', 'mbn-theme' ); ?></p>
					</td>
				</tr>
			</table>
			
			<?php submit_button(); ?>
		</form>
	</div>
	
	<script>
	jQuery(document).ready(function($) {
		$('.blgf-color-picker').wpColorPicker();
	});
	</script>
	<?php
}

/**
 * Enqueue color picker for theme options page.
 *
 * @param string $hook Current admin page hook.
 */
function blgf_enqueue_theme_options_assets( $hook ) {
  if ( 'appearance_page_mbn-theme-options' !== $hook ) {
      return;
  }

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
}
add_action( 'admin_enqueue_scripts', 'blgf_enqueue_theme_options_assets' );

/**
 * Get theme option (wrapper function compatible with old Carbon Fields calls).
 *
 * @param string $key Option key.
 * @param mixed  $default_value Default value.
 * @return mixed
 */
function blgf_get_theme_option( $key, $default_value = '' ) {
	// Map old Carbon Fields keys to new keys.
	$key_map = array(
		'crb_font_primary'                          => 'blgf_font_primary',
		'crb_font_secondary'                        => 'blgf_font_secondary',
		'crb_primary_accent_color'                  => 'blgf_primary_accent_color',
		'crb_secondary_accent_color'                => 'blgf_secondary_accent_color',
		'crb_opt_front_remove_block_global'         => 'blgf_opt_front_remove_block_global',
		'crb_opt_front_remove_classic_theme_styles' => 'blgf_opt_front_remove_classic_theme_styles',
		'crb_global_html_head'                      => 'blgf_global_html_head',
		'crb_global_html_before_body'               => 'blgf_global_html_before_body',
		'crb_global_html_after_body'                => 'blgf_global_html_after_body',
		'crb_global_html_footer'                    => 'blgf_global_html_footer',
	);

	$mapped_key = isset( $key_map[ $key ] ) ? $key_map[ $key ] : $key;

	return get_option( $mapped_key, $default_value );
}
