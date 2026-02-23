<?php
/**
 * Footer custom helpers and renderer.
 *
 * @package JASPI Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jaspi_get_fallback_footer_about_links() {
	return array(
		array(
			'label' => __( 'Contacto', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Conócenos', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Blog', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Cuentas bancarias', 'jaspi-astra' ),
			'url'   => '#',
		),
	);
}

function jaspi_get_fallback_footer_branches_links() {
	return array(
		array(
			'label' => __( 'Rastrear mi pedido', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Facturación', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Formas de pago', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Políticas de Compra y Descuentos', 'jaspi-astra' ),
			'url'   => '#',
		),
        array(
			'label' => __( 'Política de Garantías', 'jaspi-astra' ),
			'url'   => '#',
		),
        array(
			'label' => __( 'Política de Devolución', 'jaspi-astra' ),
			'url'   => '#',
		),
	);
}

function jaspi_get_fallback_footer_legal_links() {
	return array(
		array(
			'label' => __( 'Políticas JASPI', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Aviso de privacidad', 'jaspi-astra' ),
			'url'   => '#',
		),
		array(
			'label' => __( 'Aviso legal', 'jaspi-astra' ),
			'url'   => '#',
		),
	);
}

function jaspi_get_footer_icon( $icon ) {
	$icons = array(
		'email'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M3.75 7.5h16.5v9a1.5 1.5 0 0 1-1.5 1.5h-13.5a1.5 1.5 0 0 1-1.5-1.5v-9Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 8.25 7.5 5.25 7.5-5.25" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'phone'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="M18.65 14.85c-.95 0-1.87-.15-2.73-.42a1.34 1.34 0 0 0-1.31.31l-1.68 1.69a14.8 14.8 0 0 1-5.37-5.37l1.69-1.68c.36-.35.49-.86.31-1.31A9.68 9.68 0 0 1 9.15 5.3c0-.73-.58-1.3-1.3-1.3H5.35c-.72 0-1.35.58-1.3 1.3.37 6.33 5.42 11.38 11.75 11.75.72.05 1.3-.58 1.3-1.3v-2.6c0-.72-.57-1.3-1.3-1.3Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'facebook'  => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M13.5 8.6h1.7V6.2h-1.7c-1.94 0-3.1 1.15-3.1 3.3v1.3H8.8v2.4h1.6v4h2.5v-4h1.9l.3-2.4h-2.2V9.7c0-.73.3-1.1 1-1.1Z" fill="currentColor"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><rect x="8" y="8" width="8" height="8" rx="2.3" stroke="currentColor" stroke-width="1.5"/><circle cx="12" cy="12" r="2.2" stroke="currentColor" stroke-width="1.5"/><circle cx="15.8" cy="8.4" r="0.8" fill="currentColor"/></svg>',
		'x'         => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="m8 8 8 8M16 8l-8 8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>',
		'linkedin'  => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><circle cx="9" cy="9.1" r="1" fill="currentColor"/><path d="M8 11h2v5H8v-5Zm4 0h2v.8c.4-.55 1-.95 1.9-.95 1.54 0 2.1 1.02 2.1 2.58V16h-2v-2.17c0-.78-.22-1.3-.95-1.3-.72 0-1.05.48-1.05 1.3V16h-2v-5Z" fill="currentColor"/></svg>',
		'youtube'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.5"/><path d="M10 9.5v5l4-2.5-4-2.5Z" fill="currentColor"/></svg>',
	);

	return isset( $icons[ $icon ] ) ? $icons[ $icon ] : '';
}

function jaspi_render_footer_contact_line( $icon, $text, $href = '' ) {
	$icon_markup = jaspi_get_footer_icon( $icon );

	echo '<div class="jaspi-footer-contact-line">';
	echo '<span class="jaspi-footer-contact-icon" aria-hidden="true">' . wp_kses(
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
				'fill'            => true,
			),
			'circle' => array(
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'stroke'       => true,
				'stroke-width' => true,
				'fill'         => true,
			),
			'rect'   => array(
				'x'            => true,
				'y'            => true,
				'width'        => true,
				'height'       => true,
				'rx'           => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
		)
	) . '</span>';

	if ( $href ) {
		echo '<a href="' . esc_url( $href ) . '">' . esc_html( $text ) . '</a>';
	} else {
		echo '<span>' . esc_html( $text ) . '</span>';
	}

	echo '</div>';
}

function jaspi_render_footer_social_link( $icon, $url, $label ) {
	$icon_markup = jaspi_get_footer_icon( $icon );

	echo '<a class="jaspi-footer-social-link" href="' . esc_url( $url ) . '" aria-label="' . esc_attr( $label ) . '">';
	echo wp_kses(
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
				'fill'            => true,
			),
			'circle' => array(
				'cx'           => true,
				'cy'           => true,
				'r'            => true,
				'stroke'       => true,
				'stroke-width' => true,
				'fill'         => true,
			),
			'rect'   => array(
				'x'            => true,
				'y'            => true,
				'width'        => true,
				'height'       => true,
				'rx'           => true,
				'stroke'       => true,
				'stroke-width' => true,
			),
		)
	);
	echo '</a>';
}

function jaspi_render_custom_footer() {
	if ( is_admin() ) {
		return;
	}

	get_template_part(
		'template-parts/footer',
		'custom',
		array(
			'has_footer_about_menu'    => has_nav_menu( 'jaspi-footer-about-menu' ),
			'has_footer_branches_menu' => has_nav_menu( 'jaspi-footer-branches-menu' ),
			'has_footer_legal_menu'    => has_nav_menu( 'jaspi-footer-legal-menu' ),
		)
	);
}
