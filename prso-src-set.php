<?php

/**
	!!!HOW TO SETUP!!!
	
	Use some find and replace to quickly set some unique names for your plugin.
	
	1. Create unique constant prefix: 
	
			PRSOSRCSET --> MYUNIQUECONSTANT
			
	2. Add name of your unique plugin class file: 
	
			prso-src-set --> my-unique-filename
			
	3. Create uniqueue name for plugin class: 
	
			PrsoSrcSet --> MyUniqueClassName
			
	4. Create unique slug for plugin: 
	
			prso_src_set --> my_unique_slug_name
			
	5. If using option framework create uniqueu class name for options class: 
	
			PrsoSrcSetOptions --> MyUniqueOptionsClass
			
	6. If not using options framework then comment out $this->load_redux_options_framework(); line 
		and delete ReduxFramework folder from inc folder.
	
	Also you will need to delete git hidden folder in root of your copy.
	
	Delete this once you have done this basic setup, the framework is ready for your project
	
**/

/*
 * Plugin Name: SrcSet Plugin
 * Plugin URI: 
 * Description: 
 * Author: Benjamin Moody
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