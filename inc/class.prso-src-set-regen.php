<?php
/**
 * Class: PrsoSrcSetRegen
 * 
 * This class handles the regeneration of srcset attributes within post content.
 *
 * @author Eric
 */
class PrsoSrcSetRegen {
	
	function __construct() {
		// Create submenu page for srcset regeneration
		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 20 );
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
		<?php if ( filter_input( INPUT_GET, 'start' ) ) { ?>
		<p>TODO: actually do stuff.</p>
		
		<p>This is the regex to select images from HTML.</p>
		<pre><?php echo esc_html('(<img[\w\W]+?>)' ) ?></pre>
		<br/>
		<p>This is the regex to select attachment ID from an image.</p>
		<pre><?php echo esc_html('wp-image-(\d+)' ) ?></pre>
		<p>Enhanced version:
		<pre><?php echo esc_html('class=\"[\w\W]*?wp-image-(\d+)[\w\W]*?\"' ) ?></pre>
		<br/>
		<p>This is the regex to select attachment size from an image.</p>
		<pre><?php echo esc_html('size-(\w+)' ) ?></pre>
		<p>Enhanced version:
		<pre><?php echo esc_html('class=\"[\w\W]*?size-(\w+)[\w\W]*?\"' ) ?></pre>
		<?php
		$query_args = array(
			'post_type' => 'any',
			'posts_per_page' => -1
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
}
new PrsoSrcSetRegen;