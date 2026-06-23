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

	// Remote Block Template reuse sites.
	register_setting(
      'blgf_theme_options',
      'blgf_template_reuse_sites',
      array(
		  'default'           => array(),
		  'sanitize_callback' => 'custom_theme_sanitize_template_reuse_sites',
	  )
	);
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

				<?php blgf_render_template_reuse_options_rows(); ?>
				
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

		var $sites = $('#blgf-template-reuse-sites');
		var nextTemplateReuseIndex = $sites.find('[data-template-reuse-row]').length;

		$('#blgf-add-template-reuse-site').on('click', function() {
			var index = nextTemplateReuseIndex++;
			var row = [
				'<div class="blgf-template-reuse-site" data-template-reuse-row>',
				'<p><label><?php echo esc_js( __( 'Site name', 'mbn-theme' ) ); ?><br><input type="text" name="blgf_template_reuse_sites[' + index + '][site_name]" value="" class="regular-text" /></label></p>',
				'<p><label><?php echo esc_js( __( 'Home URL', 'mbn-theme' ) ); ?><br><input type="url" name="blgf_template_reuse_sites[' + index + '][home_url]" value="" class="regular-text code" placeholder="https://example.com" /></label></p>',
				'<p><label><?php echo esc_js( __( 'Credentials', 'mbn-theme' ) ); ?><br><input type="password" name="blgf_template_reuse_sites[' + index + '][application_password]" value="" class="regular-text code" autocomplete="new-password" placeholder="username:application-password" /></label></p>',
				'<p class="description"><?php echo esc_js( __( 'Use username:application-password, or paste a full Basic/Bearer authorization value. A password token by itself will not authenticate.', 'mbn-theme' ) ); ?></p>',
				'<p><button type="button" class="button" data-remove-template-reuse-site><?php echo esc_js( __( 'Remove Site', 'mbn-theme' ) ); ?></button></p>',
				'</div>'
			].join('');

			$sites.append(row);
		});

		$sites.on('click', '[data-remove-template-reuse-site]', function() {
			$(this).closest('[data-template-reuse-row]').remove();
		});
	});
	</script>
	<?php
}

/**
 * Render Remote Template Reuse settings rows.
 *
 * @return void
 */
function blgf_render_template_reuse_options_rows() {
	$template_reuse_sites = function_exists( 'custom_theme_get_template_reuse_sites' )
		? custom_theme_get_template_reuse_sites()
		: array();

  if ( empty( $template_reuse_sites ) ) {
      $template_reuse_sites = array(
		  array(
			  'site_name'            => '',
			  'home_url'             => '',
			  'application_password' => '',
		  ),
      );
  }

  ?>
	<!-- Template Reuse Section -->
	<tr>
		<th colspan="2"><h2><?php esc_html_e( 'Remote Template Reuse', 'mbn-theme' ); ?></h2></th>
	</tr>
	<tr>
		<th scope="row">
			<label><?php esc_html_e( 'Remote template sites', 'mbn-theme' ); ?></label>
		</th>
		<td>
			<div id="blgf-template-reuse-sites">
				<?php foreach ( $template_reuse_sites as $index => $site ) : ?>
					<div class="blgf-template-reuse-site" data-template-reuse-row>
						<p>
							<label>
								<?php esc_html_e( 'Site name', 'mbn-theme' ); ?><br>
								<input type="text" name="blgf_template_reuse_sites[<?php echo esc_attr( $index ); ?>][site_name]" value="<?php echo esc_attr( $site['site_name'] ?? '' ); ?>" class="regular-text" />
							</label>
						</p>
						<p>
							<label>
								<?php esc_html_e( 'Home URL', 'mbn-theme' ); ?><br>
								<input type="url" name="blgf_template_reuse_sites[<?php echo esc_attr( $index ); ?>][home_url]" value="<?php echo esc_url( $site['home_url'] ?? '' ); ?>" class="regular-text code" placeholder="https://example.com" />
							</label>
						</p>
						<p>
							<label>
								<?php esc_html_e( 'Credentials', 'mbn-theme' ); ?><br>
								<input type="password" name="blgf_template_reuse_sites[<?php echo esc_attr( $index ); ?>][application_password]" value="<?php echo esc_attr( $site['application_password'] ?? '' ); ?>" class="regular-text code" autocomplete="new-password" placeholder="username:application-password" />
							</label>
						</p>
						<p class="description"><?php esc_html_e( 'Use username:application-password, or paste a full Basic/Bearer authorization value. A password token by itself will not authenticate.', 'mbn-theme' ); ?></p>
						<p>
							<button type="button" class="button" data-remove-template-reuse-site><?php esc_html_e( 'Remove Site', 'mbn-theme' ); ?></button>
						</p>
					</div>
				<?php endforeach; ?>
			</div>

			<p>
				<button type="button" class="button button-secondary" id="blgf-add-template-reuse-site"><?php esc_html_e( 'Add Remote Site', 'mbn-theme' ); ?></button>
			</p>
			<p class="description"><?php esc_html_e( 'These sites are shown in the page editor Remote Templates sidebar. Credentials are used only by the server-side proxy request.', 'mbn-theme' ); ?></p>
		</td>
	</tr>
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
