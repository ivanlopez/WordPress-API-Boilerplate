<?php
/*
Plugin Name: WordPress API Name
Plugin URI: http://IvanLopezDeveloper.com/
Description: Description of the API
Version: 1.0
Author: Ivan Lopez
Author URI: http://IvanLopezDeveloper.com/
*/

/**
 * Copyright (c) 2013 Ivan Lopez. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) 
	die;


if ( ! class_exists( 'JSONH' ) )
	require_once( plugin_dir_path( __FILE__ ) . 'includes/JSONH.class.php' );

require_once( plugin_dir_path( __FILE__ ) . 'includes/class-base-api.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-wp-api-NAME.php' );

register_activation_hook( __FILE__, array( 'WP_API_NAME', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WP_API_NAME', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WP_API_NAME', 'get_instance' ) );