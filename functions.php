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

/**
 * Eliminar imágenes asociadas al producto al borrar un producto
 */
add_action('before_delete_post', 'jaspi_delete_product_images', 10, 1);
function jaspi_delete_product_images($post_id) {

    // Solo productos
    if (get_post_type($post_id) !== 'product') {
        return;
    }

    // Evitar ejecuciones duplicadas
    if (wp_is_post_revision($post_id)) {
        return;
    }

    // Imagen destacada
    $thumbnail_id = get_post_thumbnail_id($post_id);
    if ($thumbnail_id) {
        wp_delete_attachment($thumbnail_id, true);
    }

    // Galería del producto
    $gallery_ids = get_post_meta($post_id, '_product_image_gallery', true);

    if (!empty($gallery_ids)) {
        $gallery_ids = explode(',', $gallery_ids);

        foreach ($gallery_ids as $image_id) {
            wp_delete_attachment((int) $image_id, true);
        }
    }
}


/**
 * JASPI CUSTOM FEATURED PRODUCTS BLOCK
 * Implementación de bloque personalizado para mostrar productos destacados
 * con diferentes filtros: manual, por etiquetas, ofertas, etc.
 */

/**
 * Register JASPI Featured Products Block
 */
function jaspi_register_featured_products_block() {
	// register block script
	wp_register_script(
		'jaspi-featured-products-block',
		get_stylesheet_directory_uri() . '/blocks/featured-products/block.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-api-fetch', 'wp-url'),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	// register block styles
	wp_register_style(
		'jaspi-featured-products-block-editor',
		get_stylesheet_directory_uri() . '/blocks/featured-products/editor.css',
		array('wp-edit-blocks'),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	wp_register_style(
		'jaspi-featured-products-block',
		get_stylesheet_directory_uri() . '/blocks/featured-products/style.css',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	// register the block
	register_block_type(
		'jaspi/featured-products',
		array(
			'editor_script' => 'jaspi-featured-products-block',
			'editor_style' => 'jaspi-featured-products-block-editor',
			'style' => 'jaspi-featured-products-block',
			'render_callback' => 'jaspi_render_featured_products_block',
			'attributes' => array(
				'title' => array(
					'type' => 'string',
					'default' => 'FEATURED PRODUCTS',
				),
				'productsToShow' => array(
					'type' => 'number',
					'default' => 4,
				),
				'filterType' => array(
					'type' => 'string',
					'default' => 'manual',
				),
				'selectedProducts' => array(
					'type' => 'array',
					'default' => array(),
				),
				'selectedTags' => array(
					'type' => 'array',
					'default' => array(),
				),
				'showOnSale' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'showFeatured' => array(
					'type' => 'boolean',
					'default' => false,
				),
				'randomizeProducts' => array(
					'type' => 'boolean',
					'default' => false,
				),
			),
		)
	);
}
add_action('init', 'jaspi_register_featured_products_block');


/**
 * Render JASPI Featured Products Block
 */
function jaspi_render_featured_products_block($attributes) {
	$title = isset($attributes['title']) ? $attributes['title'] : 'FEATURED PRODUCTS';
	$products_to_show = isset($attributes['productsToShow']) ? (int)$attributes['productsToShow'] : 4;
	$filter_type = isset($attributes['filterType']) ? $attributes['filterType'] : 'manual';
	$selected_products = isset($attributes['selectedProducts']) ? $attributes['selectedProducts'] : array();
	$selected_tags = isset($attributes['selectedTags']) ? $attributes['selectedTags'] : array();
	$show_on_sale = isset($attributes['showOnSale']) ? $attributes['showOnSale'] : false;
	$show_featured = isset($attributes['showFeatured']) ? $attributes['showFeatured'] : false;
	$randomize_products = isset($attributes['randomizeProducts']) ? $attributes['randomizeProducts'] : false;

	// Preparar argumentos para WP_Query
	$args = array(
		'post_type' => 'product',
		'posts_per_page' => $products_to_show,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key' => '_stock_status',
				'value' => 'instock',
				'compare' => '='
			)
		),
		'tax_query' => array(),
	);

	// Aplicar filtros según el tipo seleccionado
	switch ($filter_type) {
		case 'manual':
			if (!empty($selected_products)) {
				if ($randomize_products) {
					// Si queremos orden aleatorio, no usar post__in con orderby
					$args['post__in'] = $selected_products;
					$args['orderby'] = 'rand';
				} else {
					// Orden normal según selección
					$args['post__in'] = $selected_products;
					$args['orderby'] = 'post__in';
				}
				// Aumentar el límite para compensar productos fuera de stock
				$args['posts_per_page'] = count($selected_products) * 2; // multiplicar por 2 para asegurar suficientes productos
			} else {
				return '<div class="jaspi-featured-products"><p>No hay productos seleccionados.</p></div>';
			}
			break;

		case 'sale':
			$args['meta_query'][] = array(
				'relation' => 'OR',
				array(
					'key' => '_sale_price',
					'value' => 0,
					'compare' => '>',
					'type' => 'NUMERIC'
				),
				array(
					'key' => '_min_variation_sale_price',
					'value' => 0,
					'compare' => '>',
					'type' => 'NUMERIC'
				)
			);
			if ($randomize_products) {
				$args['orderby'] = 'rand';
			}
			break;

		case 'featured':
			$args['tax_query'][] = array(
				'taxonomy' => 'product_visibility',
				'field' => 'name',
				'terms' => 'featured',
			);
			if ($randomize_products) {
				$args['orderby'] = 'rand';
			}
			break;

		case 'tags':
			if (!empty($selected_tags)) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_tag',
					'field' => 'term_id',
					'terms' => $selected_tags,
				);
				if ($randomize_products) {
					$args['orderby'] = 'rand';
				}
			} else {
				return '<div class="jaspi-featured-products"><p>No hay etiquetas seleccionadas.</p></div>';
			}
			break;
	}

	$products = new WP_Query($args);

	if (!$products->have_posts()) {
		return '<div class="jaspi-featured-products"><p>No se encontraron productos.</p></div>';
	}

	// Para selección manual, determinar cuántos productos en stock tenemos disponibles
	if ($filter_type === 'manual') {
		$displayed_count = min(count($selected_products), $products->found_posts);
	} else {
		$displayed_count = min($products_to_show, $products->found_posts);
	}
	
	$grid_class = 'products-count-' . $displayed_count;

	ob_start();
	?>

	<div class="jaspi-featured-products">
		<?php if (!empty($title)): ?>
			<h2 class="jaspi-featured-products-title"><?php echo esc_html($title); ?></h2>
		<?php endif; ?>
		
		<div class="featured-products-grid <?php echo esc_attr($grid_class); ?>">
			<?php 
			$products_displayed = 0;
			$max_products = ($filter_type === 'manual') ? count($selected_products) : $products_to_show;
			
			while ($products->have_posts() && $products_displayed < $max_products): 
				$products->the_post(); 
				global $product;
				
				// Skip if product is not valid or not visible
				if (!$product || !$product->is_visible()) {
					continue;
				}
				
				// Verificar stock adicional por si acaso
				if (!$product->is_in_stock()) {
					continue;
				}
				
				$product_id = get_the_ID();
				$is_on_sale = $product->is_on_sale();
				$is_featured = $product->is_featured();
				$rating = $product->get_average_rating();
				$review_count = $product->get_review_count();
				
				// Calcular porcentaje de descuento si está en oferta
				$discount_percentage = 0;
				if ($is_on_sale) {
					$regular_price = (float) $product->get_regular_price();
					$sale_price = (float) $product->get_sale_price();
					if ($regular_price > 0 && $sale_price > 0) {
						$discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
					}
				}

				// Check MSI eligibility
				//$is_msi_eligible = jaspi_is_product_msi_eligible( $product_id );
				$is_msi_eligible = false; // Placeholder, implementar lógica real según criterios de JASPI
				$products_displayed++;
			?>
			<div class="featured-product-item <?php echo $is_on_sale ? 'on-sale' : ''; ?> <?php echo $is_featured ? 'featured' : ''; ?>">
				
				<!-- Badge condicional -->
				<div class="featured-product-badge">
					<?php if ($filter_type === 'sale' && $is_on_sale && $discount_percentage > 0): ?>
						<span class="sale-badge">
							<span class="save-text">DESCUENTO</span><br>
							<span class="discount-percent"><?php echo $discount_percentage; ?>%</span>
						</span>
					<?php elseif (!$is_msi_eligible): ?>
						<span class="brs-badge">Recomendación JASPI</span>
					<?php endif; ?>
				</div>

				<?php if ($is_msi_eligible): ?>
				<div class="featured-product-badge msi-badge-container <?php echo ($filter_type === 'sale' && $is_on_sale && $discount_percentage > 0) ? 'has-sale-badge' : (($filter_type !== 'sale') ? 'has-brs-badge' : ''); ?>">
					<span class="msi-badge">Meses sin intereses</span>
				</div>
				<?php endif; ?>
				
				<!-- Imagen del producto -->
				<div class="featured-product-image">
					<a href="<?php echo get_permalink($product_id); ?>">
						<?php echo woocommerce_get_product_thumbnail(); ?>
					</a>
				</div>
				
				<!-- Contenido del producto -->
				<div class="featured-product-content">
					
					<!-- Rating -->
					<?php if ($rating > 0): ?>
					<div class="featured-product-rating">
						<div class="star-rating">
							<?php 
							for ($i = 1; $i <= 5; $i++) {
								if ($i <= $rating) {
									echo '<span class="star filled">★</span>';
								} else {
									echo '<span class="star empty">☆</span>';
								}
							}
							?>
						</div>
					</div>
					<?php endif; ?>
					
					<!-- Marca/Brand -->
					<?php 
					$brands = get_the_terms($product_id, 'pa_brand');
					if ($brands && !is_wp_error($brands)): 
						$brand = array_shift($brands);
					?>
					<div class="featured-product-brand">
						<?php echo esc_html($brand->name); ?>
					</div>
					<?php endif; ?>
					
					<!-- Título del producto -->
					<h3 class="featured-product-title">
						<a href="<?php echo get_permalink($product_id); ?>">
							<?php echo get_the_title(); ?>
						</a>
					</h3>
					
					<!-- Precio -->
					<div class="featured-product-price">
						<?php echo $product->get_price_html(); ?>
					</div>
					
					<!-- Botón Add to Cart -->
					<div class="featured-add-to-cart">
						<?php
						woocommerce_template_loop_add_to_cart();
						?>
					</div>
					
				</div>
			</div>
			<?php endwhile; ?>
		</div>
	</div>
	
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

/**
 * END JASPI CUSTOM FEATURED PRODUCTS BLOCK
 */


/**
 * JASPI CUSTOM PRODUCT CATEGORY BLOCK
 * Implementación de bloque personalizado para mostrar categorías de productos
 * en en editor de bloques de WordPress.
 */

/**
 * Register JASPI Product Category Block
 */
function jaspi_register_product_categories_block() {
	// register block script
	wp_register_script(
		'jaspi-product-categories-block',
		get_stylesheet_directory_uri() . '/blocks/product-categories/block.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-data'),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	// register block styles
	wp_register_style(
		'jaspi-product-categories-block-editor',
		get_stylesheet_directory_uri() . '/blocks/product-categories/editor.css',
		array('wp-edit-blocks'),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	wp_register_style(
		'jaspi-product-categories-block',
		get_stylesheet_directory_uri() . '/blocks/product-categories/style.css',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	// register the block
	register_block_type(
		'jaspi/product-categories',
		array(
			'editor_script' => 'jaspi-product-categories-block',
			'editor_style' => 'jaspi-product-categories-block-editor',
			'style' => 'jaspi-product-categories-block',
			'render_callback' => 'jaspi_render_product_categories_block',
			'attributes' => array(
				'selectedCategories' => array(
					'type' => 'array',
					'default' => array(),
				),
				'title' => array(
					'type' => 'string',
					'default' => 'Categorías Top',
				),
				'subtitle' => array(
					'type' => 'string',
					'default' => '',
				),
			),
		)
	);
}
add_action('init', 'jaspi_register_product_categories_block');


/**
 * Render JASPI Product Categories Block
 */
function jaspi_render_product_categories_block($attributes) {
	$selected_categories = isset($attributes['selectedCategories']) ? $attributes['selectedCategories']:array();
	$title = isset($attributes['title']) ? $attributes['title']: 'Categorías Top';
	$subtitle = isset($attributes['subtitle']) ? $attributes['subtitle']: '';

	if (empty($selected_categories)) {
		return '';
	}

	ob_start();
	?>

	<div class="jaspi-product-categories">
		<?php if (!empty($title)): ?>
			<h2 class="jaspi-product-categories-title"><?php echo esc_html($title); ?></h2>
		<?php endif; ?>
		<div class="categories-wrapper">
			<svg class="categories-curve" viewBox="0 0 1400 100" preserveAspectRatio="none">
				<path d="M0,50 Q350,0 700,50 T1400,50" fill="none" stroke="#FF1654" stroke-width="3"/>
			</svg>
			<div class="categories-container">
				<?php foreach ($selected_categories as $cat_id):
					$category = get_term($cat_id, 'product_cat');
					if (!$category || is_wp_error($category)) {
						continue;
					}

					$thumbnail_id = get_term_meta($cat_id, 'thumbnail_id', true);
					$image_url = $thumbnail_id? wp_get_attachment_url($thumbnail_id): wc_placeholder_img_src();
					$category_link = get_term_link($category);
				?>
				<div class="category-item">
					<a href="<?php echo esc_url($category_link); ?>" class="category-link">
						<div class="category-image-wrapper">
							<div class="category-circle"></div>
							<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>" class="category-image">
						</div>
						<h3 class="category-name"><?php echo esc_html($category->name); ?></h3>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php if(!empty($subtitle)): ?>
			<h4 class="category-subtitle"><?php echo esc_html($subtitle); ?></h4>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * END JASPI CUSTOM PRODUCT CATEGORY BLOCK
 */


/**
 * JASPI CUSTOM FEATURED BRANDS BLOCK
 * Implementación de bloque personalizado para mostrar marcas destacadas
 * en el editor de bloques de WordPress.
 */


/**
 * Register JASPI Featured Brands Block
 */
function jaspi_register_featured_brands_block() {
	// register block script
	wp_register_script(
		'jaspi-featured-brands-block',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/block.js',
		array('wp-blocks', 'wp-element', 'wp-components', 'wp-data'),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	// register block styles
	wp_register_style(
		'jaspi-featured-brands-block-editor',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/editor.css',
		array('wp-edit-blocks'),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	wp_register_style(
		'jaspi-featured-brands-block',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/style.css',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);

	// register carousel script
	wp_register_script(
		'jaspi-brands-carousel',
		get_stylesheet_directory_uri() . '/blocks/featured-brands/carousel.js',
		array('jquery'),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		true
	);

	// register the block
	register_block_type(
		'jaspi/featured-brands',
		array(
			'editor_script' => 'jaspi-featured-brands-block',
			'editor_style' => 'jaspi-featured-brands-block-editor',
			'style' => 'jaspi-featured-brands-block',
			'render_callback' => 'jaspi_render_featured_brands_block',
			'attributes' => array(
				'selectedBrands' => array(
					'type' => 'array',
					'default' => array(),
				),
				'title' => array(
					'type' => 'string',
					'default' => 'Marcas Destacadas',
				),
				'autoplaySpeed' => array(
					'type' => 'number',
					'default' => 3000, // en milisegundos
				),
			),
		)
	);
}
add_action('init', 'jaspi_register_featured_brands_block');


/**
 * Render JASPI Featured Brands Block
 */

/**
 * FAVORITES / WISHLIST BASIC IMPLEMENTATION
 * Stores favorites in usermeta for logged-in users and in a cookie for guests.
 */

function jaspi_get_favorites_from_cookie() {
	if ( empty( $_COOKIE['jaspi_favs'] ) ) {
		return array();
	}
	$data = json_decode( wp_unslash( $_COOKIE['jaspi_favs'] ), true );
	if ( ! is_array( $data ) ) {
		return array();
	}
	return array_map( 'intval', $data );
}

function jaspi_get_user_favorites() {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$meta = get_user_meta( $user_id, 'jaspi_favorites', true );
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}
		return array_map( 'intval', $meta );
	}

	return jaspi_get_favorites_from_cookie();
}

function jaspi_set_user_favorites( $favorites ) {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'jaspi_favorites', array_map( 'intval', $favorites ) );
	}
}

function jaspi_get_favorites_count() {
	$favs = jaspi_get_user_favorites();
	return is_array( $favs ) ? count( $favs ) : 0;
}

function jaspi_enqueue_favorites_assets() {
	wp_enqueue_script(
		'jaspi-favorites-js',
		get_stylesheet_directory_uri() . '/assets/js/favorites.js',
		array('jquery'),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		true
	);

	wp_localize_script('jaspi-favorites-js', 'jaspi_favs', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('jaspi_favs_nonce'),
		'count'    => jaspi_get_favorites_count(),
		'favorites' => jaspi_get_user_favorites(),
		'favorites_page' => esc_url( home_url( '/favoritos' ) ),
	));

	wp_enqueue_style(
		'jaspi-favorites-css',
		get_stylesheet_directory_uri() . '/assets/css/favorites.css',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'jaspi_enqueue_favorites_assets', 25 );

/**
 * Compare feature: enqueue scripts and styles
 */
function jaspi_enqueue_compare_assets() {
	wp_enqueue_script(
		'jaspi-compare-js',
		get_stylesheet_directory_uri() . '/assets/js/compare.js',
		array('jquery'),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		true
	);

	wp_localize_script('jaspi-compare-js', 'jaspi_compare', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('jaspi_compare_nonce'),
		'count'    => function_exists('jaspi_get_compare_count') ? jaspi_get_compare_count() : 0,
		'compare'  => function_exists('jaspi_get_user_compare') ? jaspi_get_user_compare() : array(),
		'compare_page' => esc_url( home_url( '/comparar' ) ),
		'limit'    => 4,
	));

	wp_enqueue_style(
		'jaspi-compare-css',
		get_stylesheet_directory_uri() . '/assets/css/compare.css',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION
	);
}
add_action( 'wp_enqueue_scripts', 'jaspi_enqueue_compare_assets', 26 );

/**
 * Compare storage helpers (usermeta + cookie)
 */
function jaspi_get_compare_from_cookie() {
	if ( empty( $_COOKIE['jaspi_compare'] ) ) {
		return array();
	}
	$data = json_decode( wp_unslash( $_COOKIE['jaspi_compare'] ), true );
	if ( ! is_array( $data ) ) {
		return array();
	}
	return array_map( 'intval', $data );
}

function jaspi_get_user_compare() {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		$meta = get_user_meta( $user_id, 'jaspi_compare', true );
		if ( ! is_array( $meta ) ) {
			$meta = array();
		}
		return array_map( 'intval', $meta );
	}
	return jaspi_get_compare_from_cookie();
}

function jaspi_set_user_compare( $compare ) {
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'jaspi_compare', array_map( 'intval', $compare ) );
	}
}

function jaspi_get_compare_count() {
	$c = jaspi_get_user_compare();
	return is_array( $c ) ? count( $c ) : 0;
}

/**
 * AJAX toggle compare
 */
function jaspi_toggle_compare_ajax() {
	check_ajax_referer( 'jaspi_compare_nonce', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Producto inválido' ), 400 );
	}

	$compare = jaspi_get_user_compare();
	$limit = 4;
	if ( in_array( $product_id, $compare, true ) ) {
		$compare = array_values( array_diff( $compare, array( $product_id ) ) );
		$action = 'removed';
	} else {
		if ( count( $compare ) >= $limit ) {
			wp_send_json_error( array( 'message' => 'Límite alcanzado', 'limit' => $limit ), 400 );
		}
		$compare[] = $product_id;
		$compare = array_values( array_unique( $compare ) );
		$action = 'added';
	}

	if ( is_user_logged_in() ) {
		jaspi_set_user_compare( $compare );
	}

	wp_send_json_success( array( 'compare' => $compare, 'count' => count( $compare ), 'action' => $action ) );
}
add_action( 'wp_ajax_jaspi_toggle_compare', 'jaspi_toggle_compare_ajax' );
add_action( 'wp_ajax_nopriv_jaspi_toggle_compare', 'jaspi_toggle_compare_ajax' );

function jaspi_clear_compare_ajax() {
	check_ajax_referer( 'jaspi_compare_nonce', 'nonce' );
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'jaspi_compare', array() );
	}
	setcookie( 'jaspi_compare', '', time() - HOUR_IN_SECONDS, '/' );
	if ( isset( $_COOKIE['jaspi_compare'] ) ) {
		unset( $_COOKIE['jaspi_compare'] );
	}
	wp_send_json_success( array( 'compare' => array(), 'count' => 0 ) );
}
add_action( 'wp_ajax_jaspi_clear_compare', 'jaspi_clear_compare_ajax' );
add_action( 'wp_ajax_nopriv_jaspi_clear_compare', 'jaspi_clear_compare_ajax' );

/**
 * Shortcode to render compare table
 */
function jaspi_compare_shortcode( $atts ) {
	$compare = jaspi_get_user_compare();
	if ( empty( $compare ) ) {
		return '<p>' . esc_html__( 'No hay productos para comparar.', 'jaspi-astra' ) . '</p>';
	}

	// Limit to first 4
	$compare = array_slice( $compare, 0, 4 );

	ob_start();
	?>
	<div class="jaspi-compare-list">
		<div class="jaspi-compare-actions" style="margin-bottom:12px;">
			<button type="button" class="button jaspi-clear-compare"><?php esc_html_e( 'Vaciar comparación', 'jaspi-astra' ); ?></button>
		</div>
		<div class="jaspi-compare-grid">
			<table class="jaspi-compare-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Campo', 'jaspi-astra' ); ?></th>
						<?php foreach ( $compare as $prod_id ): $prod = wc_get_product( $prod_id ); if ( ! $prod ) continue; ?>
							<th><?php echo esc_html( $prod->get_name() ); ?> <br> <button class="jaspi-remove-compare button" data-product-id="<?php echo esc_attr( $prod_id ); ?>">✕</button></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><?php esc_html_e( 'Imagen', 'jaspi-astra' ); ?></td>
						<?php foreach ( $compare as $prod_id ): $prod = wc_get_product( $prod_id ); if ( ! $prod ) continue; ?>
							<td><?php echo wp_kses_post( $prod->get_image() ); ?></td>
						<?php endforeach; ?>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Precio', 'jaspi-astra' ); ?></td>
						<?php foreach ( $compare as $prod_id ): $prod = wc_get_product( $prod_id ); if ( ! $prod ) continue; ?>
							<td><?php echo wp_kses_post( $prod->get_price_html() ); ?></td>
						<?php endforeach; ?>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Disponibilidad', 'jaspi-astra' ); ?></td>
						<?php foreach ( $compare as $prod_id ): $prod = wc_get_product( $prod_id ); if ( ! $prod ) continue; ?>
							<td><?php echo $prod->is_in_stock() ? esc_html__( 'En stock', 'jaspi-astra' ) : esc_html__( 'Agotado', 'jaspi-astra' ); ?></td>
						<?php endforeach; ?>
					</tr>
					<tr>
						<td><?php esc_html_e( 'SKU', 'jaspi-astra' ); ?></td>
						<?php foreach ( $compare as $prod_id ): $prod = wc_get_product( $prod_id ); if ( ! $prod ) continue; ?>
							<td><?php echo esc_html( $prod->get_sku() ); ?></td>
						<?php endforeach; ?>
					</tr>
					<tr>
						<td><?php esc_html_e( 'Categoría', 'jaspi-astra' ); ?></td>
						<?php foreach ( $compare as $prod_id ): $terms = get_the_terms( $prod_id, 'product_cat' ); $cat = ( $terms && ! is_wp_error( $terms ) ) ? esc_html( $terms[0]->name ) : ''; ?>
							<td><?php echo $cat; ?></td>
						<?php endforeach; ?>
					</tr>
				</tbody>
							<tfoot>
								<tr>
									<td><?php esc_html_e( 'Acciones', 'jaspi-astra' ); ?></td>
									<?php foreach ( $compare as $prod_id ): $prod = wc_get_product( $prod_id ); if ( ! $prod ) continue; ?>
										<td><a class="button jaspi-compare-show" href="<?php echo esc_url( get_permalink( $prod_id ) ); ?>"><?php esc_html_e( 'Mostrar', 'jaspi-astra' ); ?></a></td>
									<?php endforeach; ?>
								</tr>
							</tfoot>
					</table>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'jaspi_compare', 'jaspi_compare_shortcode' );

/**
 * Merge favorites from cookie into usermeta when a user logs in.
 * This keeps guest favorites when the user authenticates.
 */
function jaspi_merge_cookie_to_usermeta_on_login( $user_login, $user ) {
	if ( empty( $_COOKIE['jaspi_favs'] ) ) {
		return;
	}

	$cookie = json_decode( wp_unslash( $_COOKIE['jaspi_favs'] ), true );
	if ( ! is_array( $cookie ) ) {
		return;
	}

	$cookie = array_map( 'intval', $cookie );
	$user_id = is_object( $user ) && isset( $user->ID ) ? (int) $user->ID : 0;
	if ( ! $user_id ) {
		return;
	}

	$existing = get_user_meta( $user_id, 'jaspi_favorites', true );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	$merged = array_values( array_unique( array_merge( $existing, $cookie ) ) );
	update_user_meta( $user_id, 'jaspi_favorites', $merged );

	// Clear cookie
	setcookie( 'jaspi_favs', '', time() - HOUR_IN_SECONDS, '/' );
	// also unset in PHP global for immediate requests
	unset( $_COOKIE['jaspi_favs'] );
}
add_action( 'wp_login', 'jaspi_merge_cookie_to_usermeta_on_login', 10, 2 );

function jaspi_toggle_favorite_ajax() {
	check_ajax_referer( 'jaspi_favs_nonce', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => 'Producto inválido' ), 400 );
	}

	$favorites = jaspi_get_user_favorites();
	if ( in_array( $product_id, $favorites, true ) ) {
		// remove
		$favorites = array_values( array_diff( $favorites, array( $product_id ) ) );
		$action = 'removed';
	} else {
		// add
		$favorites[] = $product_id;
		$favorites = array_values( array_unique( $favorites ) );
		$action = 'added';
	}

	// Persist for logged in users
	if ( is_user_logged_in() ) {
		jaspi_set_user_favorites( $favorites );
	}

	// Return updated favorites (client will set cookie for guests)
	wp_send_json_success( array( 'favorites' => $favorites, 'count' => count( $favorites ), 'action' => $action ) );
}
add_action( 'wp_ajax_jaspi_toggle_favorite', 'jaspi_toggle_favorite_ajax' );
add_action( 'wp_ajax_nopriv_jaspi_toggle_favorite', 'jaspi_toggle_favorite_ajax' );

function jaspi_get_favorites_ajax() {
	$favs = jaspi_get_user_favorites();
	wp_send_json_success( array( 'favorites' => $favs, 'count' => count( $favs ) ) );
}
add_action( 'wp_ajax_jaspi_get_favorites', 'jaspi_get_favorites_ajax' );
add_action( 'wp_ajax_nopriv_jaspi_get_favorites', 'jaspi_get_favorites_ajax' );

function jaspi_clear_favorites_ajax() {
	check_ajax_referer( 'jaspi_favs_nonce', 'nonce' );

	// Clear server-side for logged users
	if ( is_user_logged_in() ) {
		$user_id = get_current_user_id();
		update_user_meta( $user_id, 'jaspi_favorites', array() );
	}

	// Clear cookie for guests and in any case for client
	setcookie( 'jaspi_favs', '', time() - HOUR_IN_SECONDS, '/' );
	if ( isset( $_COOKIE['jaspi_favs'] ) ) {
		unset( $_COOKIE['jaspi_favs'] );
	}

	wp_send_json_success( array( 'favorites' => array(), 'count' => 0 ) );
}
add_action( 'wp_ajax_jaspi_clear_favorites', 'jaspi_clear_favorites_ajax' );
add_action( 'wp_ajax_nopriv_jaspi_clear_favorites', 'jaspi_clear_favorites_ajax' );

function jaspi_render_fav_button( $position = '' ) {
	global $product;
	if ( ! $product ) {
		return;
	}
	$product_id = $product->get_id();
	$favorites = jaspi_get_user_favorites();
	$is_fav = in_array( $product_id, $favorites, true );

	// Render differently for single product (full theme button + label)
	if ( is_product() ) {
		$icon = '<span class="jaspi-fav-heart" aria-hidden="true">♥</span>';
		$btn_class = 'button jaspi-action-button jaspi-fav-btn' . ( $is_fav ? ' is-fav is-active' : '' );
		$label = $is_fav ? esc_html__( 'En favoritos', 'jaspi-astra' ) : esc_html__( 'Favoritos', 'jaspi-astra' );
		echo '<button type="button" class="' . esc_attr( $btn_class ) . '" data-product-id="' . esc_attr( $product_id ) . '" aria-pressed="' . ( $is_fav ? 'true' : 'false' ) . '">' . $icon . ' <span class="jaspi-action-label-text">' . esc_html( $label ) . '</span></button>';
		return;
	}

	// For product loop: small theme-styled button (inline) so it sits side-by-side with other actions
	$icon = '<span class="jaspi-fav-heart" aria-hidden="true">♥</span>';
	$btn_class = 'button jaspi-action-button jaspi-action-small jaspi-fav-btn' . ( $is_fav ? ' is-fav is-active' : '' );
	echo '<button type="button" class="' . esc_attr( $btn_class ) . '" data-product-id="' . esc_attr( $product_id ) . '" aria-pressed="' . ( $is_fav ? 'true' : 'false' ) . '">' . $icon . '</button>';
}

add_action( 'woocommerce_after_shop_loop_item', 'jaspi_render_fav_button', 12 );
add_action( 'woocommerce_single_product_summary', 'jaspi_render_fav_button', 31 );

/**
 * Render compare button on product loop and single product
 */
function jaspi_render_compare_button( $position = '' ) {
	global $product;
	if ( ! $product ) {
		return;
	}
	$product_id = $product->get_id();
	$compare = function_exists( 'jaspi_get_user_compare' ) ? jaspi_get_user_compare() : array();
	$is_compare = in_array( $product_id, $compare, true );

	$icon_markup = function_exists( 'jaspi_get_flat_icon' ) ? jaspi_get_flat_icon( 'compare' ) : '';
	// Provide a visible fallback if no SVG helper exists
	if ( empty( $icon_markup ) ) {
		$icon_markup = '⇄';
	}
	$icon = '<span class="jaspi-compare-icon" aria-hidden="true">' . $icon_markup . '</span>';

	// Single product: render full theme-styled button with label
	if ( is_product() ) {
		$btn_class = 'button jaspi-action-button jaspi-compare-btn' . ( $is_compare ? ' is-compare is-active' : '' );
		echo '<button type="button" class="' . esc_attr( $btn_class ) . '" data-product-id="' . esc_attr( $product_id ) . '" aria-pressed="' . ( $is_compare ? 'true' : 'false' ) . '">' . $icon . ' <span class="jaspi-action-label-text">' . ( $is_compare ? esc_html__( 'Eliminar de comparación', 'jaspi-astra' ) : esc_html__( 'Comparar', 'jaspi-astra' ) ) . '</span></button>';
		return;
	}

	// Product loop: small icon-only action button
	$btn_class = 'button jaspi-action-button jaspi-action-small jaspi-compare-btn' . ( $is_compare ? ' is-compare is-active' : '' );
	echo '<button type="button" class="' . esc_attr( $btn_class ) . '" data-product-id="' . esc_attr( $product_id ) . '" aria-pressed="' . ( $is_compare ? 'true' : 'false' ) . '">' . $icon . '</button>';
}

add_action( 'woocommerce_after_shop_loop_item', 'jaspi_render_compare_button', 13 );
add_action( 'woocommerce_single_product_summary', 'jaspi_render_compare_button', 36 );

/**
 * Wrapper start/end for inline action buttons in loop
 */
function jaspi_actions_wrapper_start() {
	echo '<div class="jaspi-actions-wrapper">';
}
function jaspi_actions_wrapper_end() {
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item', 'jaspi_actions_wrapper_start', 11 );
add_action( 'woocommerce_after_shop_loop_item', 'jaspi_actions_wrapper_end', 14 );

function jaspi_favorites_shortcode( $atts ) {
	$favorites = jaspi_get_user_favorites();
	if ( empty( $favorites ) ) {
		return '<p>' . esc_html__( 'No tienes favoritos aún.', 'jaspi-astra' ) . '</p>';
	}

	ob_start();
	?>
	<div class="jaspi-favorites-list">
		<div class="jaspi-favorites-actions" style="margin-bottom:12px;">
			<button type="button" class="button jaspi-clear-favs"><?php esc_html_e( 'Vaciar lista', 'jaspi-astra' ); ?></button>
		</div>
		<table class="jaspi-favorites-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Producto', 'jaspi-astra' ); ?></th>
					<th><?php esc_html_e( 'Precio', 'jaspi-astra' ); ?></th>
					<th><?php esc_html_e( 'Estado Inventario', 'jaspi-astra' ); ?></th>
					<th><?php esc_html_e( 'Acciones', 'jaspi-astra' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $favorites as $prod_id ):
					$prod = wc_get_product( $prod_id );
					if ( ! $prod ) {
						continue;
					}
					?>
					<tr>
						<td class="jaspi-fav-product">
							<button class="jaspi-remove-fav" data-product-id="<?php echo esc_attr( $prod_id ); ?>">✕</button>
							<a href="<?php echo esc_url( get_permalink( $prod_id ) ); ?>"><?php echo wp_kses_post( $prod->get_image( 'thumbnail' ) ); ?> <span class="jaspi-fav-title"><?php echo esc_html( $prod->get_name() ); ?></span></a>
						</td>
						<td class="jaspi-fav-price"><?php echo wp_kses_post( $prod->get_price_html() ); ?></td>
						<td class="jaspi-fav-stock"><?php echo $prod->is_in_stock() ? '<span class="in-stock">' . esc_html__( 'En stock', 'jaspi-astra' ) . '</span>' : '<span class="out-of-stock">' . esc_html__( 'Agotado', 'jaspi-astra' ) . '</span>'; ?></td>
						<td class="jaspi-fav-actions">
							<a class="button" href="<?php echo esc_url( get_permalink( $prod_id ) ); ?>"><?php esc_html_e( 'Mostrar', 'jaspi-astra' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'jaspi_favorites', 'jaspi_favorites_shortcode' );

function jaspi_render_featured_brands_block($attributes) {
	$selected_brands = isset($attributes['selectedBrands']) ? $attributes['selectedBrands']:array();
	$title = isset($attributes['title'])? $attributes['title']: 'Marcas Destacadas';
	$display_mode = isset($attributes['displayMode'])? $attributes['displayMode']: 'carousel';
	$autoplay_speed = isset($attributes['autoplaySpeed'])? $attributes['autoplaySpeed']: 3000;

	if(empty($selected_brands)) {
		return '';
	}

	// enqueue carousel script only for carousel mode
	if($display_mode === 'carousel') {
		wp_enqueue_script('jaspi-brands-carousel');
	}

	ob_start();
	?>

	<div class="jaspi-featured-brands <?php echo esc_attr('mode-' . $display_mode); ?>" <?php if($display_mode === 'carousel'): ?>data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"<?php endif; ?>>
		<?php if (!empty($title)): ?>
			<h2 class="brands-title"><?php echo esc_html($title); ?></h2>
		<?php endif; ?>
		
		<?php if($display_mode === 'carousel'): ?>
			<div class="brands-carousel-wrapper">
				<button class="carousel-nav carousel-prev" aria-label="Anterior">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>		
				</button>
				<div class="brands-carousel-container">
					<div class="brands-carousel">
						<?php foreach ($selected_brands as $brand_id):
							$brand = get_term($brand_id, 'product_brand');
							if(!$brand || is_wp_error($brand)) {
								continue;
							}
							$thumbnail_id = get_term_meta($brand_id, 'thumbnail_id', true);
							$image_url = $thumbnail_id? wp_get_attachment_url($thumbnail_id) : '';
							$brand_link = get_term_link($brand);
						?>
						<div class="brand-item">
							<a href="<?php echo esc_url($brand_link); ?>" class="brand-link">
								<?php if($image_url): ?>
									<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($brand->name); ?>" class="brand-logo">
								<?php else: ?>
									<span class="brand-name-text"><?php echo esc_html($brand->name); ?></span>
								<?php endif; ?>
							</a>
						</div>
						<?php endforeach; ?>
					</div>
				</div>
				<button class="carousel-nav carousel-next" aria-label="Siguiente">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
						<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>				
				</button>
			</div>
		<?php else: ?>
			<div class="brands-grid">
				<?php foreach ($selected_brands as $brand_id):
					$brand = get_term($brand_id, 'product_brand');
					if(!$brand || is_wp_error($brand)) {
						continue;
					}
					$thumbnail_id = get_term_meta($brand_id, 'thumbnail_id', true);
					$image_url = $thumbnail_id? wp_get_attachment_url($thumbnail_id) : '';
					$brand_link = get_term_link($brand);
				?>
				<div class="brand-item">
					<a href="<?php echo esc_url($brand_link); ?>" class="brand-link">
						<?php if($image_url): ?>
							<img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($brand->name); ?>" class="brand-logo">
						<?php else: ?>
							<span class="brand-name-text"><?php echo esc_html($brand->name); ?></span>
						<?php endif; ?>
					</a>
				</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * END JASPI CUSTOM FEATURED BRANDS BLOCK
 */

/**
 * Footer newsletter: admin settings, AJAX handler and frontend script
 */

function jaspi_footer_newsletter_admin_menu() {
	add_theme_page(
		__( 'Footer Newsletter', 'jaspi-astra' ),
		__( 'Footer Newsletter', 'jaspi-astra' ),
		'manage_options',
		'jaspi-footer-newsletter',
		'jaspi_footer_newsletter_page'
	);
}
add_action( 'admin_menu', 'jaspi_footer_newsletter_admin_menu' );

function jaspi_footer_newsletter_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['jaspi_footer_newsletter_submit'] ) ) {
		check_admin_referer( 'jaspi_footer_newsletter_save', 'jaspi_footer_newsletter_nonce' );

		$form_id = isset( $_POST['jaspi_footer_forminator_form_id'] ) ? absint( $_POST['jaspi_footer_forminator_form_id'] ) : 0;
		$field_name = isset( $_POST['jaspi_footer_forminator_field_name'] ) ? sanitize_text_field( wp_unslash( $_POST['jaspi_footer_forminator_field_name'] ) ) : '';

		update_option( 'jaspi_footer_forminator_form_id', $form_id );
		update_option( 'jaspi_footer_forminator_field_name', $field_name );

		echo '<div class="updated"><p>' . esc_html__( 'Ajustes guardados.', 'jaspi-astra' ) . '</p></div>';
	}

	$forms_options = array();

	if ( class_exists( 'Forminator_API' ) && method_exists( 'Forminator_API', 'get_forms' ) ) {
		$forms = Forminator_API::get_forms();
		if ( is_array( $forms ) ) {
			foreach ( $forms as $f ) {
				$id = 0;
				$title = '';
				if ( is_object( $f ) ) {
					if ( isset( $f->id ) ) {
						$id = $f->id;
					}
					if ( isset( $f->form_id ) ) {
						$id = $f->form_id;
					}
					if ( isset( $f->ID ) ) {
						$id = $f->ID;
					}
					if ( isset( $f->title ) ) {
						$title = $f->title;
					}
					if ( isset( $f->name ) ) {
						$title = $f->name;
					}
				} elseif ( is_array( $f ) ) {
					if ( isset( $f['id'] ) ) {
						$id = $f['id'];
					}
					if ( isset( $f['form_id'] ) ) {
						$id = $f['form_id'];
					}
					if ( isset( $f['ID'] ) ) {
						$id = $f['ID'];
					}
					if ( isset( $f['title'] ) ) {
						$title = $f['title'];
					}
					if ( isset( $f['name'] ) ) {
						$title = $f['name'];
					}
				}

				if ( $id ) {
					$forms_options[ $id ] = $title ? $title : sprintf( esc_html__( 'Form %d', 'jaspi-astra' ), $id );
				}
			}
		}
	} else {
		// Fallback: try to query possible form post types
		$posts = get_posts( array( 'post_type' => 'forminator_forms', 'posts_per_page' => -1 ) );
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $p ) {
				$forms_options[ $p->ID ] = $p->post_title;
			}
		}
	}

	$current_form = (int) get_option( 'jaspi_footer_forminator_form_id', 0 );
	$current_field = (string) get_option( 'jaspi_footer_forminator_field_name', '' );

	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Footer Newsletter (JASPI)', 'jaspi-astra' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'jaspi_footer_newsletter_save', 'jaspi_footer_newsletter_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="jaspi_footer_forminator_form_id"><?php esc_html_e( 'Forminator Form', 'jaspi-astra' ); ?></label></th>
					<td>
						<select name="jaspi_footer_forminator_form_id" id="jaspi_footer_forminator_form_id">
							<option value="0"><?php esc_html_e( '&mdash; Selecciona un formulario &mdash;', 'jaspi-astra' ); ?></option>
							<?php foreach ( $forms_options as $id => $label ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $current_form, $id ); ?>><?php echo esc_html( $label ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Selecciona a qué formulario de Forminator se enviará el email desde el footer.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="jaspi_footer_forminator_field_name"><?php esc_html_e( 'Campo del formulario (field name)', 'jaspi-astra' ); ?></label></th>
					<td>
						<select name="jaspi_footer_forminator_field_name" id="jaspi_footer_forminator_field_name" class="regular-text">
							<?php if ( $current_field ) : ?>
								<option value="<?php echo esc_attr( $current_field ); ?>" selected><?php echo esc_html( $current_field ); ?></option>
							<?php else : ?>
								<option value=""><?php esc_html_e( '&mdash; Selecciona un campo &mdash;', 'jaspi-astra' ); ?></option>
							<?php endif; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Selecciona el campo del formulario que recibirá el correo. Si el listado no aparece, comprueba que Forminator está activo y recarga la página.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>
			</table>
			<p class="submit">
				<button type="submit" name="jaspi_footer_newsletter_submit" class="button button-primary"><?php esc_html_e( 'Guardar ajustes', 'jaspi-astra' ); ?></button>
			</p>
		</form>
	</div>
	<?php
}

function jaspi_enqueue_footer_subscribe_script() {
	wp_enqueue_script(
		'jaspi-footer-subscribe-js',
		get_stylesheet_directory_uri() . '/assets/js/footer-subscribe.js',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		true
	);

	wp_localize_script( 'jaspi-footer-subscribe-js', 'jaspiFooterSubscribe', array(
		'ajax_url'   => admin_url( 'admin-ajax.php' ),
		'nonce'      => wp_create_nonce( 'jaspi_footer_subscribe' ),
		'form_id'    => (int) get_option( 'jaspi_footer_forminator_form_id', 0 ),
		'field_name' => (string) get_option( 'jaspi_footer_forminator_field_name', '' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'jaspi_enqueue_footer_subscribe_script', 30 );

/**
 * Admin scripts for footer newsletter page: fetch fields for selected Forminator form
 */
function jaspi_footer_newsletter_admin_scripts( $hook ) {
	if ( empty( $_GET['page'] ) || 'jaspi-footer-newsletter' !== $_GET['page'] ) {
		return;
	}

	wp_enqueue_script(
		'jaspi-footer-newsletter-admin',
		get_stylesheet_directory_uri() . '/assets/js/footer-newsletter-admin.js',
		array('jquery'),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		true
	);

	wp_localize_script( 'jaspi-footer-newsletter-admin', 'jaspiFooterNewsletterAdmin', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'jaspi_footer_newsletter_fields' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'jaspi_footer_newsletter_admin_scripts' );

/**
 * AJAX: return fields for a Forminator form
 */
function jaspi_get_forminator_fields_ajax() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ), 403 );
	}

	check_ajax_referer( 'jaspi_footer_newsletter_fields', 'nonce' );

	$raw_form_id = isset( $_POST['form_id'] ) ? wp_unslash( $_POST['form_id'] ) : '';
	if ( '' === $raw_form_id ) {
		wp_send_json_error( array( 'message' => 'Missing form id' ), 400 );
	}

	$fields = array();

	if ( class_exists( 'Forminator_API' ) ) {
		// Try to resolve the form by numeric id or by name/slug/title
		$form = null;
		$all_forms = array();

		if ( method_exists( 'Forminator_API', 'get_forms' ) ) {
			$all_forms = Forminator_API::get_forms();
		}

		// If the posted value is numeric, prefer fetching by id
		if ( is_numeric( $raw_form_id ) ) {
			$form_id = absint( $raw_form_id );
			if ( $form_id ) {
				if ( method_exists( 'Forminator_API', 'get_form' ) ) {
					$form = Forminator_API::get_form( $form_id );
				} elseif ( method_exists( 'Forminator_API', 'get_form_by_id' ) ) {
					$form = Forminator_API::get_form_by_id( $form_id );
				}
			}
		}

		// If not found yet, try to match by name/title/slug in the forms list
		if ( null === $form && ! empty( $all_forms ) && is_array( $all_forms ) ) {
			$needle = (string) $raw_form_id;
			foreach ( $all_forms as $f ) {
				$arr = (array) $f;
				$id = isset( $arr['id'] ) ? $arr['id'] : ( isset( $arr['form_id'] ) ? $arr['form_id'] : ( isset( $arr['ID'] ) ? $arr['ID'] : '' ) );
				$name = isset( $arr['name'] ) ? $arr['name'] : ( isset( $arr['slug'] ) ? $arr['slug'] : '' );
				$title = isset( $arr['title'] ) ? $arr['title'] : ( isset( $arr['label'] ) ? $arr['label'] : '' );

				if ( (string) $id === $needle || (string) $name === $needle || (string) $title === $needle ) {
					$form = $f;
					break;
				}
			}
		}

		// robust recursive extractor to find form fields in different Forminator structures
		$collected = array();

		$collect_fields = function ( $node ) use ( & $collect_fields, & $collected ) {
			if ( is_object( $node ) ) {
				$node = (array) $node;
			}

			if ( is_array( $node ) ) {
				// If associative array where keys look like field names and values contain label/key
				$all_keys_are_field_like = true;
				foreach ( $node as $k => $v ) {
					if ( ! is_string( $k ) ) {
						$all_keys_are_field_like = false;
						break;
					}
				}

				// Check for direct map: 'email-1' => array('label'=>...)
				if ( $all_keys_are_field_like ) {
					foreach ( $node as $k => $v ) {
						if ( is_array( $v ) || is_object( $v ) ) {
							$arr = (array) $v;
							if ( isset( $arr['label'] ) || isset( $arr['name'] ) || isset( $arr['type'] ) ) {
								$name = isset( $arr['name'] ) ? $arr['name'] : $k;
								$label = isset( $arr['label'] ) ? $arr['label'] : ( isset( $arr['title'] ) ? $arr['title'] : $k );
								$collected[ $name ] = array( 'name' => $name, 'label' => $label );
								continue;
							}
						}
					}
				}

				// If numeric array of items, check each
				$is_numeric_indexed = array_keys( $node ) === range( 0, count( $node ) - 1 );
				if ( $is_numeric_indexed ) {
					foreach ( $node as $item ) {
						if ( is_array( $item ) || is_object( $item ) ) {
							$arr = (array) $item;
							// common Forminator field shapes: have 'name' and 'label' or 'element' keys
							if ( isset( $arr['name'] ) || isset( $arr['element'] ) || isset( $arr['slug'] ) || isset( $arr['key'] ) ) {
								$name = isset( $arr['name'] ) ? $arr['name'] : ( isset( $arr['key'] ) ? $arr['key'] : ( isset( $arr['slug'] ) ? $arr['slug'] : ( isset( $arr['element'] ) ? $arr['element'] : '' ) ) );
								$label = isset( $arr['label'] ) ? $arr['label'] : ( isset( $arr['title'] ) ? $arr['title'] : $name );
								if ( $name ) {
									$collected[ $name ] = array( 'name' => $name, 'label' => $label );
									continue;
								}
							}
						}

						// recurse deeper
						$collect_fields( $item );
					}
				} else {
					// associative array: recurse into values
					foreach ( $node as $v ) {
						$collect_fields( $v );
					}
				}
			}
		};

		// If Forminator provides a dedicated method to get fields, prefer it (simpler and reliable)
		$resolved_id = isset( $form_id ) ? $form_id : 0;
		if ( empty( $resolved_id ) && isset( $form ) ) {
			// try to extract id from resolved form object/array
			$arrf = (array) $form;
			$resolved_id = isset( $arrf['id'] ) ? absint( $arrf['id'] ) : ( isset( $arrf['form_id'] ) ? absint( $arrf['form_id'] ) : 0 );
		}

		if ( $resolved_id && class_exists( 'Forminator_API' ) && method_exists( 'Forminator_API', 'get_form_fields' ) ) {
			$raw_fields = Forminator_API::get_form_fields( $resolved_id );
			if ( ! is_wp_error( $raw_fields ) && is_array( $raw_fields ) ) {
				foreach ( $raw_fields as $rf ) {
					$r = (array) $rf;
					$name = isset( $r['name'] ) ? $r['name'] : ( isset( $r['element'] ) ? $r['element'] : ( isset( $r['slug'] ) ? $r['slug'] : '' ) );
					$label = isset( $r['label'] ) ? $r['label'] : ( isset( $r['title'] ) ? $r['title'] : ( isset( $r['element_label'] ) ? $r['element_label'] : $name ) );
					if ( $name ) {
						$fields[] = array( 'name' => $name, 'label' => $label );
					}
				}
			}
		}

		// fallback: use recursive collector if get_form_fields didn't yield results
		if ( empty( $fields ) ) {
			if ( $form ) {
				$collect_fields( $form );
			}

			// scan postmeta values for serialized structures that may contain fields
			if ( empty( $collected ) ) {
				$post = get_post( $form_id );
				if ( $post ) {
					$all_meta = get_post_meta( $form_id );
					foreach ( $all_meta as $meta_val ) {
						$maybe = maybe_unserialize( $meta_val[0] );
						$collect_fields( $maybe );
						if ( ! empty( $collected ) ) break;
					}
				}
			}

			// normalize output
			if ( ! empty( $collected ) ) {
				foreach ( $collected as $f ) {
					if ( isset( $f['name'] ) && $f['name'] ) {
						$fields[] = array( 'name' => $f['name'], 'label' => isset( $f['label'] ) ? $f['label'] : $f['name'] );
					}
				}
			}
		}

		// fallback: try to read post meta structure
		if ( empty( $fields ) ) {
			$post = get_post( $form_id );
			if ( $post ) {
				$meta = get_post_meta( $form_id );
				foreach ( $meta as $value ) {
					$r = $extract( maybe_unserialize( $value[0] ) );
					if ( ! empty( $r ) ) {
						$fields = $r;
						break;
					}
				}
			}
		}
	}

	if ( empty( $fields ) ) {
		wp_send_json_error( array( 'message' => 'No fields found' ), 404 );
	}

	// normalize: ensure name and label present
	$out = array();
	foreach ( $fields as $f ) {
		$out[] = array( 'name' => isset( $f['name'] ) ? $f['name'] : '', 'label' => isset( $f['label'] ) ? $f['label'] : $f['name'] );
	}

	wp_send_json_success( array( 'fields' => $out ) );
}
add_action( 'wp_ajax_jaspi_get_forminator_fields', 'jaspi_get_forminator_fields_ajax' );

function jaspi_footer_subscribe_ajax() {
	check_ajax_referer( 'jaspi_footer_subscribe', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Correo inválido', 'jaspi-astra' ) ), 400 );
	}

	$form_id = (int) get_option( 'jaspi_footer_forminator_form_id', 0 );
	$field_name = (string) get_option( 'jaspi_footer_forminator_field_name', '' );

	if ( ! $form_id || empty( $field_name ) ) {
		wp_send_json_error( array( 'message' => __( 'Formulario no configurado', 'jaspi-astra' ) ), 400 );
	}

	if ( ! class_exists( 'Forminator_API' ) || ! method_exists( 'Forminator_API', 'add_form_entry' ) ) {
		wp_send_json_error( array( 'message' => __( 'Forminator no disponible', 'jaspi-astra' ) ), 500 );
	}

	$entry_meta = array(
		array(
			'name'  => $field_name,
			'value' => $email,
		),
	);

	$entry_id = Forminator_API::add_form_entry( $form_id, $entry_meta );

	if ( $entry_id ) {
		wp_send_json_success( array( 'message' => __( '¡Gracias por suscribirte!', 'jaspi-astra' ) ) );
	}

	wp_send_json_error( array( 'message' => __( 'No se pudo enviar la suscripción', 'jaspi-astra' ) ), 500 );
}
add_action( 'wp_ajax_jaspi_footer_subscribe', 'jaspi_footer_subscribe_ajax' );
add_action( 'wp_ajax_nopriv_jaspi_footer_subscribe', 'jaspi_footer_subscribe_ajax' );