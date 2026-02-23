<?php
/**
 * Custom footer template part.
 *
 * @package JASPI Astra
 */

$has_footer_about_menu    = ! empty( $args['has_footer_about_menu'] );
$has_footer_branches_menu = ! empty( $args['has_footer_branches_menu'] );
$has_footer_legal_menu    = ! empty( $args['has_footer_legal_menu'] );
?>
<footer class="jaspi-footer" role="contentinfo">
	<div class="jaspi-footer-main">
		<div class="jaspi-footer-container jaspi-footer-grid">
			<section class="jaspi-footer-brand-column" aria-label="<?php esc_attr_e( 'Información de marca', 'jaspi-astra' ); ?>">
				<div class="jaspi-footer-logo-wrap">
					<?php jaspi_render_logo(); ?>
				</div>
				<p class="jaspi-footer-tagline"><?php esc_html_e( 'Impulsando el desarrollo tecnológico de tu empresa', 'jaspi-astra' ); ?></p>
				<?php jaspi_render_footer_contact_line( 'email', 'ventas@jaspitec.com', 'mailto:ventas@jaspitec.com' ); ?>
				<?php jaspi_render_footer_contact_line( 'phone', '55 5025 7600', 'tel:+525550257600' ); ?>
			</section>

			<section class="jaspi-footer-menu-column" aria-label="<?php esc_attr_e( 'Sobre JASPI', 'jaspi-astra' ); ?>">
				<h3><?php esc_html_e( 'Sobre JASPI', 'jaspi-astra' ); ?></h3>
				<?php
				if ( $has_footer_about_menu ) {
					wp_nav_menu(
						array(
							'theme_location' => 'jaspi-footer-about-menu',
							'container'      => false,
							'menu_class'     => 'jaspi-footer-links-list',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
				} else {
					jaspi_render_fallback_list( jaspi_get_fallback_footer_about_links(), 'jaspi-footer-links-list' );
				}
				?>
			</section>

			<section class="jaspi-footer-menu-column" aria-label="<?php esc_attr_e( 'Asistencia', 'jaspi-astra' ); ?>">
				<h3><?php esc_html_e( 'Asistencia', 'jaspi-astra' ); ?></h3>
				<?php
				if ( $has_footer_branches_menu ) {
					wp_nav_menu(
						array(
							'theme_location' => 'jaspi-footer-branches-menu',
							'container'      => false,
							'menu_class'     => 'jaspi-footer-links-list jaspi-footer-branches-list',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
				} else {
					jaspi_render_fallback_list( jaspi_get_fallback_footer_branches_links(), 'jaspi-footer-links-list jaspi-footer-branches-list' );
				}
				?>
			</section>

			<section class="jaspi-footer-info-column" aria-label="<?php esc_attr_e( 'Pagos y envíos', 'jaspi-astra' ); ?>">
				<h3><?php esc_html_e( 'Pagos y envíos', 'jaspi-astra' ); ?></h3>
				<p><?php esc_html_e( 'Pagos seguros con tus métodos favoritos', 'jaspi-astra' ); ?></p>
				<p><?php esc_html_e( 'Envíos a todo México | Recoge en sucursal con Ocurre', 'jaspi-astra' ); ?></p>
				<div class="jaspi-footer-socials-wrap">
					<strong><?php esc_html_e( 'Síguenos en', 'jaspi-astra' ); ?></strong>
					<div class="jaspi-footer-socials">
						<?php jaspi_render_footer_social_link( 'facebook', '#', __( 'Facebook', 'jaspi-astra' ) ); ?>
						<?php jaspi_render_footer_social_link( 'instagram', '#', __( 'Instagram', 'jaspi-astra' ) ); ?>
						<?php jaspi_render_footer_social_link( 'x', '#', __( 'X', 'jaspi-astra' ) ); ?>
						<?php jaspi_render_footer_social_link( 'linkedin', '#', __( 'LinkedIn', 'jaspi-astra' ) ); ?>
						<?php jaspi_render_footer_social_link( 'youtube', '#', __( 'YouTube', 'jaspi-astra' ) ); ?>
					</div>
				</div>
			</section>
		</div>

		<div class="jaspi-footer-container jaspi-footer-subscribe-row">
			<form class="jaspi-footer-subscribe-form" action="#" method="post">
				<label class="screen-reader-text" for="jaspi-footer-subscribe-email"><?php esc_html_e( 'Correo electrónico', 'jaspi-astra' ); ?></label>
				<input id="jaspi-footer-subscribe-email" type="email" name="email" placeholder="<?php esc_attr_e( 'Ingresa tu correo electrónico', 'jaspi-astra' ); ?>" />
				<button type="submit"><?php esc_html_e( 'Suscríbete', 'jaspi-astra' ); ?></button>
			</form>
		</div>

		<div class="jaspi-footer-container jaspi-footer-bottom-row">
			<div class="jaspi-footer-legal-links">
				<?php
				if ( $has_footer_legal_menu ) {
					wp_nav_menu(
						array(
							'theme_location' => 'jaspi-footer-legal-menu',
							'container'      => false,
							'menu_class'     => 'jaspi-footer-legal-list',
							'fallback_cb'    => false,
							'depth'          => 1,
						)
					);
				} else {
					jaspi_render_fallback_list( jaspi_get_fallback_footer_legal_links(), 'jaspi-footer-legal-list' );
				}
				?>
			</div>
			<p class="jaspi-footer-copyright"><?php echo esc_html( sprintf( __( 'Copyright © %s JASPI Tecnología. Todos los derechos reservados.', 'jaspi-astra' ), gmdate( 'Y' ) ) ); ?></p>
		</div>
	</div>
</footer>
