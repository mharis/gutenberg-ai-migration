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
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
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

	/**
	 * Enqueue block editor assets.
	 */
	public function enqueue_block_editor_assets() {
		$asset_file = GBAIMIG_PLUGIN_DIR . '/build/gutenberg-ai-migration.asset.php';
		
		if ( file_exists( $asset_file ) ) {
			$asset = require $asset_file;
			
			wp_enqueue_script(
				'gutenberg-ai-migration-editor',
				GBAIMIG_PLUGIN_URL . '/build/gutenberg-ai-migration.js',
				$asset['dependencies'],
				$asset['version'],
				true
			);
			
			wp_set_script_translations(
				'gutenberg-ai-migration-editor',
				'gutenberg-ai-migration',
				GBAIMIG_PLUGIN_DIR . '/languages'
			);
		}
	}

	/**
	 * Register REST API routes for the plugin.
	 */
	public function register_rest_routes() {
		register_rest_route( 'gutenberg-ai-migration/v1', '/api-key', [
			'methods' => 'GET',
			'callback' => [ $this, 'get_api_key' ],
			'permission_callback' => [ $this, 'check_permissions' ],
		] );

		register_rest_route( 'gutenberg-ai-migration/v1', '/api-key', [
			'methods' => 'POST',
			'callback' => [ $this, 'save_api_key' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args' => [
				'apiKey' => [
					'required' => true,
					'type' => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );

		register_rest_route( 'gutenberg-ai-migration/v1', '/validate-key', [
			'methods' => 'POST',
			'callback' => [ $this, 'validate_api_key' ],
			'permission_callback' => [ $this, 'check_permissions' ],
			'args' => [
				'apiKey' => [
					'required' => true,
					'type' => 'string',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	/**
	 * Check if user has permission to access API endpoints.
	 */
	public function check_permissions() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get saved API key.
	 */
	public function get_api_key( $request ) {
		$api_key = get_option( 'gutenberg_ai_migration_openai_key', '' );
		
		return [
			'success' => true,
			'data' => [
				'apiKey' => $api_key,
			],
		];
	}

	/**
	 * Save API key.
	 */
	public function save_api_key( $request ) {
		$api_key = $request->get_param( 'apiKey' );
		
		if ( empty( $api_key ) ) {
			return new \WP_Error( 'missing_api_key', __( 'API key is required.', 'gutenberg-ai-migration' ), [ 'status' => 400 ] );
		}

		$saved = update_option( 'gutenberg_ai_migration_openai_key', $api_key );
		
		if ( $saved ) {
			return [
				'success' => true,
				'message' => __( 'API key saved successfully.', 'gutenberg-ai-migration' ),
			];
		} else {
			return new \WP_Error( 'save_failed', __( 'Failed to save API key.', 'gutenberg-ai-migration' ), [ 'status' => 500 ] );
		}
	}

	/**
	 * Validate API key with OpenAI.
	 */
	public function validate_api_key( $request ) {
		$api_key = $request->get_param( 'apiKey' );
		
		if ( empty( $api_key ) ) {
			return new \WP_Error( 'missing_api_key', __( 'API key is required.', 'gutenberg-ai-migration' ), [ 'status' => 400 ] );
		}

		// Make a test request to OpenAI API
		$response = wp_remote_get( 'https://api.openai.com/v1/models', [
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json',
			],
			'timeout' => 30,
		] );

		if ( is_wp_error( $response ) ) {
			return [
				'success' => false,
				'message' => __( 'Failed to connect to OpenAI API.', 'gutenberg-ai-migration' ),
			];
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		
		if ( $response_code === 200 ) {
			return [
				'success' => true,
				'message' => __( 'API key is valid.', 'gutenberg-ai-migration' ),
			];
		} elseif ( $response_code === 401 ) {
			return [
				'success' => false,
				'message' => __( 'Invalid API key. Please check your key and try again.', 'gutenberg-ai-migration' ),
			];
		} else {
			return [
				'success' => false,
				'message' => sprintf( 
					__( 'API validation failed with status code: %d', 'gutenberg-ai-migration' ), 
					$response_code 
				),
			];
		}
	}
}