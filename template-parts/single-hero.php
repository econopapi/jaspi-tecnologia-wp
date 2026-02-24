<?php
/**
 * Template part for the single hero block.
 *
 * @package JASPI Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_image     = ! empty( $args['has_image'] );
$title         = isset( $args['title'] ) ? $args['title'] : '';
$excerpt       = isset( $args['excerpt'] ) ? $args['excerpt'] : '';
$thumbnail_url = isset( $args['thumbnail_url'] ) ? $args['thumbnail_url'] : '';

$hero_classes = 'jaspi-single-hero' . ( $has_image ? ' has-image' : ' no-image' );
$style_attr   = $has_image ? ' style="background-image: url(' . esc_url( $thumbnail_url ) . ');"' : '';
?>
<section class="<?php echo esc_attr( $hero_classes ); ?>"<?php echo $style_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- contains escaped URL. ?>>
	<span class="jaspi-single-hero__overlay" aria-hidden="true"></span>
	<div class="jaspi-single-hero__inner">
		<h1 class="jaspi-single-hero__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $excerpt ) : ?>
			<p class="jaspi-single-hero__excerpt"><?php echo esc_html( $excerpt ); ?></p>
		<?php endif; ?>
	</div>
</section>
