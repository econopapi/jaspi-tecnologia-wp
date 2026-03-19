<?php
/**
 * Custom header template part.
 *
 * @package JASPI Astra
 */

$has_categories_menu = ! empty( $args['has_categories_menu'] );
$has_quick_links     = ! empty( $args['has_quick_links'] );
$topbar_settings     = jaspi_get_topbar_settings();
$topbar_highlight    = jaspi_get_topbar_highlight( $topbar_settings );
$topbar_links        = jaspi_get_topbar_links( $topbar_settings );
$header_classes      = empty( $topbar_highlight ) ? 'jaspi-header jaspi-header--no-mobile-topbar' : 'jaspi-header';
$topbar_classes      = empty( $topbar_highlight ) ? 'jaspi-header-topbar jaspi-header-topbar--mobile-hidden' : 'jaspi-header-topbar';
// Etiquetas dinámicas según estado de sesión
$account_label_action = is_user_logged_in() ? __( 'Mi cuenta', 'jaspi-astra' ) : __( 'Iniciar sesión', 'jaspi-astra' );
?>
<header class="<?php echo esc_attr( $header_classes ); ?>" role="banner">
	<div class="<?php echo esc_attr( $topbar_classes ); ?>">
		<div class="jaspi-header-container">
			<p class="jaspi-header-topbar-left"><?php echo esc_html( $topbar_settings['welcome_text'] ); ?></p>
			<div class="jaspi-header-topbar-center"<?php echo empty( $topbar_highlight ) ? ' hidden' : ''; ?>>
				<?php if ( ! empty( $topbar_highlight['url'] ) ) : ?>
					<a class="jaspi-header-topbar-highlight" href="<?php echo esc_url( $topbar_highlight['url'] ); ?>"><?php echo esc_html( $topbar_highlight['text'] ); ?></a>
				<?php elseif ( ! empty( $topbar_highlight['text'] ) ) : ?>
					<span class="jaspi-header-topbar-highlight"><?php echo esc_html( $topbar_highlight['text'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="jaspi-header-topbar-right">
				<?php foreach ( $topbar_links as $topbar_link ) : ?>
					<a href="<?php echo esc_url( $topbar_link['url'] ); ?>"><?php echo esc_html( $topbar_link['label'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</div>

	<div class="jaspi-header-desktop">
		<div class="jaspi-header-container jaspi-header-main-row">
			<div class="jaspi-header-logo">
				<?php jaspi_render_logo(); ?>
			</div>
			<form class="jaspi-header-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="jaspi-header-search-input"><?php esc_html_e( 'Buscar', 'jaspi-astra' ); ?></label>
				<input id="jaspi-header-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'Buscar ...', 'jaspi-astra' ); ?>" />
				<button type="submit" aria-label="<?php esc_attr_e( 'Buscar', 'jaspi-astra' ); ?>">🔍</button>
			</form>
				<div class="jaspi-header-actions" aria-label="<?php esc_attr_e( 'Accesos rápidos', 'jaspi-astra' ); ?>">
				<?php jaspi_render_action_link( '/favoritos', __( 'Favoritos', 'jaspi-astra' ), 'favorites', 'jaspi-action-link' ); ?>
				<?php jaspi_render_action_link( '/comparar', __( 'Comparar', 'jaspi-astra' ), 'compare', 'jaspi-action-link' ); ?>
				<?php jaspi_render_action_link( '/carrito', __( 'Carrito', 'jaspi-astra' ), 'cart', 'jaspi-action-link' ); ?>
				<?php jaspi_render_action_link( '/mi-cuenta', $account_label_action, 'account', 'jaspi-action-link' ); ?>
			</div>
		</div>

		<nav class="jaspi-header-nav" aria-label="<?php esc_attr_e( 'Menú principal', 'jaspi-astra' ); ?>">
			<div class="jaspi-header-container jaspi-header-nav-row">
				<div class="jaspi-categories-trigger">
					<button type="button" class="jaspi-categories-button" aria-expanded="false" aria-controls="jaspi-categories-dropdown">
						<span>☰</span>
						<?php esc_html_e( 'Buscar categoría', 'jaspi-astra' ); ?>
					</button>
					<div id="jaspi-categories-dropdown" class="jaspi-categories-dropdown" hidden>
						<?php
						if ( $has_categories_menu ) {
							wp_nav_menu(
								array(
									'theme_location' => 'jaspi-categories-menu',
									'container'      => false,
									'menu_class'     => 'jaspi-categories-list',
									'fallback_cb'    => false,
									'depth'          => 1,
								)
							);
						} else {
							jaspi_render_fallback_list( jaspi_get_fallback_categories(), 'jaspi-categories-list' );
						}
						?>
					</div>
				</div>

				<div class="jaspi-quick-links">
					<?php
					if ( $has_quick_links ) {
						wp_nav_menu(
							array(
								'theme_location' => 'jaspi-quick-links-menu',
								'container'      => false,
								'menu_class'     => 'jaspi-quick-links-list',
								'fallback_cb'    => false,
								'depth'          => 3,
							)
						);
					} else {
						jaspi_render_fallback_list( jaspi_get_fallback_quick_links(), 'jaspi-quick-links-list' );
					}
					?>
				</div>
			</div>
		</nav>
	</div>

	<div class="jaspi-header-mobile">
		<div class="jaspi-header-container jaspi-mobile-main">
			<button type="button" class="jaspi-mobile-menu-toggle" aria-expanded="false" aria-controls="jaspi-mobile-panel" aria-label="<?php esc_attr_e( 'Abrir menú', 'jaspi-astra' ); ?>">☰</button>
			<div class="jaspi-mobile-logo"><?php jaspi_render_logo(); ?></div>
			<div class="jaspi-mobile-actions">
				<?php jaspi_render_action_link( '/mi-cuenta', $account_label_action, 'account', 'jaspi-action-link jaspi-action-link-mobile' ); ?>
				<?php jaspi_render_action_link( '/carrito', __( 'Carrito', 'jaspi-astra' ), 'cart', 'jaspi-action-link jaspi-action-link-mobile' ); ?>
			</div>
		</div>

		<div class="jaspi-header-container jaspi-mobile-search-row">
			<form class="jaspi-header-search jaspi-mobile-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="jaspi-mobile-search-input"><?php esc_html_e( 'Buscar', 'jaspi-astra' ); ?></label>
				<input id="jaspi-mobile-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'Buscar', 'jaspi-astra' ); ?>" />
				<button type="submit" aria-label="<?php esc_attr_e( 'Buscar', 'jaspi-astra' ); ?>">🔍</button>
			</form>
		</div>

		<div id="jaspi-mobile-panel" class="jaspi-mobile-panel" hidden>
			<div class="jaspi-mobile-panel-inner" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menú principal móvil', 'jaspi-astra' ); ?>">
				<div class="jaspi-mobile-panel-header">
					<button type="button" class="jaspi-mobile-tab is-active" data-target="menu"><?php esc_html_e( 'Menú', 'jaspi-astra' ); ?></button>
					<button type="button" class="jaspi-mobile-tab" data-target="categories"><?php esc_html_e( 'Categorías', 'jaspi-astra' ); ?></button>
					<button type="button" class="jaspi-mobile-close" aria-label="<?php esc_attr_e( 'Cerrar menú', 'jaspi-astra' ); ?>">✕</button>
				</div>

				<div class="jaspi-mobile-tab-content is-active" data-content="menu">
					<?php
					if ( $has_quick_links ) {
						wp_nav_menu(
							array(
								'theme_location' => 'jaspi-quick-links-menu',
								'container'      => false,
								'menu_class'     => 'jaspi-mobile-list',
								'fallback_cb'    => false,
								'depth'          => 3,
							)
						);
					} else {
						jaspi_render_fallback_list( jaspi_get_fallback_quick_links(), 'jaspi-mobile-list' );
					}
					?>
				</div>

				<div class="jaspi-mobile-tab-content" data-content="categories">
					<p class="jaspi-mobile-all-categories"><a href="#"><?php esc_html_e( 'Ver todas las categorías', 'jaspi-astra' ); ?></a></p>
					<?php
					if ( $has_categories_menu ) {
						wp_nav_menu(
							array(
								'theme_location' => 'jaspi-categories-menu',
								'container'      => false,
								'menu_class'     => 'jaspi-mobile-list jaspi-mobile-categories-list',
								'fallback_cb'    => false,
								'depth'          => 1,
							)
						);
					} else {
						jaspi_render_fallback_list( jaspi_get_fallback_categories(), 'jaspi-mobile-list jaspi-mobile-categories-list' );
					}
					?>
				</div>
			</div>
		</div>
	</div>
</header>
