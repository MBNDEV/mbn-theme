<?php
/**
 * Appearance > MBN Theme: the theme's settings page.
 *
 * Holds every preset control (color schemes, typography, fallback fonts,
 * layout, custom HTML). Saving is handled by the
 * Settings API (options.php) against the `mbn_theme_settings` group.
 *
 * @package CustomTheme
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

/**
 * Settings API group name shared by register_setting() and settings_fields().
 */
const MBN_SETTINGS_GROUP = 'mbn_theme_settings';

/**
 * Register the Appearance > MBN Theme menu page.
 *
 * @return void
 */
function mbn_register_theme_admin_page(): void {
  add_theme_page(
    __( 'MBN Theme', 'mbn-theme' ),
    __( 'MBN Theme', 'mbn-theme' ),
    'edit_theme_options',
    'mbn-theme',
    'mbn_render_theme_admin_page'
  );
}
add_action( 'admin_menu', 'mbn_register_theme_admin_page' );

/**
 * Register all theme settings under one Settings API group.
 *
 * @return void
 */
function mbn_register_theme_settings(): void {
  register_setting(
    MBN_SETTINGS_GROUP,
    'mbn_settings',
    array(
		'type'              => 'array',
		'sanitize_callback' => 'mbn_sanitize_settings',
		'default'           => mbn_settings_defaults(),
    )
  );

  foreach ( mbn_custom_html_slots() as $slot ) {
    register_setting(
      MBN_SETTINGS_GROUP,
      $slot['option'],
      array(
		  'type'              => 'string',
		  'sanitize_callback' => 'mbn_sanitize_custom_html',
		  'default'           => '',
      )
    );
  }
}
add_action( 'admin_init', 'mbn_register_theme_settings' );

/**
 * Enqueue color picker, code editor and the settings helper script on our page.
 *
 * @param string $hook Current admin page hook suffix.
 * @return void
 */
function mbn_admin_settings_assets( string $hook ): void {
  if ( 'appearance_page_mbn-theme' !== $hook ) {
    return;
  }

  wp_enqueue_style( 'wp-color-picker' );
  wp_enqueue_script( 'wp-color-picker' );
  wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

  // Load the selected webfont + the metric-adjusted fallback @font-face rules and the
  // --mbn-* variables onto this page so the font-match preview is accurate.
  if ( function_exists( 'mbn_get_selected_font_faces_css' ) ) {
    wp_add_inline_style( 'wp-color-picker', mbn_get_selected_font_faces_css() . mbn_build_css_variables() );
  }

  wp_enqueue_script(
    'mbn-theme-settings',
    get_theme_file_uri( 'assets/js/mbn-theme-settings.js' ),
    array( 'jquery', 'wp-color-picker' ),
    '1.1.0',
    true
  );

  $families = array();
  foreach ( mbn_get_font_families() as $slug => $family ) {
    $families[ $slug ] = $family['stack'];
  }

  wp_localize_script(
    'mbn-theme-settings',
    'mbnThemeSettings',
    array(
		'families'        => $families,
		'fallbackPrimary' => 'MBN Fallback Primary',
		'fallbackSecond'  => 'MBN Fallback Secondary',
		'colorVarPrefix'  => '--mbn-color-scheme-',
		'defaultColor'    => '#2563EB',
		'removeLabel'     => __( 'Remove', 'mbn-theme' ),
    )
  );
}
add_action( 'admin_enqueue_scripts', 'mbn_admin_settings_assets' );

/**
 * Render a single color-scheme repeater row.
 *
 * @param int    $index 1-based scheme index.
 * @param string $hex   Hex value.
 * @return void
 */
function mbn_render_color_row( int $index, string $hex ): void {
  ?>
  <div class="mbn-color-row" style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
    <code class="mbn-color-var" style="min-width:190px;display:inline-block;"><?php echo esc_html( '--mbn-color-scheme-' . $index ); ?></code>
    <input type="text" class="mbn-color-field" name="mbn_settings[color_schemes][]" value="<?php echo esc_attr( $hex ); ?>" data-default-color="<?php echo esc_attr( $hex ); ?>" />
    <button type="button" class="button mbn-remove-color"><?php esc_html_e( 'Remove', 'mbn-theme' ); ?></button>
  </div>
  <?php
}

/**
 * Render the font size + weight fields (with live previews) as form-table rows.
 *
 * @param array<string, string> $sizes Size key => default size.
 * @return void
 */
function mbn_render_font_size_fields( array $sizes ): void {
  $weight_defaults = mbn_default_font_weights();
  $weight_choices  = array( '100', '200', '300', '400', '500', '600', '700', '800', '900' );

  foreach ( $sizes as $key => $default_size ) {
    $value = (string) mbn_setting( 'size_' . $key );
    $value = '' !== trim( $value ) ? $value : $default_size;

    $tablet    = (string) mbn_setting( 'size_' . $key . '_tablet' );
    $mobile    = (string) mbn_setting( 'size_' . $key . '_mobile' );
    $tablet_ph = mbn_typography_size( $key, 'tablet' );
    $mobile_ph = mbn_typography_size( $key, 'mobile' );

    $weight = (string) mbn_setting( 'weight_' . $key );
    $weight = '' !== trim( $weight ) ? $weight : ( $weight_defaults[ $key ] ?? '400' );

    /* translators: %s: heading tag or "Paragraph". */
    $label = sprintf( __( 'Type: %s', 'mbn-theme' ), 'body' === $key ? __( 'Paragraph', 'mbn-theme' ) : strtoupper( $key ) );
    ?>
    <tr>
      <th scope="row"><label for="mbn-size-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
      <td>
        <span class="mbn-type-controls" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
          <label style="font-size:12px;">
            <?php esc_html_e( 'Desktop', 'mbn-theme' ); ?><br />
            <input type="text" id="mbn-size-<?php echo esc_attr( $key ); ?>" class="mbn-size-field" style="width:90px;" name="mbn_settings[size_<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-preview="mbn-size-preview-<?php echo esc_attr( $key ); ?>" />
          </label>
          <label style="font-size:12px;">
            <?php esc_html_e( 'Tablet', 'mbn-theme' ); ?><br />
            <input type="text" class="mbn-size-field-tablet" style="width:90px;" name="mbn_settings[size_<?php echo esc_attr( $key ); ?>_tablet]" value="<?php echo esc_attr( $tablet ); ?>" placeholder="<?php echo esc_attr( $tablet_ph ); ?>" data-preview="mbn-size-preview-tablet-<?php echo esc_attr( $key ); ?>" />
          </label>
          <label style="font-size:12px;">
            <?php esc_html_e( 'Mobile', 'mbn-theme' ); ?><br />
            <input type="text" class="mbn-size-field-mobile" style="width:90px;" name="mbn_settings[size_<?php echo esc_attr( $key ); ?>_mobile]" value="<?php echo esc_attr( $mobile ); ?>" placeholder="<?php echo esc_attr( $mobile_ph ); ?>" data-preview="mbn-size-preview-mobile-<?php echo esc_attr( $key ); ?>" />
          </label>
          <label style="font-size:12px;">
            <?php esc_html_e( 'Weight', 'mbn-theme' ); ?><br />
            <select id="mbn-weight-<?php echo esc_attr( $key ); ?>" class="mbn-weight-field" name="mbn_settings[weight_<?php echo esc_attr( $key ); ?>]" data-preview="mbn-size-preview-<?php echo esc_attr( $key ); ?>">
              <?php foreach ( $weight_choices as $choice ) : ?>
                <option value="<?php echo esc_attr( $choice ); ?>" <?php selected( $weight, $choice ); ?>><?php echo esc_html( $choice ); ?></option>
              <?php endforeach; ?>
            </select>
          </label>
        </span>
        <div style="display:flex;gap:18px;flex-wrap:wrap;margin-top:8px;align-items:baseline;">
          <div class="mbn-size-preview" id="mbn-size-preview-<?php echo esc_attr( $key ); ?>" style="line-height:1.2;font-size:<?php echo esc_attr( $value ); ?>;font-weight:<?php echo esc_attr( $weight ); ?>;">
            <?php echo esc_html__( 'Desktop Aa 123', 'mbn-theme' ); ?>
          </div>
          <div class="mbn-size-preview" id="mbn-size-preview-tablet-<?php echo esc_attr( $key ); ?>" style="line-height:1.2;font-size:<?php echo esc_attr( $tablet_ph ); ?>;font-weight:<?php echo esc_attr( $weight ); ?>;opacity:.8;">
            <?php echo esc_html__( 'Tablet Aa 123', 'mbn-theme' ); ?>
          </div>
          <div class="mbn-size-preview" id="mbn-size-preview-mobile-<?php echo esc_attr( $key ); ?>" style="line-height:1.2;font-size:<?php echo esc_attr( $mobile_ph ); ?>;font-weight:<?php echo esc_attr( $weight ); ?>;opacity:.65;">
            <?php echo esc_html__( 'Mobile Aa 123', 'mbn-theme' ); ?>
          </div>
        </div>
      </td>
    </tr>
    <?php
  }
}

/**
 * Render a single fallback-font field row.
 *
 * @param string $which       'primary' or 'secondary'.
 * @param string $field       Field key.
 * @param string $field_label Display label.
 * @param bool   $is_textarea Whether to render a textarea.
 * @return void
 */
function mbn_render_fallback_field( string $which, string $field, string $field_label, bool $is_textarea ): void {
  $name  = "mbn_settings[fallback_{$which}_{$field}]";
  $value = (string) mbn_setting( "fallback_{$which}_{$field}" );
  $id    = "mbn-fallback-{$which}-{$field}";
  ?>
  <tr>
    <th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $field_label ); ?></label></th>
    <td>
      <?php if ( $is_textarea ) : ?>
        <textarea id="<?php echo esc_attr( $id ); ?>" class="large-text code" rows="3" name="<?php echo esc_attr( $name ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
      <?php else : ?>
        <input type="text" id="<?php echo esc_attr( $id ); ?>" class="regular-text" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
      <?php endif; ?>
    </td>
  </tr>
  <?php
}

/**
 * Render the primary + secondary fallback-font field groups.
 *
 * @return void
 */
function mbn_render_fallback_fields(): void {
  $groups = array(
	  'primary'   => __( 'Primary', 'mbn-theme' ),
	  'secondary' => __( 'Secondary', 'mbn-theme' ),
  );
  $fields = array(
	  'src'               => array( __( 'Local source list', 'mbn-theme' ), true ),
	  'size_adjust'       => array( __( 'size-adjust (e.g. 100%)', 'mbn-theme' ), false ),
	  'ascent_override'   => array( __( 'ascent-override (e.g. 90%)', 'mbn-theme' ), false ),
	  'descent_override'  => array( __( 'descent-override (e.g. 22%)', 'mbn-theme' ), false ),
	  'line_gap_override' => array( __( 'line-gap-override (e.g. 0%)', 'mbn-theme' ), false ),
  );

  foreach ( $groups as $which => $group_label ) {
    echo '<h3>' . esc_html( $group_label ) . '</h3>';
    echo '<table class="form-table" role="presentation">';
    foreach ( $fields as $field => $config ) {
      mbn_render_fallback_field( $which, $field, $config[0], $config[1] );
    }
    echo '</table>';
  }
}

/**
 * Render the webfont-vs-fallback overlay preview for primary and secondary fonts.
 *
 * The webfont is drawn in dark text and the metric-adjusted fallback is overlaid in
 * translucent red on top: where the two letterforms line up, the metrics match and the
 * page won't shift when the webfont loads.
 *
 * @return void
 */
function mbn_render_font_match_preview(): void {
  $rows   = array(
	  'primary'   => array( __( 'Primary font &amp; fallback', 'mbn-theme' ), 'var(--mbn-font-primary)', "'MBN Fallback Primary', sans-serif", 'primary' ),
	  'secondary' => array( __( 'Secondary font &amp; fallback', 'mbn-theme' ), 'var(--mbn-font-secondary)', "'MBN Fallback Secondary', sans-serif", 'secondary' ),
  );
  $sample = __( 'The quick brown fox jumps 0123', 'mbn-theme' );
  ?>
  <div class="mbn-font-match" style="display:flex;flex-wrap:wrap;gap:24px;margin:4px 0 16px;">
    <?php foreach ( $rows as $key => $row ) : ?>
      <div style="flex:1 1 320px;border:1px solid #dcdcde;border-radius:6px;padding:12px 16px;background:#fff;">
        <p style="margin:0 0 6px;font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#646970;"><?php echo esc_html( $row[0] ); ?></p>
        <div style="position:relative;font-size:34px;line-height:1.25;white-space:nowrap;overflow:hidden;">
          <span class="mbn-font-match__web" data-preview="<?php echo esc_attr( $row[3] ); ?>" style="font-family:<?php echo esc_attr( $row[1] ); ?>;color:#1d2327;"><?php echo esc_html( $sample ); ?></span>
          <span class="mbn-font-match__fallback" style="position:absolute;inset:0;font-family:<?php echo esc_attr( $row[2] ); ?>;color:#e50b07;opacity:.45;"><?php echo esc_html( $sample ); ?></span>
        </div>
        <p style="margin:6px 0 0;font-size:11px;color:#646970;">
          <span style="color:#1d2327;">■</span> <?php esc_html_e( 'Webfont', 'mbn-theme' ); ?>
          &nbsp; <span style="color:#e50b07;">■</span> <?php esc_html_e( 'Fallback', 'mbn-theme' ); ?>
        </p>
      </div>
    <?php endforeach; ?>
  </div>
  <?php
}

/**
 * Render the Appearance > MBN Theme settings page.
 *
 * @return void
 */
function mbn_render_theme_admin_page(): void {
  $families  = mbn_get_font_families();
  $primary   = (string) mbn_setting( 'font_primary' );
  $secondary = (string) mbn_setting( 'font_secondary' );
  $schemes   = mbn_get_color_schemes();
  $sizes     = mbn_default_font_sizes();
  ?>
  <div class="wrap mbn-theme-admin">
    <h1><?php esc_html_e( 'MBN Theme', 'mbn-theme' ); ?></h1>
    <p class="description" style="max-width:70ch;">
      <?php esc_html_e( 'Configure the theme design system. Fonts come from the WordPress Fonts Library (installed/custom fonts only).', 'mbn-theme' ); ?>
    </p>

    <form method="post" action="options.php">
      <?php settings_fields( MBN_SETTINGS_GROUP ); ?>

      <h2 class="title"><?php esc_html_e( 'Color Schemes', 'mbn-theme' ); ?></h2>
      <p class="description">
        <?php esc_html_e( 'Each color becomes --mbn-color-scheme-N in order. Scheme 1 = primary, scheme 2 = secondary (falls back to scheme 1).', 'mbn-theme' ); ?>
      </p>
      <div id="mbn-color-schemes">
        <?php foreach ( $schemes as $index => $hex ) : ?>
          <?php mbn_render_color_row( (int) $index, (string) $hex ); ?>
        <?php endforeach; ?>
      </div>
      <p>
        <button type="button" class="button mbn-add-color"><?php esc_html_e( 'Add color scheme', 'mbn-theme' ); ?></button>
      </p>

      <h2 class="title"><?php esc_html_e( 'Typography', 'mbn-theme' ); ?></h2>
      <?php if ( empty( $families ) ) : ?>
        <p class="notice notice-warning" style="padding:10px 12px;">
          <?php esc_html_e( 'No custom fonts are installed yet. Install fonts in the WordPress Fonts Library; they will appear here for selection.', 'mbn-theme' ); ?>
        </p>
      <?php endif; ?>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="mbn-font-primary"><?php esc_html_e( 'Primary font (headings)', 'mbn-theme' ); ?></label></th>
          <td>
            <select id="mbn-font-primary" name="mbn_settings[font_primary]" class="mbn-font-select" data-preview="primary">
              <option value=""><?php esc_html_e( '— None (fallback only) —', 'mbn-theme' ); ?></option>
              <?php foreach ( $families as $slug => $family ) : ?>
                <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $primary, $slug ); ?>><?php echo esc_html( $family['name'] ); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="mbn-font-secondary"><?php esc_html_e( 'Secondary font (body)', 'mbn-theme' ); ?></label></th>
          <td>
            <select id="mbn-font-secondary" name="mbn_settings[font_secondary]" class="mbn-font-select" data-preview="secondary">
              <option value=""><?php esc_html_e( '— None (fallback only) —', 'mbn-theme' ); ?></option>
              <?php foreach ( $families as $slug => $family ) : ?>
                <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $secondary, $slug ); ?>><?php echo esc_html( $family['name'] ); ?></option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><?php esc_html_e( 'Preview', 'mbn-theme' ); ?></th>
          <td>
            <p class="mbn-font-preview-primary" style="margin:0 0 4px;font-size:22px;font-weight:600;">
              <?php esc_html_e( 'Heading — The quick brown fox', 'mbn-theme' ); ?>
            </p>
            <p class="mbn-font-preview-secondary" style="margin:0;font-size:15px;">
              <?php esc_html_e( 'Body — Pack my box with five dozen liquor jugs.', 'mbn-theme' ); ?>
            </p>
          </td>
        </tr>
      </table>

      <h3><?php esc_html_e( 'Font sizes', 'mbn-theme' ); ?></h3>
      <p class="description">
        <?php esc_html_e( 'Set the desktop size for each type level. Leave Tablet/Mobile empty to auto-reduce from the desktop size (the placeholder shows the value that will be used); headings never go below the paragraph size, and the paragraph stays the same size by default.', 'mbn-theme' ); ?>
      </p>
      <table class="form-table" role="presentation">
        <?php mbn_render_font_size_fields( $sizes ); ?>
      </table>

      <h2 class="title"><?php esc_html_e( 'Fallback Fonts', 'mbn-theme' ); ?></h2>
      <p class="description">
        <?php esc_html_e( 'Metric-adjusted fallback faces emitted as @font-face to reduce layout shift before the chosen webfont loads. Match size-adjust / ascent / descent / line-gap so the fallback below sits as close to the webfont as possible — the closer they overlap, the less the page shifts when the webfont loads.', 'mbn-theme' ); ?>
      </p>
      <?php mbn_render_font_match_preview(); ?>
      <?php mbn_render_fallback_fields(); ?>

      <h2 class="title"><?php esc_html_e( 'Layout', 'mbn-theme' ); ?></h2>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="mbn-container-width"><?php esc_html_e( 'Container width', 'mbn-theme' ); ?></label></th>
          <td>
            <input type="text" id="mbn-container-width" class="regular-text" name="mbn_settings[container_width]" value="<?php echo esc_attr( (string) mbn_setting( 'container_width' ) ); ?>" />
            <p class="description"><?php esc_html_e( 'Overrides the Tailwind .container max-width. e.g. 1280px, 80rem.', 'mbn-theme' ); ?></p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="mbn-border-radius"><?php esc_html_e( 'Border radius', 'mbn-theme' ); ?></label></th>
          <td>
            <input type="text" id="mbn-border-radius" class="regular-text" name="mbn_settings[border_radius]" value="<?php echo esc_attr( (string) mbn_setting( 'border_radius' ) ); ?>" />
            <p class="description"><?php esc_html_e( 'Exposed as --mbn-radius and the .mbn-radius utility.', 'mbn-theme' ); ?></p>
          </td>
        </tr>
      </table>

      <h2 class="title"><?php esc_html_e( 'Custom HTML (Global)', 'mbn-theme' ); ?></h2>
      <table class="form-table" role="presentation">
        <?php foreach ( mbn_custom_html_slots() as $slot ) : ?>
          <tr>
            <th scope="row"><label for="<?php echo esc_attr( $slot['option'] ); ?>"><?php echo esc_html( $slot['label'] ); ?></label></th>
            <td>
              <textarea id="<?php echo esc_attr( $slot['option'] ); ?>" class="mbn-code-editor large-text code" rows="6" name="<?php echo esc_attr( $slot['option'] ); ?>"><?php echo esc_textarea( (string) get_option( $slot['option'], '' ) ); ?></textarea>
              <p class="description"><?php echo esc_html( $slot['description'] ); ?></p>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>

      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}
