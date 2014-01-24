<?php


/**
 * API Base Class
 */
class WP_API_NAME extends Base_API{

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 */
	const VERSION = '1.0.0';

	/**
	 * Plugin slug
	 *
	 * @since 1.0.0
	 */
	protected $plugin_slug = 'wp-api-NAME';

	/**
	 * Instanve of this class
	 *
	 * @since 1.0.0
	 */
	protected static $instance = null;


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
	 * Initialize the plugin
	 *
	 * @since     1.0
	 * @return void
	 */
	private function __construct() {

		add_action( 'init', array( $this, 'add_endpoint' ) );

		add_action( 'template_redirect', array( $this, 'api_request' ), -1);
		add_filter( 'query_vars',  array( $this, 'api_routes' ) );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}	

	/**
	 * Return instance of the class
	 *
	 * @since     1.0
	 * @return class
	 */
	public static function get_instance() {

		if ( null == self::$instance ) 
			self::$instance = new self;

		return self::$instance;
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0
	 * @return void
	 */
	public static function activate( $network_wide ) {
		update_option('wp_api_NAME_version', self::VERSION);
		flush_rewrite_rules();
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public static function deactivate( $network_wide ) {
		delete_option('wp_api_NAME_version', self::VERSION);
		flush_rewrite_rules();
	}

	/**
	 * Add endpoint for API.
	 *
	 * @since    1.0.0
	 * @return void
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( $this->api_key, EP_ALL );
	}

	/**
	 * Registers routs and parameters 	for API access
	 *
	 * @since    1.0.0
	 * @return array
	 */
	public function api_routes( $vars ) {

		$vars[] = 'post'; 
		$vars[] = 'limit'; 

		return $vars;
	}

	/**
	 * Rout API Call to action
	 *
	 * @since    1.0.0
	 * @return JSON
	 */
	public function api_request()
	{
		global $wp_query;

		if ( ! isset( $wp_query->query_vars[ $this->api_key ] ) )
			return;

		$validate = $this->validate_request(); //check if call is authorized

		$results = "";
		$result_key = 'results';
		$compress_results = false;

		if(is_array($validate))
		{
			status_header( $validate['status'] );
			$results = $validate['error'];
			$result_key = 'error';
		}
		else
		{
	            $query_mode = $this->get_query_mode();
	            status_header( 201 );

			switch ($query_mode) {
				
				case 'post':
					$compress_results = true;
			    		$limit = $wp_query->query_vars['limit'];

			    		$args = array(
						'post_type' => 'post',
						'posts_per_page' => $limit
					);

					$results = new WP_Query( $args );


			    	break;
			    	
			}
		}

		$arr = array ($result_key  => $results);
        	header( 'Content-Type: application/json' );

        	echo ($compress_results) ? jsonh_encode($arr) : json_encode($results);
	   		
	    	exit;
	}

	/**
	 * Validate and determine query type
	 *
	 * @since    1.0.0
	 * @return string
	 */
      public function get_query_mode() {
          global $wp_query;

          // Whitelist our query options
          $accepted = array( 'post' );

		if (isset( $wp_query->query_vars[ $this->api_key ] )) 
		{
			$routes = explode("/", $wp_query->query_vars[ $this->api_key ]);
			$query =  $routes[0];
		}
		else
			$query = null;

          if ( ! in_array( $query, $accepted ) ) 
          {
			$arr = array ('error'  => __( 'Invalid query!', $this->api_key) );
	        	header( 'Content-Type: application/json' );
		   	echo json_encode($arr);
          }

          return $query;
      }

      /**
	 * Generate API for user
	 *
	 * @since    1.0.0
	 * @return array
	 */
      public function register_user($user_id) {
      	$user = get_user_by('id', $user_id);

      	if($user)
      	{
      		$id = $user->ID;	
      		$keys = $this->generate_key($id);
      		update_user_meta( $id, $this->user_secret_key_field,  $keys['secret']);
			update_user_meta( $id, $this->user_public_key_field,  $keys['public']);
			update_user_meta( $id, $this->user_token_key_field,  $keys['token']);

			$message['message'] = __( 'Your account was created succefully!', $this->api_key );
			$message['status'] = 401;
			return $message;
      	}
      	else
      	{
      		$message['error'] =  __( 'Please enter a valid user ID!', $this->api_key ) ;
			$message['status'] = 401;
			return $error;
      	}
  
      }

      
}
