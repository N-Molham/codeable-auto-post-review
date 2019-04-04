<?php namespace Codeable_AutoPost_Review;

/**
 * AJAX handler
 *
 * @package Codeable_AutoPost_Review
 */
class Ajax_Handler extends Component {

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {

		parent::init();

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$action = filter_var( isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : '', FILTER_SANITIZE_STRING );
			if ( method_exists( $this, $action ) ) {
				// hook into action if it's method exists
				add_action( 'wp_ajax_' . $action, [ $this, $action ] );
			}
		}
	}

	/**
	 * AJAX Debug response
	 *
	 * @param mixed $data
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function debug( $data ) {

		// return dump
		$this->error( $data );
	}

	/**
	 * AJAX Debug response ( dump )
	 *
	 * @param mixed $args
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function dump( $args ) {

		// return dump
		$this->error( print_r( func_num_args() === 1 ? $args : func_get_args(), true ) );
	}

	/**
	 * AJAX Error response
	 *
	 * @param mixed $data
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function error( $data ) {

		wp_send_json_error( $data );
	}

	/**
	 * AJAX success response
	 *
	 * @param mixed $data
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function success( $data ) {

		wp_send_json_success( $data );
	}

	/**
	 * AJAX JSON Response
	 *
	 * @param mixed $response
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function response( $response ) {

		// send response
		wp_send_json( $response );
	}
}
