<?php
/*
 * Plugin Name: SrcSet Plugin
 * Plugin URI: 
 * Description: 
 * Author: Benjamin Moody, Eric Holmes
 * Version: 1.0
 * Author URI: http://www.benjaminmoody.com
 * License: GPL2+
 * Text Domain: prso_plugin_framework
 * Domain Path: /languages/
 */

//Define plugin constants
define( 'PRSOSRCSET__MINIMUM_WP_VERSION', '3.0' );
define( 'PRSOSRCSET__VERSION', '1.0' );
define( 'PRSOSRCSET__DOMAIN', 'prso_src_set_plugin' );

//Plugin admin options will be available in global var with this name, also is database slug for options
define( 'PRSOSRCSET__OPTIONS_NAME', 'prso_src_set_options' );

define( 'PRSOSRCSET__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PRSOSRCSET__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//Include plugin classes
require_once( PRSOSRCSET__PLUGIN_DIR . 'class.prso-src-set.php'               );

//Set Activation/Deactivation hooks
register_activation_hook( __FILE__, array( 'PrsoSrcSet', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'PrsoSrcSet', 'plugin_deactivation' ) );

//Set plugin config
$config_options = array();

//Instatiate plugin class and pass config options array
new PrsoSrcSet( $config_options );