<?php
/**
 * Topbar settings and helpers.
 *
 * @package JASPI Astra
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return default topbar settings.
 *
 * @return array<string, string>
 */
function jaspi_get_topbar_defaults() {
	return array(
		'welcome_text'      => __( '¡Bienvenido a JASPI Tecnología!', 'jaspi-astra' ),
		'highlight_enabled' => '1',
		'highlight_text'    => '',
		'highlight_url'     => '',
		'link_1_label'      => __( 'Contáctenos', 'jaspi-astra' ),
		'link_1_url'        => home_url( '/contacto' ),
		'link_2_label'      => __( 'Distribuidores', 'jaspi-astra' ),
		'link_2_url'        => home_url( '/conviertete-en-distribuidor' ),
	);
}

/**
 * Sanitize a checkbox-like value.
 *
 * @param mixed $value Raw setting value.
 * @return string
 */
function jaspi_sanitize_checkbox( $value ) {
	return ( isset( $value ) && '1' === (string) $value ) ? '1' : '0';
}

/**
 * Sanitize topbar settings before persisting them.
 *
 * @param array<string, mixed> $input Raw settings.
 * @return array<string, string>
 */
function jaspi_sanitize_topbar_settings( $input ) {
	$defaults = jaspi_get_topbar_defaults();
	$input    = is_array( $input ) ? $input : array();

	return array(
		'welcome_text'      => isset( $input['welcome_text'] ) ? sanitize_text_field( $input['welcome_text'] ) : $defaults['welcome_text'],
		'highlight_enabled' => jaspi_sanitize_checkbox( isset( $input['highlight_enabled'] ) ? $input['highlight_enabled'] : '0' ),
		'highlight_text'    => isset( $input['highlight_text'] ) ? sanitize_text_field( $input['highlight_text'] ) : $defaults['highlight_text'],
		'highlight_url'     => isset( $input['highlight_url'] ) ? esc_url_raw( $input['highlight_url'] ) : '',
		'link_1_label'      => isset( $input['link_1_label'] ) ? sanitize_text_field( $input['link_1_label'] ) : '',
		'link_1_url'        => isset( $input['link_1_url'] ) ? esc_url_raw( $input['link_1_url'] ) : '',
		'link_2_label'      => isset( $input['link_2_label'] ) ? sanitize_text_field( $input['link_2_label'] ) : '',
		'link_2_url'        => isset( $input['link_2_url'] ) ? esc_url_raw( $input['link_2_url'] ) : '',
	);
}

/**
 * Register topbar option.
 */
function jaspi_register_topbar_settings() {
	register_setting(
		'jaspi_topbar_settings_group',
		'jaspi_topbar_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'jaspi_sanitize_topbar_settings',
			'default'           => jaspi_get_topbar_defaults(),
		)
	);
}
add_action( 'admin_init', 'jaspi_register_topbar_settings' );

/**
 * Add topbar settings page under Appearance.
 */
function jaspi_topbar_admin_menu() {
	add_theme_page(
		__( 'Topbar del Header', 'jaspi-astra' ),
		__( 'JASPI Topbar', 'jaspi-astra' ),
		'manage_options',
		'jaspi-topbar-settings',
		'jaspi_render_topbar_settings_page'
	);
}
add_action( 'admin_menu', 'jaspi_topbar_admin_menu' );

/**
 * Return merged topbar settings.
 *
 * @return array<string, string>
 */
function jaspi_get_topbar_settings() {
	$defaults = jaspi_get_topbar_defaults();
	$settings = get_option( 'jaspi_topbar_settings', array() );
	$settings = is_array( $settings ) ? $settings : array();

	if ( ! array_key_exists( 'welcome_text', $settings ) ) {
		$settings['welcome_text'] = ! empty( $settings['highlight_text'] ) ? (string) $settings['highlight_text'] : $defaults['welcome_text'];
		$settings['highlight_text'] = '';
	}

	if ( ! array_key_exists( 'highlight_url', $settings ) ) {
		$settings['highlight_url'] = '';
	}

	return wp_parse_args( $settings, $defaults );
}

/**
 * Return highlight payload when enabled and complete enough to render.
 *
 * @param array<string, string>|null $settings Settings array.
 * @return array<string, string>|null
 */
function jaspi_get_topbar_highlight( $settings = null ) {
	$settings = is_array( $settings ) ? $settings : jaspi_get_topbar_settings();
	$text     = isset( $settings['highlight_text'] ) ? trim( (string) $settings['highlight_text'] ) : '';
	$url      = isset( $settings['highlight_url'] ) ? trim( (string) $settings['highlight_url'] ) : '';

	if ( '1' !== (string) $settings['highlight_enabled'] || '' === $text ) {
		return null;
	}

	return array(
		'text' => $text,
		'url'  => $url,
	);
}

/**
 * Build visible topbar links.
 *
 * @param array<string, string>|null $settings Settings array.
 * @return array<int, array<string, string>>
 */
function jaspi_get_topbar_links( $settings = null ) {
	$settings = is_array( $settings ) ? $settings : jaspi_get_topbar_settings();
	$links    = array();

	for ( $index = 1; $index <= 2; $index++ ) {
		$label = isset( $settings[ 'link_' . $index . '_label' ] ) ? trim( (string) $settings[ 'link_' . $index . '_label' ] ) : '';
		$url   = isset( $settings[ 'link_' . $index . '_url' ] ) ? trim( (string) $settings[ 'link_' . $index . '_url' ] ) : '';

		if ( '' === $label || '' === $url ) {
			continue;
		}

		$links[] = array(
			'label' => $label,
			'url'   => $url,
		);
	}

	return $links;
}

/**
 * Enqueue admin assets for the topbar settings page.
 *
 * @return void
 */
function jaspi_topbar_admin_scripts() {
	if ( empty( $_GET['page'] ) || 'jaspi-topbar-settings' !== $_GET['page'] ) {
		return;
	}

	wp_enqueue_script(
		'jaspi-topbar-admin',
		get_stylesheet_directory_uri() . '/assets/js/topbar-admin.js',
		array(),
		CHILD_THEME_JASPI_ASTRA_VERSION,
		true
	);

	wp_localize_script(
		'jaspi-topbar-admin',
		'jaspiTopbarAdmin',
		array(
			'noLinksText'  => __( 'No hay enlaces visibles en este momento.', 'jaspi-astra' ),
			'hiddenText'   => __( 'El mensaje destacado no se mostrará en este momento.', 'jaspi-astra' ),
			'previewLabel' => __( 'Vista previa de la topbar', 'jaspi-astra' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'jaspi_topbar_admin_scripts' );

/**
 * Render topbar settings page.
 */
function jaspi_render_topbar_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings  = jaspi_get_topbar_settings();
	$highlight = jaspi_get_topbar_highlight( $settings );
	$links    = jaspi_get_topbar_links( $settings );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Topbar del Header (JASPI)', 'jaspi-astra' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Configura el mensaje de bienvenida, el mensaje destacado central y los dos enlaces de la barra superior del header. La topbar siempre permanece visible; desactivar el mensaje destacado solo oculta el bloque central.', 'jaspi-astra' ); ?></p>

		<?php settings_errors(); ?>

		<style>
			.jaspi-topbar-preview-shell {
				max-width: 960px;
				margin: 24px 0;
				padding: 20px;
				background: #f6f7f7;
				border: 1px solid #dcdcde;
				border-radius: 8px;
			}

			.jaspi-topbar-preview {
				display: flex;
				align-items: center;
				justify-content: space-between;
				gap: 16px;
				min-height: 38px;
				padding: 0 16px;
				background: #000;
				color: #fff;
				font-size: 13px;
				border-radius: 6px;
			}

			.jaspi-topbar-preview-message {
				margin: 0;
				white-space: nowrap;
			}

			.jaspi-topbar-preview-highlight {
				flex: 1;
				min-width: 0;
				text-align: center;
			}

			.jaspi-topbar-preview-highlight-link,
			.jaspi-topbar-preview-highlight-text {
				display: inline-block;
				max-width: 100%;
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
				color: inherit;
				text-decoration: none;
				font-weight: 600;
			}

			.jaspi-topbar-preview-highlight[hidden] {
				display: block;
				visibility: hidden;
				flex: 1;
			}

			.jaspi-topbar-preview-links {
				display: flex;
				align-items: center;
				justify-content: flex-end;
				gap: 16px;
				flex-wrap: wrap;
				margin-left: auto;
			}

			.jaspi-topbar-preview-links a {
				color: inherit;
				text-decoration: none;
			}

			.jaspi-topbar-preview-note {
				margin: 10px 0 0;
				color: #50575e;
				font-style: italic;
			}
		</style>

		<div class="jaspi-topbar-preview-shell" aria-label="<?php echo esc_attr__( 'Vista previa de la topbar', 'jaspi-astra' ); ?>">
			<h2><?php esc_html_e( 'Vista previa', 'jaspi-astra' ); ?></h2>
			<div class="jaspi-topbar-preview" id="jaspi-topbar-preview">
				<p class="jaspi-topbar-preview-message" id="jaspi-topbar-preview-message"><?php echo esc_html( $settings['welcome_text'] ); ?></p>
				<div class="jaspi-topbar-preview-highlight" id="jaspi-topbar-preview-highlight"<?php echo empty( $highlight ) ? ' hidden' : ''; ?>>
					<a class="jaspi-topbar-preview-highlight-link" id="jaspi-topbar-preview-highlight-link"<?php echo ! empty( $highlight['url'] ) ? ' href="' . esc_url( $highlight['url'] ) . '"' : ''; ?>><?php echo ! empty( $highlight['text'] ) ? esc_html( $highlight['text'] ) : ''; ?></a>
				</div>
				<div class="jaspi-topbar-preview-links" id="jaspi-topbar-preview-links">
					<?php foreach ( $links as $link ) : ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<p class="jaspi-topbar-preview-note" id="jaspi-topbar-preview-note"<?php echo ! empty( $links ) ? ' hidden' : ''; ?>><?php esc_html_e( 'No hay enlaces visibles en este momento.', 'jaspi-astra' ); ?></p>
		</div>

		<form action="options.php" method="post">
			<?php settings_fields( 'jaspi_topbar_settings_group' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="jaspi_topbar_welcome_text"><?php esc_html_e( 'Mensaje de bienvenida', 'jaspi-astra' ); ?></label>
					</th>
					<td>
						<input type="text" id="jaspi_topbar_welcome_text" name="jaspi_topbar_settings[welcome_text]" value="<?php echo esc_attr( $settings['welcome_text'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Texto mostrado al lado izquierdo de la topbar.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="jaspi_topbar_highlight_enabled"><?php esc_html_e( 'Mensaje destacado', 'jaspi-astra' ); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" id="jaspi_topbar_highlight_enabled" name="jaspi_topbar_settings[highlight_enabled]" value="1" <?php checked( $settings['highlight_enabled'], '1' ); ?> />
							<?php esc_html_e( 'Mostrar mensaje destacado', 'jaspi-astra' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'La topbar sigue visible aunque esta opción esté desactivada.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="jaspi_topbar_highlight_text"><?php esc_html_e( 'Texto del mensaje', 'jaspi-astra' ); ?></label>
					</th>
					<td>
						<input type="text" id="jaspi_topbar_highlight_text" name="jaspi_topbar_settings[highlight_text]" value="<?php echo esc_attr( $settings['highlight_text'] ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'Texto mostrado en el bloque central de la topbar.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="jaspi_topbar_highlight_url"><?php esc_html_e( 'URL del mensaje destacado', 'jaspi-astra' ); ?></label>
					</th>
					<td>
						<input type="url" id="jaspi_topbar_highlight_url" name="jaspi_topbar_settings[highlight_url]" value="<?php echo esc_attr( $settings['highlight_url'] ); ?>" class="regular-text" placeholder="https://example.com" />
						<p class="description"><?php esc_html_e( 'Opcional. Si se completa, el mensaje destacado central se mostrará como enlace.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="jaspi_topbar_link_1_label"><?php esc_html_e( 'Enlace 1', 'jaspi-astra' ); ?></label>
					</th>
					<td>
						<input type="text" id="jaspi_topbar_link_1_label" name="jaspi_topbar_settings[link_1_label]" value="<?php echo esc_attr( $settings['link_1_label'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr__( 'Texto del enlace', 'jaspi-astra' ); ?>" />
						<br />
						<input type="url" id="jaspi_topbar_link_1_url" name="jaspi_topbar_settings[link_1_url]" value="<?php echo esc_attr( $settings['link_1_url'] ); ?>" class="regular-text" placeholder="https://example.com" />
						<p class="description"><?php esc_html_e( 'Si el texto o la URL quedan vacíos, el enlace no se mostrará en frontend.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="jaspi_topbar_link_2_label"><?php esc_html_e( 'Enlace 2', 'jaspi-astra' ); ?></label>
					</th>
					<td>
						<input type="text" id="jaspi_topbar_link_2_label" name="jaspi_topbar_settings[link_2_label]" value="<?php echo esc_attr( $settings['link_2_label'] ); ?>" class="regular-text" placeholder="<?php echo esc_attr__( 'Texto del enlace', 'jaspi-astra' ); ?>" />
						<br />
						<input type="url" id="jaspi_topbar_link_2_url" name="jaspi_topbar_settings[link_2_url]" value="<?php echo esc_attr( $settings['link_2_url'] ); ?>" class="regular-text" placeholder="https://example.com" />
						<p class="description"><?php esc_html_e( 'La lógica de visualización es la misma: sin texto o sin URL, se oculta.', 'jaspi-astra' ); ?></p>
					</td>
				</tr>
			</table>

			<?php submit_button( __( 'Guardar ajustes', 'jaspi-astra' ) ); ?>
		</form>
	</div>
	<?php
}