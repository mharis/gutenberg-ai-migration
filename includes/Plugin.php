<?php
namespace GutenbergAIMigration;

class Plugin {

	/**
	 * @var $instance Plugin Singleton plugin instance
	 */
	public static $instance = null;

	/**
	 * @var array $services The known list of services.
	 */
	public $services = [];

	/**
	 * @var array $admin_helpers Class instances providing features in the admin UI.
	 */
	public $admin_helpers = [];

	/**
	 * Lazy initialize the plugin
	 */
	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new Plugin();
		}

		return self::$instance;
	}

	/**
	 * Setup WP hooks
	 */
	public function enable() {
		add_action( 'init', [ $this, 'init' ], 20 );
		add_action( 'init', [ $this, 'i18n' ] );
	}

	/**
	 * Initializes the plugin modules and support objects.
	 */
	public function init() {
	}

	/**
	 * Load translations.
	 */
	public function i18n() {
		load_plugin_textdomain( 'gutenberg-ai-migration', false, GBAIMIG_PLUGIN_DIR . '/languages' );
	}

	/**
	 * Initialize the Services.
	 */
	public function init_services() {
	}

	/**
	 * Initiates classes providing admin features.
	 *
	 * @since 1.4.0
	 */
	public function init_admin_helpers() {
	}
}