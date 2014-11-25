<?php
class PrsoSrcSet {
	
	protected static $class_config 				= array();
	protected $current_screen					= NULL;
	protected $plugin_ajax_nonce				= 'prso_src_set-ajax-nonce';
	protected $plugin_path						= PRSOSRCSET__PLUGIN_DIR;
	protected $plugin_url						= PRSOSRCSET__PLUGIN_URL;
	protected $plugin_textdomain				= PRSOSRCSET__DOMAIN;
	
	function __construct( $config = array() ) {
		
		//Cache plugin congif options
		self::$class_config = $config;
		
		//Set textdomain
		add_action( 'after_setup_theme', array($this, 'plugin_textdomain') );
		
		//Init plugin
		add_action( 'init', array($this, 'init_plugin') );
		add_action( 'admin_init', array($this, 'admin_init_plugin') );
		add_action( 'current_screen', array($this, 'current_screen_init_plugin') );
		
	}
	
	/**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation( $network_wide ) {
		
	}

	/**
	 * Attached to deactivate_{ plugin_basename( __FILES__ ) } by register_deactivation_hook()
	 * @static
	 */
	public static function plugin_deactivation( ) {
		
	}
	
	/**
	 * Setup plugin textdomain folder
	 * @public
	 */
	public function plugin_textdomain() {
		
		load_plugin_textdomain( $this->plugin_textdomain, FALSE, $this->plugin_path . '/languages/' );
		
	}
	
	/**
	* init_plugin
	* 
	* Used By Action: 'init'
	* 
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function init_plugin() {
		
		//Init vars
		$options 		= self::$class_config;
		
		if( is_admin() ) {
		
			//PLUGIN OPTIONS FRAMEWORK -- comment out if you dont need options
			$this->load_redux_options_framework();
			
		}
		
		//Register image sizes based on groups in config
		$this->register_image_sizes();
		
		//Prevent our custom image sizes being created by default when images are uploaded to media library
		//add_filter('intermediate_image_sizes_advanced', array($this, 'prevent_resize_on_upload'));
		
		//Dynamically create our image thumbnails only when they are first requested on the front end
		//add_filter('image_downsize', array($this, 'downsize_image_on_first_use'), 10, 3);
		
		// Attachment image attribute filter
		add_filter( 'post_thumbnail_html', array( $this, 'add_image_srcset' ), 10, 5 );
		
		//add_filter( 'get_image_tag', array( $this, 'add_media_image_tag_srcset' ), 10, 6 );
		add_filter( 'image_send_to_editor', array( $this, 'add_media_image_tag_srcset' ), 999, 6 );
		
	}
	
	/**
	* admin_init_plugin
	* 
	* Used By Action: 'admin_init'
	* 
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function admin_init_plugin() {
		
		//Init vars
		$options 		= self::$class_config;
		
		if( is_admin() ) {
			
			//Enqueue admin scripts
			add_action( 'admin_enqueue_scripts', array($this, 'enqueue_admin_scripts') );
			
		}
		
	}
	
	/**
	* current_screen_init_plugin
	* 
	* Used By Action: 'current_screen'
	* 
	* Detects current view and decides if plugin should be activated
	*
	* @access 	public
	* @author	Ben Moody
	*/
	public function current_screen_init_plugin() {
		
		//Init vars
		$options 		= self::$class_config;
		
		if( is_admin() ) {
		
			//Confirm we are on an active admin view
			if( $this->is_active_view() ) {
		
				//Carry out view specific actions here
				
			}
			
		}
		
	}
	
	
    /**
     * Create our srcset attribute for images called via get_the_post_thumbnail(), ect
     * 
     * Called by Filter: post_thumbnail_html
     *
     */	
	function add_image_srcset( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    	
    	$srcset = array();
    	
    	//Check if requested size is one of our image groups
    	if( isset(self::$class_config[$size]) ) {
	    	
	    	//Loop group sizes
	    	foreach( self::$class_config[$size] as $breakpoint => $image_data ) {
		    	
		    	//Cache unique image name based on group title and breakpoint
			    $image_name = "{$size}-{$breakpoint}";
			    
			    //Check if this is a retina image create a x1 version as well as x2
			    if( (bool)$image_data['retina'] === TRUE ) {
				    
				    //x2 (use original size supplied by user)
				    $image_name = "{$size}-{$breakpoint}-@2";
				    
				    //Add regular version
					$_attachment_src = wp_get_attachment_image_src( $post_thumbnail_id, $image_name );
					
					//Check image size exists
					if( isset($_attachment_src[3]) && ($_attachment_src[3] !== FALSE) ) {
						$srcset[] = $_attachment_src[0] . " {$breakpoint}w 2x";
					}
				    
			    }
				    
				//Add regular version
				$_attachment_src = wp_get_attachment_image_src( $post_thumbnail_id, $image_name );
				
				if( isset($_attachment_src[3]) && ($_attachment_src[3] !== FALSE) ) {
					$srcset[] = $_attachment_src[0] . " {$breakpoint}w";
				}
		    	
	    	}
	    	
	    	//Splice srcset into html output
	    	if( !empty($srcset) ) {
	    		$_srcset = join(',', $srcset );
		    	$html = str_replace( '/>', 'srcset="'.$_srcset.'"/>', $html );
	    	}
	    	
    	}
		
        return $html;
    }
	
    public function add_media_image_tag_srcset( $html, $id, $alt, $title, $align, $size ){
    	
    	//Init vars
    	$post_group_size 	= 'test'; //Get this from config options once setup
    	$srcset 			= array();
    	
    	//Confirm that post group size is setup in config
    	if( isset(self::$class_config[$post_group_size]) ) {
	    	
	    	//Loop group sizes
	    	foreach( self::$class_config[$post_group_size] as $breakpoint => $image_data ) {
		    	
		    	//Cache unique image name based on group title and breakpoint
			    $image_name = "{$post_group_size}-{$breakpoint}";
			    
			    //Check if this is a retina image create a x1 version as well as x2
			    if( (bool)$image_data['retina'] === TRUE ) {
				    
				    //x2 (use original size supplied by user)
				    $image_name = "{$post_group_size}-{$breakpoint}-@2";
				    
				    //Add regular version
					$_attachment_src = wp_get_attachment_image_src( $id, $image_name );
					
					//Check image size exists
					if( isset($_attachment_src[3]) && ($_attachment_src[3] !== FALSE) ) {
						$srcset[] = $_attachment_src[0] . " {$breakpoint}w 2x";
					}
				    
			    }
				    
				//Add regular version
				$_attachment_src = wp_get_attachment_image_src( $id, $image_name );
				
				if( isset($_attachment_src[3]) && ($_attachment_src[3] !== FALSE) ) {
					$srcset[] = $_attachment_src[0] . " {$breakpoint}w";
				}
		    	
	    	}
	    	
	    	//Splice srcset into html output
	    	if( !empty($srcset) ) {
	    		$_srcset = join(',', $srcset );
		    	$html = str_replace( '/>', 'srcset="'.$_srcset.'"/>', $html );
	    	}
	    	
    	}
		
        return $html;
    }
    
    private function register_image_sizes() {
	    
	    //Init vars
	    $image_name = NULL;
	    $image_w	= NULL;
	    $image_h	= NULL;
	    
	    //Loop image groups and setup image sizes
	    if( !empty(self::$class_config) && is_array(self::$class_config) ) {
		    
		    foreach( self::$class_config as $group_title => $breakpoint_sizes ) {
		    
			    //Loop breakpoint image sizes in this group
			    if( !empty($breakpoint_sizes) ) {
			    
				    foreach( $breakpoint_sizes as $breakpoint => $image_data ) {
					    
					    //Cache unique image name based on group title and breakpoint
					    $image_name = "{$group_title}-{$breakpoint}";
					    
					    //Check if this is a retina image create a x1 version as well as x2
					    if( (bool)$image_data['retina'] === TRUE ) {
						    
						    //x1
						    add_image_size( $image_name, ($image_data['w']/2), ($image_data['h']/2), TRUE );
						    
						    //x2 (use original size supplied by user)
						    $image_name = "{$group_title}-{$breakpoint}-@2";
						    add_image_size( $image_name, $image_data['w'], $image_data['h'], TRUE );
						    
					    } else {
						    
						    //Add regular version
						    add_image_size( $image_name, $image_data['w'], $image_data['h'], TRUE );
						    
					    }
					    
				    }
				    
			    }
			    
		    }
		    
	    }
	    
    }
    
    /**
     * Would prevent srcset custom sizes being generated on image upload
     *
     * Needs work.
     *
     */	
    public function prevent_resize_on_upload( $sizes ) {
	    
	    //Loop image groups and setup image sizes
	    if( !empty(self::$class_config) && is_array(self::$class_config) ) {
		    
		    foreach( self::$class_config as $group_title => $breakpoint_sizes ) {
		    
			    //Loop breakpoint image sizes in this group
			    if( !empty($breakpoint_sizes) ) {
			    
				    foreach( $breakpoint_sizes as $breakpoint => $image_data ) {
					    
					    //Cache unique image name based on group title and breakpoint
					    $image_name = "{$group_title}-{$breakpoint}";
					    
					    //Unset image size
					    unset( $sizes[$image_name] );
					    
					    //Check if this is a retina image create a x1 version as well as x2
					    if( (bool) $image_data['retina'] === TRUE ) {
						    
						   //Unset retina image size
						   $image_name = "{$group_title}-{$breakpoint}-@2";
						   
						   unset( $sizes[$image_name] );
						    
					    }
					    
				    }
				    
			    }
			    
		    }
		    
	    }
	    
    }
    
    /**
     * Would resize srcset image thumbnails only on first use of size
     *
     * Needs work.
     *
     */	
    public function downsize_image_on_first_use( $out, $id, $size ) {
	    
	    //Only process images with a title added via api
	    if( is_array($size) ) {
	    	return false;
	    }
	    
	    // If image size exists let WP serve it like normally
        $imagedata = wp_get_attachment_metadata($id);
        if (is_array($imagedata) && isset($imagedata['sizes'][$size]))
            return false;

        // Check that the requested size exists, or abort
        global $_wp_additional_image_sizes;
        if( is_array($size) ) {
	        if (!isset($_wp_additional_image_sizes[$size])) {
		        return false;
	        }
	        return false;
        }
        
        if( !isset($_wp_additional_image_sizes[$size]) ) {
	        return false;
        }
        
        // Make the new thumb
        if (!$resized = image_make_intermediate_size(
            get_attached_file($id),
            $_wp_additional_image_sizes[$size]['width'],
            $_wp_additional_image_sizes[$size]['height'],
            $_wp_additional_image_sizes[$size]['crop']
        ))
            return false;

        // Save image meta, or WP can't see that the thumb exists now
        $imagedata['sizes'][$size] = $resized;
        wp_update_attachment_metadata($id, $imagedata);

        // Return the array for displaying the resized image
        $att_url = wp_get_attachment_url($id);
        return array(dirname($att_url) . '/' . $resized['file'], $resized['width'], $resized['height'], true);
	    
    }

        
	/**
	* load_redux_options_framework
	* 
	* Loads Redux options framework as well as the unique config file for this plugin
	*
	* NOTE!!!!
	*			You WILL need to make sure some unique constants as well as the class
	*			name in the plugin config file 'inc/ReduxConfig/ReduxConfig.php'
	*
	* @access 	public
	* @author	Ben Moody
	*/
	protected function load_redux_options_framework() {
		
		//Init vars
		$framework_inc 		= $this->plugin_path . 'inc/ReduxFramework/ReduxCore/framework.php';
		$framework_config	= $this->plugin_path . 'inc/ReduxConfig/ReduxConfig.php';
		
		//Try and load redux framework
		if ( !class_exists('ReduxFramework') && file_exists($framework_inc) ) {
			require_once( $framework_inc );
		}
		
		//Try and load redux config for this plugin
		if ( file_exists($framework_config) ) {
			require_once( $framework_config );
		}
		
	}
	
	/**
	* is_active_view
	* 
	* Detects if current admin view has been set as 'active_post_type' in
	* plugin config options array.
	* 
	* @var		array	self::$class_config
	* @var		array	$active_views
	* @var		obj		$screen
	* @var		string	$current_screen
	* @return	bool	
	* @access 	protected
	* @author	Ben Moody
	*/
	protected function is_active_view() {
		
		//Init vars
		$options 		= self::$class_config;
		$active_views	= array();
		$screen			= get_current_screen();
		$current_screen	= NULL;
		
		//Cache all views plugin will be active on
		$active_views = $this->get_active_views( $options );
		
		//Cache the current view
		if( isset($screen) ) {
		
			//Is this an attachment screen (base:upload or post_type:attachment)
			if( ($screen->id === 'attachment') || ($screen->id === 'upload') ) {
				$current_screen = 'attachment';
			} else {
				
				//Cache post type for all others
				$current_screen = $screen->post_type;
				
			}
			
			//Cache current screen in class protected var
			$this->current_screen = $current_screen;
		}
		
		//Finaly lets check if current view is an active view for plugin
		if( in_array($current_screen, $active_views) ) {
			return TRUE;
		} else {
			return FALSE;
		}
		
	}
	
	/**
	* get_active_views
	* 
	* Interates over plugin config options array merging all
	* 'active_post_type' values into single array
	* 
	* @param	array	$options
	* @var		array	$active_views
	* @return	array	$active_views
	* @access 	private
	* @author	Ben Moody
	*/
	protected function get_active_views( $options = array() ) {
		
		//Init vars
		$active_views = array();
		
		//Loop options and cache each active post view
		foreach( $options as $option ) {
			if( isset($option['active_post_types']) ) {
				$active_views = array_merge($active_views, $option['active_post_types']);
			}
		}
		
		return $active_views;
	}
	
	/**
	 * Helper to set all actions for plugin
	 */
	protected function set_admin_actions() {
		
		
		
	}
	
	/**
	 * Helper to enqueue all scripts/styles for admin views
	 */
	public function enqueue_admin_scripts() {
		
		//Init vars
		$js_inc_path 	= $this->plugin_url . 'inc/js/';
		$css_inc_path 	= $this->plugin_url . 'inc/css/';
		
		
		
		//Localize vars
		$this->localize_script();
		
	}
	
	/**
	* localize_script
	* 
	* Helper to localize all vars required for plugin JS.
	* 
	* @var		string	$object
	* @var		array	$js_vars
	* @access 	private
	* @author	Ben Moody
	*/
	protected function localize_script() {
		
		//Init vars
		$object 	= 'PrsoPluginFrameworkVars';
		$js_vars	= array();
		
		//Localize vars for ajax requests
		
		
		//wp_localize_script( '', $object, $js_vars );
	}
	
}



