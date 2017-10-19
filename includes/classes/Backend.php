<?php namespace Codeable_AutoPost_Review;

use Codeable_AutoPost_Review\Libraries\Settings_API;

/**
 * Backend logic
 *
 * @package Codeable_AutoPost_Review
 */
class Backend extends Component {
	/**
	 * Plugin's settings page slug
	 *
	 * @var string
	 */
	protected $settings_prefix;

	/**
	 * Settings API instance
	 *
	 * @var Settings_API
	 */
	protected $settings;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// vars
		$this->settings_prefix = 'capr_settings_';
		$this->settings        = new Settings_API();

		// System initialized
		add_action( 'init', [ &$this, 'register_settings_fields' ] );

		// Dashboard admin initialization
		add_action( 'admin_init', [ &$this, 'settings_init' ] );

		// Dashboard admin menu
		add_action( 'admin_menu', [ &$this, 'register_settings_menu_page' ] );
	}

	/**
	 * Plugin's settings initialization
	 *
	 * @return void
	 */
	public function settings_init() {
		$this->settings->admin_init();
	}

	/**
	 * Register plugin's settings page
	 *
	 * @return void
	 */
	public function register_settings_menu_page() {
		add_options_page(
			__( 'Codeable Auto-Post Review', CAPR_DOMAIN ),
			__( 'Codeable Auto-Post Review', CAPR_DOMAIN ),
			'manage_options',
			$this->settings_prefix . 'page',
			[ &$this, 'settings_page_render' ]
		);
	}

	/**
	 * Plugin's settings page render
	 *
	 * @return void
	 */
	public function settings_page_render() {
		echo '<div class="wrap">';
		$this->settings->show_navigation();
		$this->settings->show_forms();
		echo '</div>';
	}

	/**
	 * @return void
	 */
	public function register_settings_fields() {
		// sections
		$this->settings->set_sections( [
			[
				'id'    => $this->settings_prefix . 'general',
				'title' => __( 'General', CAPR_DOMAIN ),
			],
		] );

		$this->settings->set_fields( [
			$this->settings_prefix . 'general' => [
				'enabled'              => [
					'name'              => 'enabled',
					'label'             => __( 'Enabled', CAPR_DOMAIN ),
					'type'              => 'checkbox',
					'default'           => 'off',
					'desc'              => 'Yes',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'codeable_user_id'     => [
					'name'              => 'codeable_user_id',
					'label'             => __( 'Codeable User/Expert ID', CAPR_DOMAIN ),
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'twitter_callback_url' => [
					'name'  => 'twitter_callback_url',
					'label' => __( 'Twitter Callback URL', CAPR_DOMAIN ),
					'type'  => 'html',
					'desc'  => '<input type="text" readonly="readonly" id="general[twitter_callback_url]" class="large-text code" value="' . esc_url( '' ) . '" onfocus="this.select();" />',
				],
			],
		] );
	}

	/**
	 * Get plugin settings
	 *
	 * @param string $field
	 * @param string $section
	 *
	 * @return mixed
	 */
	public function get_settings( $field, $section = 'general' ) {
		return $this->settings->get_option( $field, $this->settings_prefix . $section );
	}
}
