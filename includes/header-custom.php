<?php
/**
 * Header custom helpers and renderer.
 *
 * @package JASPI Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jaspi_get_fallback_categories() {
	return array(
		array(
			'label' => __( 'Accesorios', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Almacenamiento', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Audio', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Cables', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Componentes', 'jaspi-astra' ),
			'url'   => '#',
		),
	);
}

function jaspi_get_fallback_quick_links() {
	return array(
		array(
			'label' => __( 'Inicio', 'jaspi-astra' ),
			'url'   => home_url( '/' ),
		),
		array(
			'label' => __( 'Productos', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Recursos', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Ser distribuidor', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Contáctanos', 'jaspi-astra' ),
			'url'   => '#',
		),
	);
}

function jaspi_render_fallback_list( $items, $class_name = '' ) {
	$class_attr = $class_name ? ' class="' . esc_attr( $class_name ) . '"' : '';
	echo '<ul' . $class_attr . '>';
	foreach ( $items as $item ) {
		echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
	}
	echo '</ul>';
}

function jaspi_render_logo() {
	if ( has_custom_logo() ) {
		echo wp_kses_post( get_custom_logo() );
		return;
	}

	echo '<a class="jaspi-logo-text" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
}

function jaspi_get_flat_icon( $icon ) {
	$icons = array(
		'favorites' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M12 20.25c-.24 0-.47-.08-.66-.22-2.27-1.69-4.2-3.35-5.74-4.94C3.7 13.19 2.75 11.46 2.75 9.5c0-3.1 2.4-5.5 5.46-5.5 1.67 0 2.93.67 3.79 1.65C12.86 4.67 14.12 4 15.79 4c3.06 0 5.46 2.4 5.46 5.5 0 1.96-.95 3.69-2.85 5.59-1.54 1.59-3.47 3.25-5.74 4.94-.19.14-.42.22-.66.22z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'compare'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M8 5v14M8 5l-3 3M8 5l3 3M16 19V5m0 14l-3-3m3 3l3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'cart'      => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M3 4h2.1l1.5 9.1a1.4 1.4 0 0 0 1.38 1.18h8.72a1.4 1.4 0 0 0 1.37-1.1L20 7.1H6.1M9.25 19.25a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Zm8 0a1.25 1.25 0 1 1-2.5 0 1.25 1.25 0 0 1 2.5 0Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'account'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="8" r="3.3" stroke="currentColor" stroke-width="1.8"/><path d="M5.25 18.25c1.34-2.74 3.7-4.25 6.75-4.25s5.41 1.51 6.75 4.25" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	);

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : '';
}

function jaspi_render_action_link( $href, $label, $icon, $class_name = '' ) {
	$icon_markup = jaspi_get_flat_icon( $icon );
	$class_attr  = $class_name ? ' class="' . esc_attr( $class_name ) . '"' : '';
	echo '<a href="' . esc_url( $href ) . '"' . $class_attr . '>';
	echo '<span class="jaspi-action-icon" aria-hidden="true">' . wp_kses(
		$icon_markup,
		array(
			'svg'    => array(
				'viewBox'     => true,
				'fill'        => true,
				'aria-hidden' => true,
				'focusable'   => true,
			),
			'path'   => array(
				'd'               => true,
				'stroke'          => true,
				'stroke-width'    => true,
				'stroke-linecap'  => true,
				'stroke-linejoin' => true,
			),
			'circle' => array(
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
		)
	) . '</span>';

	// If this is the favorites action, print a counter bubble that can be updated by JS
	if ( 'favorites' === $icon ) {
		$count = function_exists( 'jaspi_get_favorites_count' ) ? jaspi_get_favorites_count() : 0;
		if ( $count > 0 ) {
			echo '<span class="jaspi-action-count" id="jaspi-fav-count">' . esc_html( $count ) . '</span>';
		} else {
			// render hidden placeholder so JS can reveal when >0
			echo '<span class="jaspi-action-count" id="jaspi-fav-count" style="display:none"></span>';
		}
	}

	echo '<span class="jaspi-action-label">' . esc_html( $label ) . '</span>';
	echo '</a>';
}

function jaspi_render_custom_header() {
	if ( is_admin() ) {
		return;
	}

	get_template_part(
		'template-parts/header',
		'custom',
		array(
			'has_categories_menu' => has_nav_menu( 'jaspi-categories-menu' ),
			'has_quick_links'     => has_nav_menu( 'jaspi-quick-links-menu' ),
		)
	);
}
