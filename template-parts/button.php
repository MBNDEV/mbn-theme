<?php
/**
 * Global button component.
 *
 * @package CustomTheme
 */

/**
 * Get global button HTML.
 *
 * @param array $args Button arguments. See inline comments for array keys.
 *                    Button text content, URL, style (primary/secondary/outline),
 *                    target, icon URL and position, icon alt text, CSS class,
 *                    and whether to open in new window.
 * @return string The button HTML.
 */
function get_global_button( $args = array() ) {
  $defaults = array(
	  'text'          => '',
	  'url'           => '#',
	  'style'         => 'primary',
	  'target'        => '_self',
	  'icon_url'      => '',
	  'icon_position' => 'after',
	  'icon_alt'      => __( 'Icon', 'mbn-theme' ),
	  'class'         => '',
	  'new_window'    => false,
  );

  $args = wp_parse_args( $args, $defaults );

  if ( empty( $args['text'] ) || empty( $args['url'] ) ) {
    return '';
  }

  $target = $args['new_window'] ? '_blank' : $args['target'];
  $rel    = '_blank' === $target ? 'noopener noreferrer' : '';

  // Build button classes based on style.
  $button_classes = blacklineguardianfund_get_button_classes( $args['style'], $args['class'] );

  $html = sprintf(
    '<a href="%1$s" target="%2$s" rel="%3$s" class="%4$s">',
    esc_url( $args['url'] ),
    esc_attr( $target ),
    esc_attr( $rel ),
    esc_attr( $button_classes )
  );

  // Icon - before text.
  if ( $args['icon_url'] && 'before' === $args['icon_position'] ) {
    $html .= sprintf(
      '<img src="%1$s" alt="%2$s" class="h-5 w-5 object-contain" />',
      esc_url( $args['icon_url'] ),
      esc_attr( $args['icon_alt'] )
    );
  }

  // Button text.
  $html .= sprintf(
    '<span class="whitespace-nowrap">%s</span>',
    esc_html( $args['text'] )
  );

  // Icon - after text.
  if ( $args['icon_url'] && 'after' === $args['icon_position'] ) {
    $html .= sprintf(
      '<img src="%1$s" alt="%2$s" class="h-5 w-5 object-contain" />',
      esc_url( $args['icon_url'] ),
      esc_attr( $args['icon_alt'] )
    );
  }

  $html .= '</a>';

  return $html;
}

/**
 * Get button classes based on style.
 *
 * @param string $style The button style: 'primary', 'secondary', 'outline'.
 * @param string $extra Additional CSS classes.
 * @return string The button classes.
 */
function blacklineguardianfund_get_button_classes( $style, $extra = '' ) {
  $base_classes = array(
	  'inline-flex',
	  'items-center',
	  'justify-center',
	  'gap-2',
	  'h-11',
	  'px-5',
	  'rounded-full',
	  'font-bold',
	  'text-base',
	  'transition-all',
	  'duration-300',
	  'cursor-pointer',
	  'shadow-md',
	  'hover:shadow-lg',
	  'hover:-translate-y-0.5',
	  'active:shadow-sm',
	  'active:translate-y-0',
	  'disabled:opacity-60',
	  'disabled:cursor-not-allowed',
	  'disabled:transform-none',
	  'disabled:shadow-md',
  );

  switch ( $style ) {
    case 'secondary':
      $style_classes = array(
		  'bg-gray-100',
		  'text-gray-900',
		  'border',
		  'border-gray-300',
		  'hover:bg-gray-200',
      );
        break;

    case 'outline':
      $style_classes = array(
		  'bg-transparent',
		  'text-amber-900',
		  'border-2',
		  'border-amber-700',
		  'hover:bg-amber-50',
      );
        break;

    case 'primary':
    default:
      $style_classes = array(
		  'bg-gradient-to-b',
		  'from-amber-100',
		  'to-amber-700',
		  'text-amber-900',
		  'hover:from-amber-100',
		  'hover:to-amber-600',
      );
        break;
  }

  $classes = array_merge( $base_classes, $style_classes );

  if ( ! empty( $extra ) ) {
    $classes[] = $extra;
  }

  return implode( ' ', $classes );
}

/**
 * Echo the global button.
 *
 * @param array $args Button arguments. @see get_global_button().
 * @return void
 */
function the_global_button( $args = array() ) {
  echo wp_kses_post( get_global_button( $args ) );
}
