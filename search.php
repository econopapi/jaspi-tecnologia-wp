<?php
/**
 * Search results template.
 *
 * @package JASPI Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$search_query = get_search_query();
$all_product_ids = array();
$total_product_count = 0;
$display_ids = array();
$product_query = null;

if ( '' !== trim( $search_query ) ) {
	$text_query = new WP_Query(
		array(
			'post_type'      => 'product',
			's'              => $search_query,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		)
	);

	$text_product_ids = $text_query->posts;

	$sku_query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => '_sku',
					'value'   => $search_query,
					'compare' => 'LIKE',
				),
			),
		)
	);

	$sku_product_ids = $sku_query->posts;

	$all_product_ids = array_unique( array_merge( $sku_product_ids, $text_product_ids ) );
	$total_product_count = count( $all_product_ids );
	$display_ids = array_slice( $all_product_ids, 0, 12 );

	if ( ! empty( $display_ids ) ) {
		$product_query = new WP_Query(
			array(
				'post_type'      => 'product',
				'post__in'       => $display_ids,
				'post_status'    => 'publish',
				'posts_per_page' => 12,
				'orderby'        => 'post__in',
			)
		);
	}
}

$post_query = new WP_Query(
	array(
		'post_type'      => 'post',
		's'              => $search_query,
		'post_status'    => 'publish',
		'posts_per_page' => 10,
	)
);

$has_product_results = ! empty( $display_ids ) && $product_query instanceof WP_Query && $product_query->have_posts();
$has_post_results = $post_query->have_posts();
?>

<div id="primary" class="content-area jaspi-search-page">
	<main id="main" class="site-main">

		<header class="page-header jaspi-search-header">
			<h1 class="page-title">
				<?php
				/* translators: %s: search query. */
				printf( esc_html__( 'Resultados de búsqueda para: %s', 'jaspi-astra' ), '<span>' . esc_html( $search_query ) . '</span>' );
				?>
			</h1>
		</header>

		<?php if ( $has_product_results ) : ?>
			<section class="search-products-section jaspi-search-section">
				<div class="search-section-header">
					<h2 class="search-section-title"><?php esc_html_e( 'Productos', 'jaspi-astra' ); ?></h2>
					<?php if ( $total_product_count > 0 ) : ?>
						<span class="search-results-count">
							<?php
							$showing = min( $product_query->post_count, 12 );
							printf(
								esc_html__( 'Mostrando %1$d de %2$d productos', 'jaspi-astra' ),
								(int) $showing,
								(int) $total_product_count
							);
							?>
						</span>
					<?php endif; ?>
				</div>

				<div class="woocommerce">
					<ul class="products columns-4">
						<?php
						while ( $product_query->have_posts() ) :
							$product_query->the_post();
							$product = wc_get_product( get_the_ID() );

							if ( ! $product || ! $product->is_visible() ) {
								continue;
							}

							wc_get_template_part( 'content', 'product' );
						endwhile;
						wp_reset_postdata();
						?>
					</ul>
				</div>

				<?php if ( $total_product_count > 12 ) : ?>
					<div class="search-view-all-products">
						<?php
						$shop_url = add_query_arg(
							array(
								's'         => $search_query,
								'post_type' => 'product',
							),
							wc_get_page_permalink( 'shop' )
						);
						?>
						<a href="<?php echo esc_url( $shop_url ); ?>" class="view-all-products-btn">
							<?php
							printf(
								esc_html__( 'Ver todos los productos (%d)', 'jaspi-astra' ),
								(int) $total_product_count
							);
							?>
						</a>
					</div>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php if ( $has_post_results ) : ?>
			<section class="search-posts-section jaspi-search-section">
				<h2 class="search-section-title"><?php esc_html_e( 'Artículos del Blog', 'jaspi-astra' ); ?></h2>

				<div class="search-posts-grid">
					<?php while ( $post_query->have_posts() ) : $post_query->the_post(); ?>
						<article id="post-<?php the_ID(); ?>" <?php post_class( 'search-post-item' ); ?>>
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="search-post-thumbnail">
									<a href="<?php the_permalink(); ?>">
										<?php the_post_thumbnail( 'medium' ); ?>
									</a>
								</div>
							<?php endif; ?>

							<div class="search-post-content">
								<div class="search-post-meta">
									<?php
									$categories = get_the_category();
									if ( ! empty( $categories ) ) {
										echo '<span class="search-post-category">' . esc_html( $categories[0]->name ) . '</span>';
									}
									?>
								</div>

								<h3 class="search-post-title">
									<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
								</h3>

								<div class="search-post-meta-info">
									<span class="search-post-author"><?php echo esc_html( get_the_author() ); ?></span>
									<span class="search-post-date"><?php echo esc_html( get_the_date() ); ?></span>
								</div>

								<div class="search-post-excerpt">
									<?php echo esc_html( wp_trim_words( get_the_excerpt(), 20, '...' ) ); ?>
								</div>
							</div>
						</article>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! $has_product_results && ! $has_post_results ) : ?>
			<section class="no-results not-found jaspi-search-empty">
				<header class="page-header">
					<h2 class="page-title"><?php esc_html_e( 'Nada por aquí', 'jaspi-astra' ); ?></h2>
				</header>

				<div class="page-content">
					<p><?php esc_html_e( 'Lo siento, pero nada coincide con tus términos de búsqueda. Por favor, intenta de nuevo con algunas palabras clave diferentes.', 'jaspi-astra' ); ?></p>
				</div>
			</section>
		<?php endif; ?>

	</main>
</div>

<?php
get_footer();
