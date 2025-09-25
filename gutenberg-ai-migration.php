<?php
/**
 * Plugin Name:       Gutenberg AI Migration
 * Plugin URI:        https://github.com/10up/gutenberg-ai-migration
 * Update URI:        false
 * Description:       A plugin to help migrate content from HTML/Avada/Elementor & etc. to Gutenberg with AI.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      8.3
 * Author:            Haris Zulfiqar
 * Author URI:        https://hariszulfiqar.com
 * License:           GPLv2
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       gutenberg-ai-migration
 * Domain Path:       /languages
 */

// Define plugin constants
if ( ! defined( 'GBAIMIG_PLUGIN_FILE' ) ) {
	define( 'GBAIMIG_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'GBAIMIG_PLUGIN_DIR' ) ) {
	define( 'GBAIMIG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'GBAIMIG_PLUGIN_URL' ) ) {
	define( 'GBAIMIG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'GBAIMIG_PLUGIN_BASENAME' ) ) {
	define( 'GBAIMIG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'GBAIMIG_VERSION' ) ) {
	define( 'GBAIMIG_VERSION', '1.0.0' );
}

/**
 * Get the minimum version of PHP required by this plugin.
 *
 * @return string Minimum version required.
 */
function gbaimig_minimum_php_requirement() {
	return '8.3';
}

/**
 * Whether PHP installation meets the minimum requirements
 *
 * @return bool True if meets minimum requirements, false otherwise.
 */
function gbaimig_site_meets_php_requirements() {
	return version_compare( phpversion(), gbaimig_minimum_php_requirement(), '>=' );
}

// Ensuring our PHP version requirement is met first before loading plugin.
if ( ! gbaimig_site_meets_php_requirements() ) {
	add_action(
		'admin_notices',
		function () {
			?>
			<div class="notice notice-error">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: Minimum required PHP version */
							__( 'Gutenberg AI Migration requires PHP version %s or later. Please upgrade PHP or disable the plugin.', 'gutenberg-ai-migration' ),
							esc_html( gbaimig_minimum_php_requirement() )
						)
					);
					?>
				</p>
			</div>
			<?php
		}
	);
	return;
}

/**
 * Loads the autoloader if possible.
 *
 * @return bool True or false if autoloading was successful.
 */
function gbaimig_autoload() {
	if ( file_exists( GBAIMIG_PLUGIN_DIR . '/vendor/autoload.php' ) ) {
		require_once GBAIMIG_PLUGIN_DIR . '/vendor/autoload.php';

		return true;
	} else {
		error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			sprintf( 'Warning: Composer not setup in %s', GBAIMIG_PLUGIN_DIR )
		);

		return false;
	}
}

/**
 * Gets the installation message error.
 *
 * Used both in a WP-CLI context and within an admin notice.
 *
 * @return string
 */
function gbaimig_get_error_install_message() {
	return esc_html__( 'Error: Please run $ composer install in the Gutenberg AI Migration plugin directory.', 'gutenberg-ai-migration' );
}

/**
 * Plugin code entry point.
 *
 * If autoloading failed an admin notice is shown and logged to
 * the PHP error_log.
 */
function gbaimig_autorun() {
	if ( gbaimig_autoload() ) {
		$plugin = \GutenbergAIMigration\Plugin::get_instance();
        $plugin->enable();
	} else {
		add_action( 'admin_notices', 'gbaimig_autoload_notice' );
	}
}

/**
 * Generate a notice if autoload fails.
 */
function gbaimig_autoload_notice() {
	printf( '<div class="%1$s"><p>%2$s</p></div>', 'notice notice-error', gbaimig_get_error_install_message() ); // @codingStandardsIgnoreLine Text is escaped in calling function already.
	error_log( gbaimig_get_error_install_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

/**
 * Run functionality on plugin activation.
 */
function gbaimig_activation() {
	set_transient( 'gbaimig_activation_notice', 'gbaimig', HOUR_IN_SECONDS );
}
register_activation_hook( __FILE__, 'gbaimig_activation' );

gbaimig_autorun();