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