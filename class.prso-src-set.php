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
		//add_action( 'admin_init', array($this, 'admin_init_plugin') );
		//add_action( 'current_screen', array($this, 'current_screen_init_plugin') );
		
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
		
		//Attachment image attribute filter -- for use with code e.g. get_the_post_thumbnail()
		add_filter( 'post_thumbnail_html', array( $this, 'add_image_srcset' ), 10, 5 );
		
		//Attachment image attribute filter -- filters img tags added via tinymce content editor
		add_filter( 'image_send_to_editor', array( $this, 'add_media_image_tag_srcset' ), 999, 6 );
		
		//Filter TINYMCE allowed attributes for img tag
		add_filter('tiny_mce_before_init', array($this, 'add_tinymce_attributes'));
		
		//prso_debug(get_intermediate_image_sizes());
		//prso_debug(self::$class_config);
		//exit();
		
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
	* add_tinymce_attributes
	* 
	* @Called By Filter: 'tiny_mce_before_init'
	* 
	* Filters the list of img attributes allowed by tinymce when adding images to content area
	* Here we add in the srcset attribute
	*
	* @param	array	$options
	* @return	array	$options
	* @access 	public
	* @author	Ben Moody
	*/
	public function add_tinymce_attributes( $options ) {
		
		if ( ! isset( $options['extended_valid_elements'] ) ) {
	        $options['extended_valid_elements'] = '';
	    } else {
	        $options['extended_valid_elements'] .= ',';
	    }
	 
	    if ( ! isset( $options['custom_elements'] ) ) {
	        $options['custom_elements'] = '';
	    } else {
	        $options['custom_elements'] .= ',';
	    }
	 
	    $options['extended_valid_elements'] .= 'img[class|src|srcset|alt|width|height]';
	    $options['custom_elements']         .= 'img[class|src|srcset|alt|width|height]';
	    return $options;
		
	}
	
    /**
    * add_image_srcset
    *
    * @Called By Filter: 'post_thumbnail_html'
    * 
    * Create our srcset attribute for images called via get_the_post_thumbnail(), ect
    * 
    * @return	string	$html
    * @access 	public
    * @author	Ben Moody
    */
	public function add_image_srcset( $html, $post_id, $post_thumbnail_id, $size, $attr ) {
    	
    	//Loop group sizes and get final html for image tag
	    $html = $this->render_img_tag_html( $html, $size, $post_thumbnail_id );
		
        return $html;
    }
	
	/**
    * add_media_image_tag_srcset
    *
    * @Called By Filter: 'get_image_tag'
    * @Called By Filter: 'image_send_to_editor'
    * 
    * Create our srcset attribute for images added into content via tinymce or visual editor
    * 
    * NOTE: The function looks at the options config to see which image group has been set as the
    * 		default image size group for post/page content images
    * 
    * @return	string	$html
    * @access 	public
    * @author	Ben Moody
    */
    public function add_media_image_tag_srcset( $html, $id, $alt, $title, $align, $size ){
    	
    	//Init vars
    	$post_group_size 	= NULL; //Get this from config options self::$class_config['post_img_group']
    	$srcset 			= array();
    	
    	//Get post image group from config
    	if( isset(self::$class_config['post_img_group']) && !empty(self::$class_config['post_img_group']) ) {
	    	$post_group_size = self::$class_config['post_img_group'];
    	}
    	
    	//Loop group sizes and get final html for image tag
	    $html = $this->render_img_tag_html( $html, $post_group_size, $id );
		
        return $html;
    }
    
    /**
    * add_image_srcset
    *
    * @Called By : $this->add_image_srcset()
    * @Called By : $this->add_media_image_tag_srcset()
    * 
    * Helper to loop image group data and return the img tag html along with srcset attr
    * 
    * @param	string 	$html
    * @param	string 	$img_size_slug 		//Image size slug of source image
    * @param	int		$post_thumbnail_id	//either image id for post thumnail id for image to process
    * @return	string	$html
    * @access 	public static
    * @author	Ben Moody
    */
    public static function render_img_tag_html( $html = NULL, $img_size_slug = NULL, $post_thumbnail_id = NULL ) {
		
		//Init vars
		$srcset = array();
		
		//Check if requested size is one of our image groups
    	if( isset(static::$class_config['img_groups'][$img_size_slug]) ) {
	    	
	    	foreach( static::$class_config['img_groups'][$img_size_slug] as $breakpoint => $image_data ) {
		    	
		    	//Cache unique image name based on group title and breakpoint
			    $image_name = $image_data['thumb_size'];
			   
			    //Check if this is a retina image create a x1 version as well as x2
			    if( (bool)$image_data['retina'] === TRUE ) {
				    
				    //x2 (use original size supplied by user)
				    $image_name = "{$image_name}-@2";
				    
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
		    	$html = str_replace( '/>', ' srcset="'.$_srcset.'"/>', $html );
		    	
	    	}
	    	
    	}
		
		return $html;
	}
    
    /**
    * register_image_sizes
    *
    * @Called By: $this->init_plugin()
    * 
    * Loop all image groups in congif options. Creating new image sizes in wordpress api for each
    * breakpoint within that image group
    * 
    * NOTE: Also handles Retina (x2) images if user has marked group as such.
    *		If marked as retina the supplied size is assumed to be the x2 version. So we register this
    *		then also register a x1 version at 1/2 the width and height.
    * 
    * @access 	private
    * @author	Ben Moody
    */
    private function register_image_sizes() {
	    
	    //Init vars
	    $image_name = NULL;
	    $image_w	= NULL;
	    $image_h	= NULL;
	    
	    //Loop image groups and setup image sizes
	    if( !empty(self::$class_config['img_groups']) && is_array(self::$class_config['img_groups']) ) {
		    
		    foreach( self::$class_config['img_groups'] as $group_title => $breakpoint_sizes ) {
		    
			    //Loop breakpoint image sizes in this group
			    if( !empty($breakpoint_sizes) ) {
			    
				    foreach( $breakpoint_sizes as $breakpoint => $image_data ) {
					    
					    //Confirm this is a custom image size and not existing image size
					    if( isset($image_data['w'], $image_data['h']) ) {
						    
						    //Cache unique image name based on group title and breakpoint
						    $image_name = "{$group_title}-{$breakpoint}";
						    
						    //Cache custom image size name
						    self::$class_config['img_groups'][ $group_title ][ $breakpoint ]['thumb_size'] = $image_name;
						    
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



