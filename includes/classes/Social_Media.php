<?php namespace Codeable_AutoPost_Review;

use Abraham\TwitterOAuth\TwitterOAuth;
use Abraham\TwitterOAuth\TwitterOAuthException;
use stdClass;

/**
 * Social_Media logic
 *
 * @package Codeable_AutoPost_Review
 */
class Social_Media extends Component {

	/**
	 * @var TwitterOAuth
	 */
	protected $_twitter_connection;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	protected function init() {

		parent::init();

		// Twitter authentication callback
		add_action( 'admin_action_capr_auth_twitter', [ $this, 'authenticate_twitter' ] );

		// Codeable latest reviews
		add_action( 'capr_latest_review', [ $this, 'publish_on_twitter' ] );

	}

	/**
	 * @param stdClass $review
	 *
	 * @return void
	 */
	public function publish_on_twitter( $review ) {

		// twitter isn't enabled
		if ( 'on' !== capr_backend()->get_settings( 'enabled', 'twitter' ) ) {
			return;
		}

		$connection = $this->get_twitter_connection();
		$tweet_body = $this->parse_review_template( $review, capr_backend()->get_settings( 'tweet_template', 'twitter' ) );

		if ( $tweet_body ) {
			$connection->post( 'statuses/update', [ 'status' => $tweet_body ] );
		}
	}

	/**
	 * @return void
	 * @throws TwitterOAuthException
	 */
	public function authenticate_twitter() {

		$request_token = $this->get_twitter_request_token( true );

		if ( ! isset( $_REQUEST['oauth_token'] ) || $request_token['oauth_token'] !== $_REQUEST['oauth_token'] ) {
			wp_die( 'Invalid authentication response!', __( 'OAth Error', CAPR_DOMAIN ) );
		}

		// get access token and store it
		$connection   = $this->get_twitter_connection( $request_token );
		$access_token = $connection->oauth( 'oauth/access_token', [ 'oauth_verifier' => $_REQUEST['oauth_verifier'] ] );
		update_option( 'capr_twitter_access_token', $access_token, 'no' );

		// redirect back to settings page
		$settings_slug = capr_backend()->get_settings_slug();
		wp_redirect( add_query_arg( 'page', $settings_slug . '_page', admin_url( '/options-general.php' ) ) . '#' . $settings_slug . '_twitter' );
		exit;
		
	}

	/**
	 * @return string
	 */
	public function get_twitter_callback_url() {

		return $this->get_social_media_callback_url( 'twitter' );
		
	}

	/**
	 * @param string $media_name
	 *
	 * @return string
	 */
	public function get_social_media_callback_url( $media_name ) {

		return add_query_arg( 'action', 'capr_auth_' . $media_name, admin_url( '/' ) );
		
	}

	/**
	 * @param array $request_token
	 *
	 * @return TwitterOAuth
	 */
	public function get_twitter_connection( $request_token = null ) {

		if ( null === $this->_twitter_connection ) {
			$request_token = $request_token ?? $this->get_twitter_access_token();

			$this->_twitter_connection = new TwitterOAuth(
				capr_backend()->get_settings( 'api_key', 'twitter' ),
				capr_backend()->get_settings( 'api_secret', 'twitter' ),
				$request_token['oauth_token'],
				$request_token['oauth_token_secret']
			);
		}

		return $this->_twitter_connection;
		
	}

	/**
	 * Render Twitter Authentication link button
	 *
	 * @return void
	 * @throws TwitterOAuthException
	 */
	public function twitter_authentication_button() {

		$connection    = $this->get_twitter_connection();
		$request_token = $this->get_twitter_request_token();
		$access_token  = $this->get_twitter_access_token();

		echo '<p><a href="', esc_url( $connection->url( 'oauth/authorize', [ 'oauth_token' => $request_token['oauth_token'] ] ) ), '" class="button">',
		false === $access_token ? __( 'Authenticate', CAPR_DOMAIN ) : __( 'Re-Authenticate', CAPR_DOMAIN ), '</a></p>';

		if ( false !== $access_token ) {
			
			echo '<br/><textarea readonly class="large-text code" rows="20">',
				"\nAccess Token: " . print_r( $access_token, true ),
				"\nVerify Credentials: " . print_r( $connection->get( 'account/verify_credentials' ), true ),
			'</textarea>';
			
		}
		
	}

	/**
	 * @param boolean $from_session
	 *
	 * @return array
	 * @throws TwitterOAuthException
	 */
	public function get_twitter_request_token( $from_session = false ) {

		if ( $from_session ) {
			$request_token = $_SESSION['capr_twitter_request_token'] ?? null;

			if ( false !== $request_token ) {
				return $request_token;
			}
		}

		$_SESSION['capr_twitter_request_token'] = $this->get_twitter_connection()->oauth( 'oauth/request_token', [ 'oauth_callback' => $this->get_twitter_callback_url() ] );

		return $_SESSION['capr_twitter_request_token'];
		
	}

	/**
	 * @param mixed $default
	 *
	 * @return array
	 */
	public function get_twitter_access_token( $default = false ) {

		return $this->get_social_media_access_token( 'twitter', $default );
		
	}

	/**
	 * @param string $media_name
	 * @param mixed  $default
	 *
	 * @return array
	 */
	public function get_social_media_access_token( $media_name, $default = false ) {

		return get_option( 'capr_' . $media_name . '_access_token', $default );
		
	}

	/**
	 * Parse template placeholders with given review
	 *
	 * @param stdClass $review
	 * @param string   $template
	 *
	 * @return string
	 */
	public function parse_review_template( $review, $template ) {

		// extract placeholder
		preg_match_all( '/\{([a-zA-Z_\.]+)\}/', $template, $template_vars );

		$var_replacements = [];

		if ( ! empty( $template_vars[1] ) ) {

			foreach ( $template_vars[1] as $var_index => $var_name ) {

				$var_value = Helpers::get_object_prop( $review, $var_name );
				if ( null === $var_value ) {
					continue;
				}

				$var_replacements[ $template_vars[0][ $var_index ] ] = $var_value;
			}

			if ( count( $var_replacements ) ) {
				$template = str_replace( array_keys( $var_replacements ), array_values( $var_replacements ), $template );
			}
		}

		/**
		 * @param string   $template
		 * @param array    $var_replacements
		 * @param stdClass $review
		 *
		 * @return string
		 */
		return (string) apply_filters( 'capr_parsed_review_template', $template, $var_replacements, $review );
	}
}
