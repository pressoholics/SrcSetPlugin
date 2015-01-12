<?php
/*
 * Plugin Name: TrueNorth SrcSet Plugin
 * Plugin URI: 
 * Description: SrcSet Plugin allows you to change the dimensions of images based on browser size by automatically adding the srcset attribute.
 * Author: Benjamin Moody, Eric Holmes
 * Version: 1.0
 * Author URI: http://www.benjaminmoody.com
 * License: GPL2+
 * Text Domain: tnsrc_set_plugin
 * Domain Path: /languages/
 */

//Define plugin constants
define( 'TNSRCSET__MINIMUM_WP_VERSION', '3.0' );
define( 'TNSRCSET__VERSION', '1.0' );
define( 'TNSRCSET__DOMAIN', 'tnsrc_set_plugin' );

//Plugin admin options will be available in global var with this name, also is database slug for options
define( 'TNSRCSET__OPTIONS_NAME', 'tnsrc_set_options' );

define( 'TNSRCSET__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TNSRCSET__PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//Include plugin classes
require_once( TNSRCSET__PLUGIN_DIR . 'class.tn-srcset.php' );
require_once( TNSRCSET__PLUGIN_DIR . 'inc/class.tn-srcset-regen.php' );

//Set Activation/Deactivation hooks
register_activation_hook( __FILE__, array( 'TNSrcSet', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'TNSrcSet', 'plugin_deactivation' ) );

//Add extra options to plugin links in plugins page
function tnsrc_set_set_plugin_meta($links, $file) {
	
	$plugin = plugin_basename(__FILE__);

	// create link
	if ($file == $plugin) {
		return array_merge(
			$links,
			array(
				sprintf( '<a href="tools.php?page=tnsrc_set_options_options">%s</a>', __('Settings') ) 
			)
		);
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'tnsrc_set_set_plugin_meta', 10, 2 );

tnsrc_set_init();
function tnsrc_set_init() {
	
	//Init vars
	global $tnsrc_set_options;
	$config_options = array();
	$img_size_rels	= array();
	
	//Cache plugin options array
	$tnsrc_set_options = get_option( TNSRCSET__OPTIONS_NAME );
	
	//Build config array for each instance of rater
	if( isset($tnsrc_set_options['img_groups_instances']) ) {
		
		//First, set global options
		if( isset($tnsrc_set_options['post_img_group']) ) {
			$config_options['post_img_group'] = $tnsrc_set_options['post_img_group'];
		}
		
		//Loop all image group sizes
		foreach( $tnsrc_set_options['img_groups_instances'] as $group_title ) {
		
			$_group_slug = hash("crc32b", $group_title);
			
			$group_title = sanitize_title( $group_title );
			
			//Cache custom image size relationship for this group
			if( isset($tnsrc_set_options['img_size_rel_'.$_group_slug]) && !empty($tnsrc_set_options['img_size_rel_'.$_group_slug]) ) {
				$img_size_rels[ $tnsrc_set_options['img_size_rel_'.$_group_slug] ] = $group_title;
			}
			
			//Small breakpoint
			if( isset($tnsrc_set_options['fullsz_sm_'.$_group_slug]) ) {
				
				$_break_point = $tnsrc_set_options['bp_sm_'.$_group_slug];
				
				$config_options['img_groups'][$group_title][$_break_point] = array(
					'breakpoint'	=>	$_break_point,
					//'retina'		=>	$tnsrc_set_options['x2_sm_'.$_group_slug],
					'retina'		=>	NULL,
					'thumb_size'	=>	$tnsrc_set_options['fullsz_sm_'.$_group_slug]
				);
				
				//Detect if this is a custom image size and add image width/height
				if( $tnsrc_set_options['fullsz_sm_'.$_group_slug] === 'custom' ) {
					
					$config_options['img_groups'][$group_title][$_break_point]['w'] = $tnsrc_set_options['imgw_sm_'.$_group_slug];
					
					$config_options['img_groups'][$group_title][$_break_point]['h'] = $tnsrc_set_options['imgh_sm_'.$_group_slug];
					
				}
				
			}
			
			
			//Medium breakpoint
			if( isset($tnsrc_set_options['fullsz_med_'.$_group_slug]) ) {
				
				$_break_point = $tnsrc_set_options['bp_med_'.$_group_slug];
				
				$config_options['img_groups'][$group_title][$_break_point] = array(
					'breakpoint'	=>	$_break_point,
					//'retina'		=>	$tnsrc_set_options['x2_med_'.$_group_slug],
					'retina'		=>	NULL,
					'thumb_size'	=>	$tnsrc_set_options['fullsz_med_'.$_group_slug]
				);
				
				//Detect if this is a custom image size and add image width/height
				if( $tnsrc_set_options['fullsz_med_'.$_group_slug] === 'custom' ) {
					
					$config_options['img_groups'][$group_title][$_break_point]['w'] = $tnsrc_set_options['imgw_med_'.$_group_slug];
					
					$config_options['img_groups'][$group_title][$_break_point]['h'] = $tnsrc_set_options['imgh_med_'.$_group_slug];
					
				}
				
			}
			
			//Large breakpoint
			if( isset($tnsrc_set_options['fullsz_lg_'.$_group_slug]) ) {
				
				$_break_point = $tnsrc_set_options['bp_lg_'.$_group_slug];
				
				$config_options['img_groups'][$group_title][$_break_point] = array(
					'breakpoint'	=>	$_break_point,
					//'retina'		=>	$tnsrc_set_options['x2_lg_'.$_group_slug],
					'retina'		=>	NULL,
					'thumb_size'	=>	$tnsrc_set_options['fullsz_lg_'.$_group_slug]
				);
				
				//Detect if this is a custom image size and add image width/height
				if( $tnsrc_set_options['fullsz_lg_'.$_group_slug] === 'custom' ) {
					
					$config_options['img_groups'][$group_title][$_break_point]['w'] = $tnsrc_set_options['imgw_lg_'.$_group_slug];
					
					$config_options['img_groups'][$group_title][$_break_point]['h'] = $tnsrc_set_options['imgh_lg_'.$_group_slug];
					
				}
				
			}
			
			//X Large breakpoint
			if( isset($tnsrc_set_options['fullsz_xl_'.$_group_slug]) ) {
				
				$_break_point = $tnsrc_set_options['bp_xl_'.$_group_slug];
				
				$config_options['img_groups'][$group_title][$_break_point] = array(
					'breakpoint'	=>	$_break_point,
					//'retina'		=>	$tnsrc_set_options['x2_xl_'.$_group_slug],
					'retina'		=>	NULL,
					'thumb_size'	=>	$tnsrc_set_options['fullsz_xl_'.$_group_slug]
				);
				
				//Detect if this is a custom image size and add image width/height
				if( $tnsrc_set_options['fullsz_xl_'.$_group_slug] === 'custom' ) {
					
					$config_options['img_groups'][$group_title][$_break_point]['w'] = $tnsrc_set_options['imgw_xl_'.$_group_slug];
					
					$config_options['img_groups'][$group_title][$_break_point]['h'] = $tnsrc_set_options['imgh_xl_'.$_group_slug];
					
				}
				
			}
			
		}
		
		//Cache custom image size relationships in config array
		if( !empty($img_size_rels) ) {
			$config_options['img_size_rels'] = $img_size_rels;
		}
		
	}
	
	//Instatiate plugin class and pass config options array
	new TNSrcSet( $config_options );

	
}
