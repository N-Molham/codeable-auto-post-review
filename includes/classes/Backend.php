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
	protected $settings_slug;

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
		$this->settings_slug = 'capr_settings';
		$this->settings      = new Settings_API();

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
			$this->settings_slug . '_page',
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
		$this->settings->set_sections( (array) apply_filters( 'capr_settings_sections', [
			[
				'id'    => $this->settings_slug . '_general',
				'title' => __( 'General', CAPR_DOMAIN ),
			],
			[
				'id'    => $this->settings_slug . '_twitter',
				'title' => __( 'Twitter', CAPR_DOMAIN ),
			],
		] ) );

		$this->settings->set_fields( (array) apply_filters( 'capr_settings_fields', [
			$this->settings_slug . '_general' => [
				'enabled'          => [
					'name'              => 'enabled',
					'label'             => __( 'Enabled', CAPR_DOMAIN ),
					'type'              => 'checkbox',
					'default'           => 'off',
					'desc'              => 'Yes',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'codeable_user_id' => [
					'name'              => 'codeable_user_id',
					'label'             => __( 'Codeable User/Expert ID', CAPR_DOMAIN ),
					'type'              => 'text',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
			$this->settings_slug . '_twitter' => [
				'api_key'        => [
					'name'              => 'api_key',
					'label'             => __( 'Consumer Key (API Key)', CAPR_DOMAIN ),
					'type'              => 'text',
					'input_class'       => 'code',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'api_secret'     => [
					'name'              => 'api_secret',
					'label'             => __( 'Consumer Secret (API Secret)', CAPR_DOMAIN ),
					'type'              => 'text',
					'size'              => 'large',
					'input_class'       => 'code',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				],
				'callback_url'   => [
					'name'  => 'callback_url',
					'label' => __( 'Twitter Callback URL', CAPR_DOMAIN ),
					'type'  => 'html',
					'desc'  => '<input type="text" readonly="readonly" class="large-text code" value="' .
					           esc_url( capr_social_media()->get_twitter_callback_url() ) .
					           '" onfocus="this.select();" />',
				],
				'auth_button'    => [
					'name'     => 'auth_button',
					'label'    => __( 'Authentication', CAPR_DOMAIN ),
					'type'     => 'button',
					'callback' => [ capr_social_media(), 'twitter_authentication_button' ],
				],
				'tweet_template' => [
					'name'    => 'tweet_template',
					'label'   => __( 'Review Tweet Template', CAPR_DOMAIN ),
					'type'    => 'textarea',
					'default' => capr_view( 'tweet_template', null, true ),
					'size'    => 'large',
					'desc'    => capr_view( 'tweet_desc', null, true ),
					'rows'    => 3,
				],
			],
		] ) );
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
		return $this->settings->get_option( $field, $this->settings_slug . '_' . $section );
	}

	/**
	 * @return string
	 */
	public function get_settings_slug() {
		return $this->settings_slug;
	}
}
