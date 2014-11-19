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

prso_src_set_init();
function prso_src_set_init() {
	
	//Init vars
	global $prso_src_set_options;
	$config_options = array();
	
	//Cache plugin options array
	$prso_src_set_options = get_option( PRSOSRCSET__OPTIONS_NAME );
	
	//Build config array for each instance of rater
	if( isset($prso_src_set_options['img_groups_instances']) ) {
		foreach( $prso_src_set_options['img_groups_instances'] as $group_title ) {
		
			$_group_slug = hash("crc32b", $group_title);
			
			//Small breakpoint
			if( isset($prso_src_set_options['fullsz_sm_'.$_group_slug]) && ($prso_src_set_options['fullsz_sm_'.$_group_slug] == 0) ) {
				
				$_break_point = $prso_src_set_options['bp_sm_'.$_group_slug];
				
				$config_options[$group_title][$_break_point] = array(
					'w'				=>	$prso_src_set_options['imgw_sm_'.$_group_slug],
					'h'				=>	$prso_src_set_options['imgh_sm_'.$_group_slug],
					'breakpoint'	=>	$_break_point,
					'retina'		=>	$prso_src_set_options['x2_sm_'.$_group_slug]
				);
				
			}
			
			
			//Medium breakpoint
			if( isset($prso_src_set_options['fullsz_med_'.$_group_slug]) && ($prso_src_set_options['fullsz_med_'.$_group_slug] == 0) ) {
				
				$_break_point = $prso_src_set_options['bp_med_'.$_group_slug];
				
				$config_options[$group_title][$_break_point] = array(
					'w'				=>	$prso_src_set_options['imgw_med_'.$_group_slug],
					'h'				=>	$prso_src_set_options['imgh_med_'.$_group_slug],
					'breakpoint'	=>	$_break_point,
					'retina'		=>	$prso_src_set_options['x2_med_'.$_group_slug]
				);
				
			}
			
			//Large breakpoint
			if( isset($prso_src_set_options['fullsz_lg_'.$_group_slug]) && ($prso_src_set_options['fullsz_lg_'.$_group_slug] == 0) ) {
				
				$_break_point = $prso_src_set_options['bp_lg_'.$_group_slug];
				
				$config_options[$group_title][$_break_point] = array(
					'w'				=>	$prso_src_set_options['imgw_lg_'.$_group_slug],
					'h'				=>	$prso_src_set_options['imgh_lg_'.$_group_slug],
					'breakpoint'	=>	$_break_point,
					'retina'		=>	$prso_src_set_options['x2_lg_'.$_group_slug]
				);
				
			}
			
			//X Large breakpoint
			if( isset($prso_src_set_options['fullsz_xl_'.$_group_slug]) && ($prso_src_set_options['fullsz_xl_'.$_group_slug] == 0) ) {
				
				$_break_point = $prso_src_set_options['bp_xl_'.$_group_slug];
				
				$config_options[$group_title][$_break_point] = array(
					'w'				=>	$prso_src_set_options['imgw_xl_'.$_group_slug],
					'h'				=>	$prso_src_set_options['imgh_xl_'.$_group_slug],
					'breakpoint'	=>	$_break_point,
					'retina'		=>	$prso_src_set_options['x2_xl_'.$_group_slug]
				);
				
			}
			
		}
	}
	
	//Instatiate plugin class and pass config options array
	new PrsoSrcSet( $config_options );
		
}