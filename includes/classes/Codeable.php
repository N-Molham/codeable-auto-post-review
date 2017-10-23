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
			$last_posted_review = $this->get_last_posted_review();
			if ( 0 !== $last_posted_review ) {

				// get reviews after the last one 
				$reviews = array_filter( $reviews, function ( $review ) use ( $last_posted_review ) {
					return $review->id > $last_posted_review;
				} );

			}

			foreach ( $reviews as $review ) {
				/**
				 * @param \stdClass $review
				 */
				do_action( 'capr_latest_review', $review );

				$this->update_last_posted_review( $review->id );
			}
		}
	}

	/**
	 * @return int
	 */
	public function get_last_posted_review() {
		return (int) get_option( 'capr_last_posted_review', 0 );
	}

	/**
	 * @param int $review_id
	 *
	 * @return void
	 */
	public function update_last_posted_review( $review_id ) {

		$last_posted_review = $this->get_last_posted_review();

		if ( $review_id > $last_posted_review ) {
			update_option( 'capr_last_posted_review', $review_id, 'no' );
		}

	}

	/**
	 * @return void
	 */
	public function register_cron_job() {
		wp_schedule_event( time(), 'twicehourly', 'capr_check_reviews' );
	}
}
