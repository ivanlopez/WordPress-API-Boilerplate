<?php 

/**
 * API Base Class
 */
 class Base_API 
 {

 	/**
	 * Api key used in url
	 *
	 * @since 1.0.0
	 */
 	public $api_key = 'api-NAME';

 	/**
	 * variable stored in database for a secrete key
	 *
	 * @since 1.0.0
	 */
 	public $user_secret_key_field = 'user_secret_key';

 	/**
	 * variable stored in database for a public key
	 *
	 * @since 1.0.0
	 */
 	public $user_public_key_field = 'user_public_key';

 	/**
	 * variable stored in database for a public key
	 *
	 * @since 1.0.0
	 */
 	public $user_token_key_field = 'user_token_key';

 	/**
	 * Is this a valid request?
	 *
	 * @since 1.0.0
	 */
	private $is_valid_request = false;

	/**
	 * current user id
	 *
	 * @since 1.0.0
	 */
	private $user_id = 0;


 	/**
	 * Validate the API request
	 *	 
	 * @since 1.0
	 * @return void
	 */
 	public function validate_request(){
 		global $wp_query;

        	// Make sure we have both user and api key
		if ( ! empty( $wp_query->query_vars[$this->api_key] )) 
		{
			if ( empty( $wp_query->query_vars['token'] ) || empty( $wp_query->query_vars['key'] ) )
				return $this->missing_auth();

			// Retrieve the user by public API key and ensure they exist
			if ( ! ( $user = $this->get_user( $wp_query->query_vars['key'] ) ) ) :
				return $this->invalid_key();
			else :
				$token  = urldecode( $wp_query->query_vars['token'] );
				$secret = get_user_meta( $user, $this->user_secret_key_field, true );
				$public = urldecode( $wp_query->query_vars['key'] );

				if ( hash( 'md5', $secret . $public ) === $token )
				{
					return $this->user_id;
				}
				else
					return $this->invalid_auth();
			endif;
		} 
		else
		{
			$error['error'] = __( 'Invalid query!', 'wpc' );
			$error['status'] = 401;

			return $error;
		}
 	}

 	/**
	 * Retrives the user ID base on the public key
	 *	 
	 * @since 1.0
	 * @return Boolean
	 */
	private function get_user( $key = '' ) {
		global $wpdb, $wp_query;

		if( empty( $key ) )
			$key = urldecode( $wp_query->query_vars['key'] );

		$user = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = '". $this->user_public_key_field ."' AND meta_value = %s LIMIT 1", $key ) );

		if ( $user != NULL ) 
		{
			$this->user_id = $user;
			return $user;
		}
		return false;
	}

	/**
	 * Display error if API key or Token was not provided
	 *	 
	 * @since 1.0
	 * @return array
	 */
	private function missing_auth() {
		$error['error'] = __( 'You must specify both a token and API key!', 'wpc' );
		$error['status'] = 401;

		return $error;
	}

	/**
	 * Display error if not authenticated
	 *	 
	 * @since 1.0
	 * @return array
	 */
	private function invalid_auth() {
		$error['error'] = __( 'Your request could not be authenticated!', 'wpc' );
		$error['status'] = 401;

		return $error;
	}

	/**
	 * Display error if invalid API Key
	 *	 
	 * @since 1.0
	 * @return array
	 */
	private function invalid_key() {
		$error['error'] = __( 'Invalid API key!', 'wpc' );
		$error['status'] = 401;

		return $error;
	}

	/**
	 * Generate and save API keys
	 *	 
	 * @since 1.0
	 * @return array
	 */
	public function generate_key( $user_id ) {
		$user = get_userdata( $user_id );
		$keys['public'] =  hash( 'md5', $user->user_email . date( 'U' ) );
		$keys['secret'] =  hash( 'md5', $user->ID . date( 'U' ) );
		$keys['token'] =  hash( 'md5', $secret . $public  );

		return $keys;		
	}


 } 