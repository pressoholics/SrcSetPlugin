<?php

/**
	ReduxFramework Config File
	For full documentation, please visit: https://github.com/ReduxFramework/ReduxFramework/wiki
**/

if ( !class_exists( "ReduxFramework" ) ) {
	return;
} 

if ( !class_exists( "TNSrcSetOptions" ) ) {
	class TNSrcSetOptions {

		public $args = array();
		public $sections = array();
		public $theme;
		public $ReduxFramework;
		public $text_domain 	= TNSRCSET__DOMAIN;
		public $options_name 	= TNSRCSET__OPTIONS_NAME;

		public function __construct( ) {

			// Just for demo purposes. Not needed per say.
			$this->theme = wp_get_theme();

			// Set the default arguments
			$this->setArguments();
			
			// Set a few help tabs so you can see how it's done
			$this->setHelpTabs();

			// Create the sections and fields
			$this->setSections();
			
			if ( !isset( $this->args['opt_name'] ) ) { // No errors please
				return;
			}
			
			$this->ReduxFramework = new ReduxFramework($this->sections, $this->args);

			// If Redux is running as a plugin, this will remove the demo notice and links
			add_action( 'redux/plugin/hooks', array( $this, 'remove_demo' ) );
			
			// Function to test the compiler hook and demo CSS output.
			//add_filter('redux/options/'.$this->args['opt_name'].'/compiler', array( $this, 'compiler_action' ), 10, 2); 
			// Above 10 is a priority, but 2 in necessary to include the dynamically generated CSS to be sent to the function.

			// Change the arguments after they've been declared, but before the panel is created
			//add_filter('redux/options/'.$this->args['opt_name'].'/args', array( $this, 'change_arguments' ) );
			
			// Change the default value of a field after it's been set, but before it's been used
			//add_filter('redux/options/'.$this->args['opt_name'].'/defaults', array( $this,'change_defaults' ) );

			// Dynamically add a section. Can be also used to modify sections/fields
			add_filter('redux/options/'.$this->args['opt_name'].'/sections', array( $this, 'dynamic_section' ) );

		}


		/**

			This is a test function that will let you see when the compiler hook occurs. 
			It only runs if a field	set with compiler=>true is changed.

		**/

		function compiler_action($options, $css) {
			echo "<h1>The compiler hook has run!";
			//print_r($options); //Option values
			
			// print_r($css); // Compiler selector CSS values  compiler => array( CSS SELECTORS )
			/*
			// Demo of how to use the dynamic CSS and write your own static CSS file
		    $filename = dirname(__FILE__) . '/style' . '.css';
		    global $wp_filesystem;
		    if( empty( $wp_filesystem ) ) {
		        require_once( ABSPATH .'/wp-admin/includes/file.php' );
		        WP_Filesystem();
		    }

		    if( $wp_filesystem ) {
		        $wp_filesystem->put_contents(
		            $filename,
		            $css,
		            FS_CHMOD_FILE // predefined mode settings for WP files
		        );
		    }
			*/
		}



		/**
		 
		 	Custom function for filtering the sections array. Good for child themes to override or add to the sections.
		 	Simply include this function in the child themes functions.php file.
		 
		 	NOTE: the defined constants for URLs, and directories will NOT be available at this point in a child theme,
		 	so you must use get_template_directory_uri() if you want to use any of the built in icons
		 
		 **/

		function dynamic_section($sections){
		    //$sections = array();
		    $sections[] = array(
		        'title' => __('Section via hook', $this->text_domain),
		        'desc' => __('<p class="description">This is a section created by adding a filter to the sections array. Can be used by child themes to add/remove sections from the options.</p>', $this->text_domain),
				'icon' => 'el-icon-paper-clip',
				    // Leave this as a blank section, no options just some intro text set above.
		        'fields' => array()
		    );

		    return $sections;
		}
		
		
		/**

			Filter hook for filtering the args. Good for child themes to override or add to the args array. Can also be used in other functions.

		**/
		
		function change_arguments($args){
		    //$args['dev_mode'] = true;
		    
		    return $args;
		}
			
		
		/**

			Filter hook for filtering the default value of any given field. Very useful in development mode.

		**/

		function change_defaults($defaults){
		    $defaults['str_replace'] = "Testing filter hook!";
		    
		    return $defaults;
		}


		// Remove the demo link and the notice of integrated demo from the redux-framework plugin
		function remove_demo() {
			
			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if ( class_exists('ReduxFrameworkPlugin') ) {
				remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::get_instance(), 'plugin_meta_demo_mode_link'), null, 2 );
			}

			// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
			remove_action('admin_notices', array( ReduxFrameworkPlugin::get_instance(), 'admin_notices' ) );	

		}


		public function setSections() {

			/**
			 	Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
			 **/


			// Background Patterns Reader
			$sample_patterns_path = ReduxFramework::$_dir . '../sample/patterns/';
			$sample_patterns_url  = ReduxFramework::$_url . '../sample/patterns/';
			$sample_patterns      = array();

			if ( is_dir( $sample_patterns_path ) ) :
				
			  if ( $sample_patterns_dir = opendir( $sample_patterns_path ) ) :
			  	$sample_patterns = array();

			    while ( ( $sample_patterns_file = readdir( $sample_patterns_dir ) ) !== false ) {

			      if( stristr( $sample_patterns_file, '.png' ) !== false || stristr( $sample_patterns_file, '.jpg' ) !== false ) {
			      	$name = explode(".", $sample_patterns_file);
			      	$name = str_replace('.'.end($name), '', $sample_patterns_file);
			      	$sample_patterns[] = array( 'alt'=>$name,'img' => $sample_patterns_url . $sample_patterns_file );
			      }
			    }
			  endif;
			endif;

			ob_start();

			$ct = wp_get_theme();
			$this->theme = $ct;
			$item_name = $this->theme->get('Name'); 
			$tags = $this->theme->Tags;
			$screenshot = $this->theme->get_screenshot();
			$class = $screenshot ? 'has-screenshot' : '';

			$customize_title = sprintf( __( 'Customize &#8220;%s&#8221;',$this->text_domain ), $this->theme->display('Name') );

			?>
			<div id="current-theme" class="<?php echo esc_attr( $class ); ?>">
				<?php if ( $screenshot ) : ?>
					<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
					<a href="<?php echo wp_customize_url(); ?>" class="load-customize hide-if-no-customize" title="<?php echo esc_attr( $customize_title ); ?>">
						<img src="<?php echo esc_url( $screenshot ); ?>" alt="<?php esc_attr_e( 'Current theme preview' ); ?>" />
					</a>
					<?php endif; ?>
					<img class="hide-if-customize" src="<?php echo esc_url( $screenshot ); ?>" alt="<?php esc_attr_e( 'Current theme preview' ); ?>" />
				<?php endif; ?>

				<h4>
					<?php echo $this->theme->display('Name'); ?>
				</h4>

				<div>
					<ul class="theme-info">
						<li><?php printf( __('By %s',$this->text_domain), $this->theme->display('Author') ); ?></li>
						<li><?php printf( __('Version %s',$this->text_domain), $this->theme->display('Version') ); ?></li>
						<li><?php echo '<strong>'.__('Tags', $this->text_domain).':</strong> '; ?><?php printf( $this->theme->display('Tags') ); ?></li>
					</ul>
					<p class="theme-description"><?php echo $this->theme->display('Description'); ?></p>
					<?php if ( $this->theme->parent() ) {
						printf( ' <p class="howto">' . __( 'This <a href="%1$s">child theme</a> requires its parent theme, %2$s.' ) . '</p>',
							__( 'http://codex.wordpress.org/Child_Themes',$this->text_domain ),
							$this->theme->parent()->display( 'Name' ) );
					} ?>
					
				</div>

			</div>

			<?php
			$item_info = ob_get_contents();
			    
			ob_end_clean();

			$sampleHTML = '';
			if( file_exists( dirname(__FILE__).'/info-html.html' )) {
				/** @global WP_Filesystem_Direct $wp_filesystem  */
				global $wp_filesystem;
				if (empty($wp_filesystem)) {
					require_once(ABSPATH .'/wp-admin/includes/file.php');
					WP_Filesystem();
				}  		
				$sampleHTML = $wp_filesystem->get_contents(dirname(__FILE__).'/info-html.html');
			}


			//Add dropdown to select which group to use for post content
			global $tnsrc_set_options;

			if( isset($tnsrc_set_options['img_groups_instances']) && !empty($tnsrc_set_options['img_groups_instances']) ){
				
				//INit vars
				$group_select		= array();
				
				foreach( $tnsrc_set_options['img_groups_instances'] as $_img_group ) {
					$group_select[$_img_group] = $_img_group;
				}
				
				/*  --- TO REMOVE!!
				$post_group_select = array(
					'id'       => 'post_img_group',
				    'type'     => 'select',
				    'title'    => __('Post Content Group', $this->text_domain),
				    'subtitle' => __('Image size group used when users insert images into post content', $this->text_domain),
				    'desc'     => __('Select group to use for post/page content', $this->text_domain),
				    // Must provide key => value pairs for select options
				    'options'  => $group_select,
				);
				 --- TO REMOVE!! */
				
			}

			// ACTUAL DECLARATION OF SECTIONS
			$this->sections[] = array(
				'title' => __('General', $this->text_domain),
				'desc' => __('Create a SrcSet group by adding a title below, then "Save Changes". New options will be created in the sidebar on the left for each group added.', $this->text_domain),
				'icon' => 'el-icon-home',
			    // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
				'fields' => array(
					array(
						'id'=>'img_groups_instances',
						'type' => 'multi_text',
						'title' => __('Manage SrcSet Groups', $this->text_domain),
						'validate' => 'no_special_chars',
						'subtitle' => __('Add and remove SrcSet groups. Enter title for each group then click "Save Changes" to create options more options for each group.', $this->text_domain),
						'desc' => __('Add unique title for each group. Tip: Keep it short', $this->text_domain)
					)
				)
			);
			
			//Plugin Help Section
			$this->sections[] = array(
				'title' => __('Help', $this->text_domain),
				'desc' => __('Need assistance setting up SrcSet?', $this->text_domain),
				'icon' => 'el-icon-info-sign',
			    // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
				'fields' => array(
					array(
					    'id'       => 'help-raw',
					    'type'     => 'raw',
					    'title'    => __('Getting Started', 'redux-framework-demo'),
					    'content'  => file_get_contents(dirname(__FILE__) . '/getting-started-raw.txt')
					)
				)
			);
			
			//Check for number of instances requested and build options for each
			if( isset($tnsrc_set_options['img_groups_instances']) ){
				
				//Loop each instance and setup options table for each
				foreach( (array)$tnsrc_set_options['img_groups_instances'] as $group_title ) {
					
					$_group_slug = hash("crc32b", $group_title);
					
					//Create array of image sizes to use in 'Intermidiate Image Size' select menu
					$reg_img_sizes 	= get_intermediate_image_sizes();
					$img_sizes 		= array( 'full' => 'Full Size', 'custom' => 'Custom' );
					foreach( $reg_img_sizes as $size_slug ) {
						$img_sizes[$size_slug] = $size_slug;
					}
					
					//Create array of image sizes to use in 'Custom Image Size Relationship' select menu
					$rel_img_sizes 	= array( 'full' => 'Full Size' );
					foreach( $reg_img_sizes as $size_slug ) {
						$rel_img_sizes[$size_slug] = ucfirst( $size_slug );
					}
					
					$this->sections[] = array(
						'title' => __(ucfirst($group_title).' SrcSet Group', $this->text_domain),
						'desc' => __('Setup image sizes for all breakpoints in this SrcSet group', $this->text_domain),
						'icon' => 'el-icon-cogs',
					    // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
						'fields' => array(
							
							array(
							    'id'       => 'img_size_rel_'.$_group_slug,
							    'class'	   => 'tn-srcset-img-rel',
							    'type'     => 'select',
							    'title'    => __('1. Image Size Relationship', $this->text_domain),
							    'subtitle' => __('Link this SrcSet group with an existing Wordpress image size', $this->text_domain),
							    'desc'     => __('Selected wordpress image size will be associated with this SrcSet group. All images using this size will have this SrcSet group applied to them. <br/><span style="color:red;font-weight:bold;">(An Image size can only be assigned to a single SrcSet group at a time)</span>', $this->text_domain),
							    // Must provide key => value pairs for select options
							    'options'  => $rel_img_sizes,
							    'default'  => 'full',
							),
							
							array(
								'id'=>'size_select'.$_group_slug,
								'type' => 'select',
								'title' => __('2. Select Breakpoint', $this->text_domain),
								'desc' => __('Select a browser width breakpoint to edit', $this->text_domain),
								'options' => array('small' => 'Small','medium' => 'Medium','large' => 'Large','xlarge' => 'X Large'),
								'default' => ''
							),
							
							/** Small breakpoint section **/
							array(
								'id'=>'sec_sm_srt_'.$_group_slug,
								'type' => 'section', 
								'required' => array('size_select'.$_group_slug,'=','small'),
								'title' => '',                            
								'indent' => true // Indent all options below until the next 'section' option is set.
							),
							
								//Breakpoint in px
						        array(
									'id'=>'bp_sm_'.$_group_slug,
									'type' => 'slider', 
									'title' => __('3. Breakpoint Width', $this->text_domain),
									'desc'=> __('Breakpoint width in px', $this->text_domain),
									"default" 	=> "640",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "2000",
								),
								
								
								/** RETINA OPTION
								array(
									'id'=>'x2_sm_'.$_group_slug,
									'type' => 'switch',
									'title' => __('Activate Retina (x2)', $this->text_domain),
									'desc' => __('<span style="color:red;font-weight:bold;">If enabled, be sure to set the Retina (x2) image size below NOT the regular (x1) size. We will create the x1 for you.</span>', $this->text_domain),
									"default" 		=> false,
								),
								**/
								
								array(
								    'id'       => 'fullsz_sm_'.$_group_slug,
								    'type'     => 'select',
								    'title'    => __('4. Breakpoint Image Size', $this->text_domain),
								    'subtitle' => __('Select image size to use for this breakpoint', $this->text_domain),
								    'desc'     => __('You can select an existing Wordpress image size or choose Custom to create a new image size.', $this->text_domain),
								    // Must provide key => value pairs for select options
								    'options'  => $img_sizes,
								    'default'  => 'full',
								),
								
								//Image Width
						        array(
									'id'=>'imgw_sm_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_sm_'.$_group_slug,'=','custom'),
									'title' => __('Image Width', $this->text_domain),
									'desc'=> __('Image width in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
								array(
									'id'=>'imgh_sm_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_sm_'.$_group_slug,'=','custom'),
									'title' => __('Image Height', $this->text_domain),
									'desc'=> __('Image height in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
							array(
								'id'=>'sec_sm_end_'.$_group_slug,
								'type' => 'section', 
								'indent' => false // Indent all options below until the next 'section' option is set.
							),
							
							
							
							
							/** Medium breakpoint section **/
							array(
								'id'=>'sec_med_srt_'.$_group_slug,
								'type' => 'section', 
								'required' => array('size_select'.$_group_slug,'=','medium'),
								'title' => '',                            
								'indent' => true // Indent all options below until the next 'section' option is set.
							),
							
								//Breakpoint in px
						        array(
									'id'=>'bp_med_'.$_group_slug,
									'type' => 'slider', 
									'title' => __('3. Breakpoint Width', $this->text_domain),
									'desc'=> __('Breakpoint in px', $this->text_domain),
									"default" 	=> "1024",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "2000",
								),
								
								
								/** RETINA OPTION
								array(
									'id'=>'x2_med_'.$_group_slug,
									'type' => 'switch',
									'title' => __('Activate Retina (x2)', $this->text_domain),
									'desc' => __('<span style="color:red;font-weight:bold;">If enabled, be sure to set the Retina (x2) image size below NOT the regular (x1) size. We will create the x1 for you.</span>', $this->text_domain),
									"default" 		=> false,
								),
								**/
								
								
								array(
								    'id'       => 'fullsz_med_'.$_group_slug,
								    'type'     => 'select',
								    'title'    => __('4. Breakpoint Image Size', $this->text_domain),
								    'subtitle' => __('Select image size to use for this breakpoint', $this->text_domain),
								    'desc'     => __('You can select an existing Wordpress image size or choose Custom to create a new image size.', $this->text_domain),
								    // Must provide key => value pairs for select options
								    'options'  => $img_sizes,
								    'default'  => 'full',
								),
								
								//Image Width
						        array(
									'id'=>'imgw_med_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_med_'.$_group_slug,'=','custom'),
									'title' => __('Image Width', $this->text_domain),
									'desc'=> __('Image width in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
								array(
									'id'=>'imgh_med_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_med_'.$_group_slug,'=','custom'),
									'title' => __('Image Height', $this->text_domain),
									'desc'=> __('Image height in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
							array(
								'id'=>'sec_med_end_'.$_group_slug,
								'type' => 'section', 
								'indent' => false // Indent all options below until the next 'section' option is set.
							),
							
							
							
							/** Large breakpoint section **/
							array(
								'id'=>'sec_lg_srt_'.$_group_slug,
								'type' => 'section', 
								'required' => array('size_select'.$_group_slug,'=','large'),
								'title' => '',                            
								'indent' => true // Indent all options below until the next 'section' option is set.
							),
							
								//Breakpoint in px
						        array(
									'id'=>'bp_lg_'.$_group_slug,
									'type' => 'slider', 
									'title' => __('3. Breakpoint Width', $this->text_domain),
									'desc'=> __('Breakpoint in px', $this->text_domain),
									"default" 	=> "1440",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "2000",
								),
								
								/** RETINA OPTION
								array(
									'id'=>'x2_lg_'.$_group_slug,
									'type' => 'switch',
									'title' => __('Activate Retina (x2)', $this->text_domain),
									'desc' => __('<span style="color:red;font-weight:bold;">If enabled, be sure to set the Retina (x2) image size below NOT the regular (x1) size. We will create the x1 for you.</span>', $this->text_domain),
									"default" 		=> false,
								),
								**/
								
								array(
								    'id'       => 'fullsz_lg_'.$_group_slug,
								    'type'     => 'select',
								    'title'    => __('4. Breakpoint Image Size', $this->text_domain),
								    'subtitle' => __('Select image size to use for this breakpoint', $this->text_domain),
								    'desc'     => __('You can select an existing Wordpress image size or choose Custom to create a new image size.', $this->text_domain),
								    // Must provide key => value pairs for select options
								    'options'  => $img_sizes,
								    'default'  => 'full',
								),
								
								//Image Width
						        array(
									'id'=>'imgw_lg_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_lg_'.$_group_slug,'=','custom'),
									'title' => __('Image Width', $this->text_domain),
									'desc'=> __('Image width in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
								array(
									'id'=>'imgh_lg_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_lg_'.$_group_slug,'=','custom'),
									'title' => __('Image Height', $this->text_domain),
									'desc'=> __('Image height in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
							array(
								'id'=>'sec_lg_end_'.$_group_slug,
								'type' => 'section', 
								'indent' => false // Indent all options below until the next 'section' option is set.
							),
							
							/** X Large breakpoint section **/
							array(
								'id'=>'sec_xl_srt_'.$_group_slug,
								'type' => 'section', 
								'required' => array('size_select'.$_group_slug,'=','xlarge'),
								'title' => '',                            
								'indent' => true // Indent all options below until the next 'section' option is set.
							),
							
								//Breakpoint in px
						        array(
									'id'=>'bp_xl_'.$_group_slug,
									'type' => 'slider', 
									'title' => __('3. Breakpoint Width', $this->text_domain),
									'desc'=> __('Breakpoint in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "2000",
								),
								
								/** RETINA OPTION
								array(
									'id'=>'x2_xl_'.$_group_slug,
									'type' => 'switch', 
									'title' => __('Activate Retina (x2)', $this->text_domain),
									'desc' => __('<span style="color:red;font-weight:bold;">If enabled, be sure to set the Retina (x2) image size below NOT the regular (x1) size. We will create the x1 for you.</span>', $this->text_domain),
									"default" 		=> false,
								),
								**/
								
								array(
								    'id'       => 'fullsz_xl_'.$_group_slug,
								    'type'     => 'select',
								    'title'    => __('4. Breakpoint Image Size', $this->text_domain),
								    'subtitle' => __('Select image size to use for this breakpoint', $this->text_domain),
								    'desc'     => __('You can select an existing Wordpress image size or choose Custom to create a new image size.', $this->text_domain),
								    // Must provide key => value pairs for select options
								    'options'  => $img_sizes,
								    'default'  => 'full',
								),
								
								//Image Width
						        array(
									'id'=>'imgw_xl_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_xl_'.$_group_slug,'=','custom'),
									'title' => __('Image Width', $this->text_domain),
									'desc'=> __('Image width in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
								array(
									'id'=>'imgh_xl_'.$_group_slug,
									'type' => 'slider', 
									'required' => array('fullsz_xl_'.$_group_slug,'=','custom'),
									'title' => __('Image Height', $this->text_domain),
									'desc'=> __('Image height in px', $this->text_domain),
									"default" 	=> "1920",
									"min" 		=> "1",
									"step"		=> "1",
									"max" 		=> "5000",
								),
								
							array(
								'id'=>'sec_xl_end_'.$_group_slug,
								'type' => 'section', 
								'indent' => false // Indent all options below until the next 'section' option is set.
							),

							
						)
					);
					
				}
				
			}
			
			// Regeneration
			if ( class_exists( 'TNSrcSetRegen' ) ) {
				$this->sections[] = array(
					'title' => __('Regeneration', $this->text_domain),
					'desc' => __('Regenerate srcset attributes of all images within post content. <strong>THIS IS NOT REVERSABLE</strong>.', $this->text_domain),
					'icon' => 'el-icon-refresh',
					// 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
					'fields' => array(
						array(
							'id'=>'img_groups_instances',
							'type' => 'callback',
							'title' => __('Manage SrcSet Groups', $this->text_domain),
							'callback' => array( 'TNSrcSetRegen', 'render_regeneration_section' )
						)
					)
				);
			}

			if(file_exists(trailingslashit(dirname(__FILE__)) . 'README.html')) {
			    $tabs['docs'] = array(
					'icon' => 'el-icon-book',
					    'title' => __('Documentation', $this->text_domain),
			        'content' => nl2br(file_get_contents(trailingslashit(dirname(__FILE__)) . 'README.html'))
			    );
			}

		}	

		public function setHelpTabs() {

			// Custom page help tabs, displayed using the help API. Tabs are shown in order of definition.
			$this->args['help_tabs'][] = array(
			    'id' => 'redux-opts-1',
			    'title' => __('Theme Information 1', $this->text_domain),
			    'content' => __('<p>This is the tab content, HTML is allowed.</p>', $this->text_domain)
			);

			$this->args['help_tabs'][] = array(
			    'id' => 'redux-opts-2',
			    'title' => __('Theme Information 2', $this->text_domain),
			    'content' => __('<p>This is the tab content, HTML is allowed.</p>', $this->text_domain)
			);

			// Set the help sidebar
			$this->args['help_sidebar'] = __('<p>This is the sidebar content, HTML is allowed.</p>', $this->text_domain);

		}


		/**
			
			All the possible arguments for Redux.
			For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments

		 **/
		public function setArguments() {
			
			$theme = wp_get_theme(); // For use with some settings. Not necessary.

			$this->args = array(
	            
	            // TYPICAL -> Change these values as you need/desire
				'opt_name'          	=> $this->options_name, // This is where your data is stored in the database and also becomes your global variable name.
				'display_name'			=> 'TrueNorth SrcSet Plugin', // Name that appears at the top of your panel
				'display_version'		=> '1.0', // Version that appears at the top of your panel
				'menu_type'          	=> 'menu', //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
				'allow_sub_menu'     	=> true, // Show the sections below the admin menu item or not
				'menu_title'			=> __( 'SrcSet Settings', $this->text_domain ),
	            'page'		 	 		=> __( 'SrcSet Settings', $this->text_domain ),
	            'google_api_key'   	 	=> '', // Must be defined to add google fonts to the typography module
	            'global_variable'    	=> '', // Set a different name for your global variable other than the opt_name
	            'dev_mode'           	=> false, // Show the time the page took to load, etc
	            'customizer'         	=> false, // Enable basic customizer support

	            // OPTIONAL -> Give you extra features
	            'page_priority'      	=> null, // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
	            'page_type'   			=> 'submenu', // set to “menu” for a top level menu, or “submenu” to add below an existing item
	            'page_parent'        	=> 'tools.php', // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
	            'page_permissions'   	=> 'manage_options', // Permissions needed to access the options panel.
	            'menu_icon'          	=> '', // Specify a custom URL to an icon
	            'last_tab'           	=> '', // Force your panel to always open to a specific tab (by id)
	            'page_icon'          	=> 'icon-themes', // Icon displayed in the admin panel next to your menu_title
	            'page_slug'          	=> $this->options_name.'_options', // Page slug used to denote the panel
	            'save_defaults'      	=> true, // On load save the defaults to DB before user clicks save or not
	            'default_show'       	=> false, // If true, shows the default value next to each field that is not the default value.
	            'default_mark'       	=> '', // What to print by the field's title if the value shown is default. Suggested: *


	            // CAREFUL -> These options are for advanced use only
	            'transient_time' 	 	=> 60 * MINUTE_IN_SECONDS,
	            'output'            	=> true, // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
	            'output_tag'            	=> true, // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
	            //'domain'             	=> 'redux-framework', // Translation domain key. Don't change this unless you want to retranslate all of Redux.
	            //'footer_credit'      	=> '', // Disable the footer credit of Redux. Please leave if you can help it.
	            

	            // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
	            'database'           	=> '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
	            
	        
	            'show_import_export' 	=> true, // REMOVE
	            'system_info'        	=> false, // REMOVE
	            
	            'help_tabs'          	=> array(),
	            'help_sidebar'       	=> '', // __( '', $this->args['domain'] );            
				);


			// SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.		
			$this->args['share_icons'][] = array(
			    'url' => 'https://github.com/ReduxFramework/ReduxFramework',
			    'title' => 'Visit us on GitHub', 
			    'icon' => 'el-icon-github'
			    // 'img' => '', // You can use icon OR img. IMG needs to be a full URL.
			);		
			$this->args['share_icons'][] = array(
			    'url' => 'https://www.facebook.com/pages/Redux-Framework/243141545850368',
			    'title' => 'Like us on Facebook', 
			    'icon' => 'el-icon-facebook'
			);
			$this->args['share_icons'][] = array(
			    'url' => 'http://twitter.com/reduxframework',
			    'title' => 'Follow us on Twitter', 
			    'icon' => 'el-icon-twitter'
			);
			$this->args['share_icons'][] = array(
			    'url' => 'http://www.linkedin.com/company/redux-framework',
			    'title' => 'Find us on LinkedIn', 
			    'icon' => 'el-icon-linkedin'
			);

			
	 
			// Panel Intro text -> before the form
			if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false ) {
				if (!empty($this->args['global_variable'])) {
					$v = $this->args['global_variable'];
				} else {
					$v = str_replace("-", "_", $this->args['opt_name']);
				}
				$this->args['intro_text'] = sprintf( __('<p>Did you know that Redux sets a global variable for you? To access any of your saved options from within your code you can use your global variable: <strong>$%1$s</strong></p>', $this->text_domain ), $v );
			} else {
				//$this->args['intro_text'] = __('<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', $this->text_domain);
			}

			// Add content after the form.
			//$this->args['footer_text'] = __('<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', $this->text_domain);

		}
	}
	new TNSrcSetOptions();

}


/** 

	Custom function for the callback referenced above

 */
if ( !function_exists( 'redux_my_custom_field' ) ):
	function redux_my_custom_field($field, $value) {
	    print_r($field);
	    print_r($value);
	}
endif;

/**
 
	Custom function for the callback validation referenced above

**/
if ( !function_exists( 'redux_validate_callback_function' ) ):
	function redux_validate_callback_function($field, $value, $existing_value) {
	    $error = false;
	    $value =  'just testing';
	    /*
	    do your validation
	    
	    if(something) {
	        $value = $value;
	    } elseif(something else) {
	        $error = true;
	        $value = $existing_value;
	        $field['msg'] = 'your custom error message';
	    }
	    */
	    
	    $return['value'] = $value;
	    if($error == true) {
	        $return['error'] = $field;
	    }
	    return $return;
	}
endif;
