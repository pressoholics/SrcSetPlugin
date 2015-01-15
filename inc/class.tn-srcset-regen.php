<?php
/**
 * Class: TNSrcSetRegen
 * 
 * This class handles the regeneration of srcset attributes within post content.
 *
 * @author Eric
 */
class TNSrcSetRegen {
	
	const REGEN_AJAX_ACTION = 'tn-srcset-regenerate';
	
	/**
	 * This is the callback for a Redux framework section. Here we will regenerate
	 * srcset within post content.
	 * 
	 * @param array $field
	 * @param array $value
	 */
	public static function render_regeneration_section( $field, $value ) {
		?>
		<h3><?php _e( "Warning:", TNSRCSET__DOMAIN ) ?></h3>
		<p><strong><?php _e( "This feature directly modifies your post content HTML. It is highly recommended that you do a database backup before running this feature.", TNSRCSET__DOMAIN ) ?></strong></p>
		
		<br>
		
		 <select class='uninstall-srcset'>
		  <option value="regen"><?php _ex( 'Regenerate srcset', 'text', TNSRCSET__DOMAIN ); ?></option>
		  <option value="uninstall"><?php _ex( 'Uninstall srcset', 'text', TNSRCSET__DOMAIN ); ?></option>
		</select>
		
		<br><br>
		
		<p><a class="button button-primary os-srcset-regen" rel="regenerate-srcset" href="javascript:void(0);"><?php _ex( 'Regenerate srcset', 'text', TNSRCSET__DOMAIN ); ?></a></p>
		<span class="spinner pull-content" style="float:left;"></span>
		<div id="os-srcset-regen-status">
			<p class="progress"></p>
			<p class="message"></p>
		</div>
		<?php
	}
	
	
	function __construct() {
		// Scripts for the Regeneration
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		
		// AJAX Listener
		add_action( 'wp_ajax_' . self::REGEN_AJAX_ACTION, array( $this, 'ajax_regenerate_batch' ) );
	}
	
	function enqueue_admin_scripts(){
		wp_enqueue_script( 'tn-srcset-admin', plugin_dir_url( __FILE__ ). '/js/tn-srcset-admin.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'tn-srcset-admin', 'TNSrcSetData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'ajaxAction' => self::REGEN_AJAX_ACTION,
			'messages' => array(
				'start' => __( 'Starting&hellip;', TNSRCSET__DOMAIN ),
				'progress' => __( 'Progress: %d/%d', TNSRCSET__DOMAIN ),
				'complete' => __( 'Complete!', TNSRCSET__DOMAIN ),
				'error' => __( 'Something went wrong. Please try again.', TNSRCSET__DOMAIN )
			)
		) );
	}
	
	/**
	 * AJAX Request to regenerate a batch of posts.
	 * @global type $post
	 */
	function ajax_regenerate_batch(){
		global $post;
		
		$current_page 	= filter_input( INPUT_POST, 'current_page', FILTER_SANITIZE_NUMBER_INT );
		$ren_action 	= esc_attr( $_REQUEST['regen_action'] );
		
		$output = array();
		$images = array();
		
		$post_types = get_post_types( array( 'public' => true ) );
		
		// Remove Attachments
		unset($post_types['attachment']);
		
		$post_types = apply_filters( 'tn_srcset_regen_post_types', $post_types, $current_page );
		
		$output['postTypes'] = $post_types;
		
		$query_args = array(
			'post_type' => $post_types,
			'posts_per_page' => 50,
			'page' => $current_page
		);
		
		$query = new WP_Query( $query_args );
		
		$output['totalPosts'] = $query->found_posts;
		$output['postsPerPage'] = $query->get( 'posts_per_page' );
		
		
		while ( $query->have_posts() ) {
			$query->the_post();
			
			// Get raw post content
			$content = $post->post_content;
			
			$has_images = preg_match_all( '/<img[\w\W]+?>/i', $content, $images );
			
			// If no images found, skip this one
			if ( ! $has_images )  {
				continue;
			}
			
			foreach ( $images[0] as $image ) {
				// Pattern that reads the attachment ID from inline image
				$is_attachment = preg_match( '/class=\"[\w\W]*?wp-image-(\d+)[\w\W]*?\"/i', $image, $id_matches );
				
				// We are only dealing with WP Attachments
				if ( $is_attachment ) {
					
					//Check reg action 
					if( $ren_action === 'uninstall' ) {
						
						//Unistall srcset across post
						$content = $this->get_uninstalled_srcset_content( $image, $id_matches, $content );
						
					} else {
						
						//Regenerate srcset across post
						$content = $this->get_regen_content( $image, $id_matches, $content );
						
					}
					
					
				}
			}
			
			if ( ! wp_update_post( array( 'ID' => get_the_ID(), 'post_content' => $content ) ) ) {
				// Something went wrong
				
			}
		}
		wp_reset_query();
		wp_send_json_success( $output );
	}
	
	/**
	* get_regen_content
	* 
	* Helper to find the image size from it's class, strip out any srcset attr,
	* Then call the method to regenerate the srcset attr for the image size in question.
	* Finally replace the old img tag with the new updated tag
	* 
	* @param	string	$image
	* @param	array	$id_matches
	* @param	string	$content
	* @return	string	$content
	* @access 	private
	* @author	Ben Moody
	*/
	private function get_regen_content( $image, $id_matches, $content ) {
		
		//Init vars
		$size_matches	= NULL;
		$has_size		= NULL;
		$stripped_image	= NULL;
		$new_image		= NULL;
		
		// retrieve image size
		$has_size = preg_match( '/class=\"[\w\W]*?size-(\w+)[\w\W]*?\"/i', $image, $size_matches );
	
		// Strip old srcset attribute if exists
		$stripped_image = preg_replace( '/\s*srcset=\"[\w\W]*?\"/i', '', $image );
		
		// Generate the new srcset attribute
		$new_image = TNSrcSet::render_img_tag_html( $stripped_image, $size_matches[1], $id_matches[1]);
		
		// Replace the old <img> tag with the new one
		$content = str_replace( $image, $new_image, $content );
		
		return $content;
	}
	
	/**
	* get_uninstalled_srcset_content
	* 
	* Helper to find a srcset tag on an image and remove it. Then replace the img tag in the content
	* 
	* @param	string	$image
	* @param	array	$id_matches
	* @param	string	$content
	* @return	string	$content
	* @access 	private
	* @author	Ben Moody
	*/
	private function get_uninstalled_srcset_content( $image, $id_matches, $content ) {
		
		//Init vars
		$stripped_image	= NULL;
	
		// Strip old srcset attribute if exists
		$stripped_image = preg_replace( '/\s*srcset=\"[\w\W]*?\"/i', '', $image );
		
		// Replace the old <img> tag with the new one
		$content = str_replace( $image, $stripped_image, $content );
		
		return $content;
	}
	
}
new TNSrcSetRegen;