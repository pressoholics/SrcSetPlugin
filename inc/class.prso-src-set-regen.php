<?php
/**
 * Class: PrsoSrcSetRegen
 * 
 * This class handles the regeneration of srcset attributes within post content.
 *
 * @author Eric
 */
class PrsoSrcSetRegen {
	
	const REGEN_AJAX_ACTION = 'prso-srcset-regenerate';
	
	public static function render_regeneration_section( $field, $value ) {
		
	?>
	<h3>Warning:</h3>
	<p><strong>This feature directly modifies your post content HTML. It is highly recommended that you do a database backup before running this feature.</strong></p>
		
	<p><a class="button button-primary os-srcset-regen" rel="regenerate-srcset" href="javascript:void(0);"><?php _ex( 'Regenerate srcset', 'text', PRSOSRCSET__DOMAIN ); ?></a></p>
	<span class="spinner pull-content" style="float:left;"></span>
	<div id="os-srcset-regen-status">
		<p class="progress"></p>
		<p class="message"></p>
	</div>
	<?php

	}
	
	
	function __construct() {
		// Create submenu page for srcset regeneration
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 20 );
		
		// Scripts for the Regeneration
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		
		// AJAX Listener
		add_action( 'wp_ajax_' . self::REGEN_AJAX_ACTION, array( $this, 'ajax_regenerate_batch' ) );
	}

	function add_submenu_page() {
		add_submenu_page(
			'prso_src_set_options_options',
			__('SrcSet Regeneration'),
			__('SrcSet Regeneration'),
			'manage_options',
			'prso_src_set_regenerate',
			array( $this, 'regen_srcset_page' ) );
	}
	
	function regen_srcset_page() { 
		global $post;
		?>
	<div id="wrap">
		<h2>SrcSet Regeneration</h2>
		<p><strong>Warning:</strong> This feature is experimental. It is required that you understand the consequences of directly manipulating page content.</p>
		<p><strong>It is highly recommended that you do a database backup before running this feature.</strong></p>
		<?php if ( filter_input( INPUT_GET, 'start' ) ) {
		$query_args = array(
			'post_type' => 'any',
			'posts_per_page' => 1000
		);
		$query = new WP_Query( $query_args );
		// echo '<pre>', print_r( $query, true ), '</pre>';
		?>
		<br/>
		<h3>Total Posts: <?php echo $query->post_count ?></h3>
		<?php
		$images = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$has_images = preg_match_all( '/<img[\w\W]+?>/i', $post->post_content, $images );
			echo '<pre>',print_r( $images, true ), '</pre>';
			$content = $post->post_content;
			?>
		<h5>Post (ID <?php the_ID() ?>): <?php the_title() ?></h5>
		<p><?php echo $has_images ? 'Matches: ' . count( $images[0] ) : 'No matches' ?></p>
			<?php
			foreach ( $images[0] as $image ) {
				$is_attachment = preg_match( '/class=\"[\w\W]*?wp-image-(\d+)[\w\W]*?\"/i', $image, $id_matches );
				
				// We are only dealing with WP Attachments
				if ( $is_attachment ) {
					echo "<hr/><h4>New Attachment</h4>";
					echo '<p> Attachment ID is ', $id_matches[1];
					echo '<pre>', esc_html( $image ), '</pre>';
				
					// Strip old srcset attribute if exists
					$stripped_image = preg_replace( '/\s*srcset=\"[\w\W]*?\"/i', '', $image );
					
					// todo: generate srcset
					$new_image = PrsoSrcSet::render_img_tag_html( $stripped_image, 'Full', $id_matches[1]);
					echo "<h4>New HTML</h4>", '<pre>', esc_html( $new_image ), '</pre>';
					
					$content = str_replace( $image, $new_image, $content );
				}
			}
			echo '<p><strong>', ( wp_update_post( array( 'ID' => get_the_ID(), 'post_content' => $content ) ) ? 'Updated!' : "Error" ), '</strong></p>';
		}
		?>
		<?php } else { ?>
		<a class="button-primary" href="<?php echo add_query_arg('start', 1 ) ?>">Regenerate srcset in <strong>ALL</strong> post content</a>
		<?php } ?>
	</div>
	<?php
	}
	
	function enqueue_admin_scripts(){
		wp_enqueue_script( 'prso-srcset-admin', plugin_dir_url( __FILE__ ). '/js/prso-srcset-admin.js', array( 'jquery' ), '1.0', true );
		wp_localize_script( 'prso-srcset-admin', 'PrsoSrcSetData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'ajaxAction' => self::REGEN_AJAX_ACTION,
			'messages' => array(
				'start' => __( 'Starting&hellip;', PRSOSRCSET__DOMAIN ),
				'progress' => __( 'Progress: %d/%d', PRSOSRCSET__DOMAIN ),
				'complete' => __( 'Complete!', PRSOSRCSET__DOMAIN ),
				'error' => __( 'Something went wrong. Please try again.', PRSOSRCSET__DOMAIN )
			)
		) );
	}
	
	/**
	 * AJAX Request to regenerate a batch of posts.
	 * @global type $post
	 */
	function ajax_regenerate_batch(){
		global $post;
		
		$current_page = filter_input( INPUT_POST, 'current_page', FILTER_SANITIZE_NUMBER_INT );
		
		$query_args = array(
			'post_type' => 'any',
			'posts_per_page' => 1,
			'page' => $current_page
		);
		
		$query = new WP_Query( $query_args );
		
		$images = array();
		
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
					// Strip old srcset attribute if exists
					$stripped_image = preg_replace( '/\s*srcset=\"[\w\W]*?\"/i', '', $image );
					
					// Generate the new srcset attribute
					$new_image = PrsoSrcSet::render_img_tag_html( $stripped_image, 'Full', $id_matches[1]);
					
					// Replace the old <img> tag with the new one
					$content = str_replace( $image, $new_image, $content );
				}
			}
			if ( ! wp_update_post( array( 'ID' => get_the_ID(), 'post_content' => $content ) ) ) {
				// Something went wrong
				
			}
		}
		wp_reset_query();
		wp_send_json_success( array( 
			'totalPosts' => $query->found_posts,
			'postsPerPage' => $query->get( 'posts_per_page' )
		) );
	}
}
new PrsoSrcSetRegen;