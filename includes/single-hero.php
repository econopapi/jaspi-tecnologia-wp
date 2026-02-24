<?php
/**
 * Single hero renderer for posts and pages.
 *
 * @package JASPI Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determine whether to render the hero.
 *
 * @return bool
 */
function jaspi_should_render_single_hero() {
	return ! is_admin() && is_singular( array( 'post', 'page' ) );
}

/**
 * Hooked hero renderer.
 */
function jaspi_render_single_hero() {
	if ( ! jaspi_should_render_single_hero() ) {
		return;
	}

	$thumbnail_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : '';
	$has_image     = ! empty( $thumbnail_url );
	$title         = get_the_title();
	$excerpt       = has_excerpt() ? get_the_excerpt() : '';

	get_template_part(
		'template-parts/single',
		'hero',
		array(
			'title'         => $title,
			'excerpt'       => $excerpt,
			'thumbnail_url' => $thumbnail_url,
			'has_image'     => $has_image,
		)
	);
}
add_action( 'astra_entry_before', 'jaspi_render_single_hero', 5 );

/**
 * Hide Astra's default title on posts/pages to avoid duplication with hero.
 *
 * @param bool $enabled Whether the title is enabled.
 * @return bool
 */
function jaspi_hide_astra_single_title( $enabled ) {
	if ( is_singular( array( 'post', 'page' ) ) ) {
		return false;
	}

	return $enabled;
}
add_filter( 'astra_the_title_enabled', 'jaspi_hide_astra_single_title' );
