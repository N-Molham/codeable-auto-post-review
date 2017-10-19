<?php namespace Codeable_AutoPost_Review;

/**
 * Codeable logic
 *
 * @package Codeable_AutoPost_Review
 */
class Codeable extends Component {
	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		// Plugin activation
		register_activation_hook( CAPR_MAIN_FILE, [ &$this, 'register_cron_job' ] );
		add_action( 'admin_action_capr_setup_cron', [ &$this, 'register_cron_job' ] );

		// cron job callback
		add_action( 'capr_check_reviews', [ &$this, 'check_for_new_reviews' ] );
		add_action( 'admin_action_capr_run_cron', create_function( null, 'do_action("capr_check_reviews");' ) );

		add_filter( 'cron_schedules', [ &$this, 'register_cron_custom_schedule' ] );
	}

	/**
	 * @param array $schedule
	 *
	 * @return array
	 */
	public function register_cron_custom_schedule( $schedule ) {

		$schedule['twicehourly'] = [
			'interval' => HOUR_IN_SECONDS,
			'display'  => __( 'Twice Hourly', CAPR_DOMAIN ),
		];

		return $schedule;
	}

	/**
	 * @return void
	 */
	public function check_for_new_reviews() {

		$user_id = capr_backend()->get_settings( 'codeable_user_id' );

		// skip if user ID not set!
		if ( '' === $user_id || empty( $user_id ) ) {
			return;
		}

		// fetch reviews
		$response = wp_safe_remote_get( 'https://api.codeable.io/users/' . $user_id . '/reviews' );

		// HTTP error!
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$reviews = get_transient( 'capr_reviews' );
		if ( false === $reviews ) {
			// cache for half an hour
			$reviews = @json_decode( wp_remote_retrieve_body( $response ) );
			set_transient( 'capr_reviews', $reviews, 30 * MINUTE_IN_SECONDS );
		}

		if ( is_array( $reviews ) ) {
			do_action( 'capr_latest_reviews', $reviews );
		}
	}

	/**
	 * @return void
	 */
	public function register_cron_job() {
		wp_schedule_event( time(), 'twicehourly', 'capr_check_reviews' );
	}
}
