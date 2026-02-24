<?php
/**
 * JASPI Astra Theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package JASPI Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define constants.
 */
define( 'CHILD_THEME_JASPI_ASTRA_VERSION', '1.0.0' );

require_once get_stylesheet_directory() . '/includes/header-custom.php';
require_once get_stylesheet_directory() . '/includes/footer-custom.php';
require_once get_stylesheet_directory() . '/includes/single-hero.php';

/**
 * Register theme menu locations.
 */
function jaspi_register_menus() {
	register_nav_menus(
		array(
			'jaspi-categories-menu' => __( 'JASPI Header: Categories Menu', 'jaspi-astra' ),
			'jaspi-quick-links-menu' => __( 'JASPI Header: Quick Links Menu', 'jaspi-astra' ),
			'jaspi-footer-about-menu' => __( 'JASPI Footer: Sobre JASPI', 'jaspi-astra' ),
			'jaspi-footer-branches-menu' => __( 'JASPI Footer: Asistencia', 'jaspi-astra' ),
			'jaspi-footer-legal-menu' => __( 'JASPI Footer: Legal', 'jaspi-astra' ),
		)
	);
}
add_action( 'init', 'jaspi_register_menus' );

/**
 * Enqueue child theme styles.
 */
function child_enqueue_styles() {
	wp_enqueue_style(
		'jaspi-astra-theme-css',
		get_stylesheet_directory_uri() . '/style.css',
		array( 'astra-theme-css' ),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		'all'
	);
}
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

/**
 * Enqueue custom header script.
 */
function jaspi_enqueue_header_script() {
	wp_enqueue_script(
		'jaspi-header-js',
		get_stylesheet_directory_uri() . '/assets/js/custom-header.js',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'jaspi_enqueue_header_script', 20 );

add_action( 'wp_body_open', 'jaspi_render_custom_header', 5 );
add_action( 'wp_footer', 'jaspi_render_custom_footer', 5 );
